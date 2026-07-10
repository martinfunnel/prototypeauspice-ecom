<?php

namespace App\Http\Controllers;

use App\Mail\UserInvitation;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Commune;
use App\Models\Order;
use App\Models\Permission;
use App\Models\Product;
use App\Models\PromoBanner;
use App\Models\Role;
use App\Models\Testimonial;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::pending()->count(),
            'delivered_orders' => Order::delivered()->count(),
            'total_revenue' => Order::where('status', '!=', 'cancelled')->sum('total'),
            'products_active' => Product::where('is_active', true)->count(),
            'products_total' => Product::count(),
            'low_stock' => Product::where('is_active', true)->where('stock', '<=', 3)->count(),
            'communes_active' => Commune::where('is_active', true)->count(),
        ];

        $rawSales = Order::where('status', '!=', 'cancelled')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as revenue'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $salesByDay = collect();
        for ($i = 6; $i >= 0; $i--) {
            $d = now()->subDays($i)->format('Y-m-d');
            $salesByDay->push((object)[
                'date' => $d,
                'revenue' => $rawSales[$d]->revenue ?? 0,
                'count' => $rawSales[$d]->count ?? 0,
            ]);
        }

        return view('admin.dashboard', compact('stats', 'salesByDay'));
    }

    public function products(Request $request)
    {
        $products = Product::with('category')->orderByDesc('created_at')->get();
        $categories = Category::orderBy('sort_order')->get();
        $editing = $request->has('edit') ? Product::find($request->edit) : null;
        return view('admin.products', compact('products', 'categories', 'editing'));
    }

    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'slug' => 'nullable|string|max:200',
            'short_description' => 'nullable|string|max:300',
            'description' => 'nullable|string|max:5000',
            'benefits' => 'nullable|string',
            'price' => 'required|numeric|min:0|max:10000000',
            'promo_price' => 'nullable|numeric|min:0|max:10000000',
            'stock' => 'required|integer|min:0|max:100000',
            'category_id' => 'nullable|exists:categories,id',
            'is_active' => 'nullable|boolean',
            'is_popular' => 'nullable|boolean',
        ]);

        $data = $this->prepareProductData($request, $validated);
        $data['slug'] = $data['slug'] ?: \Illuminate\Support\Str::slug($data['name']);

        $product = Product::create($data);
        $this->logActivity('create_product', "Création du produit {$product->name}", Product::class, $product->id);
        return redirect('/admin/products')->with('success', 'Produit créé');
    }

    public function updateProduct(Request $request, string $id)
    {
        $product = Product::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'slug' => 'nullable|string|max:200',
            'short_description' => 'nullable|string|max:300',
            'description' => 'nullable|string|max:5000',
            'benefits' => 'nullable|string',
            'price' => 'required|numeric|min:0|max:10000000',
            'promo_price' => 'nullable|numeric|min:0|max:10000000',
            'stock' => 'required|integer|min:0|max:100000',
            'category_id' => 'nullable|exists:categories,id',
            'is_active' => 'nullable|boolean',
            'is_popular' => 'nullable|boolean',
        ]);

        $data = $this->prepareProductData($request, $validated);
        $data['slug'] = $data['slug'] ?: \Illuminate\Support\Str::slug($data['name']);

        $product->update($data);
        $this->logActivity('update_product', "Mise à jour du produit {$product->name}", Product::class, $product->id);
        return redirect('/admin/products')->with('success', 'Produit mis à jour');
    }

    private function prepareProductData(Request $request, array $validated): array
    {
        $benefits = collect(explode("\n", $validated['benefits'] ?? ''))
            ->map(fn($s) => trim($s))
            ->filter()
            ->values()
            ->all();

        // Images existantes
        $existingImages = collect(explode("\n", $request->input('existing_images', '')))
            ->map(fn($s) => trim($s))
            ->filter()
            ->values()
            ->all();

        $existingDetailImages = collect(explode("\n", $request->input('existing_detail_images', '')))
            ->map(fn($s) => trim($s))
            ->filter()
            ->values()
            ->all();

        // Upload nouvelles images
        $newImages = $this->uploadImages($request->file('product_images'));
        $newDetailImages = $this->uploadImages($request->file('detail_product_images'));

        return [
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?: \Illuminate\Support\Str::slug($validated['name']),
            'short_description' => $validated['short_description'] ?? null,
            'description' => $validated['description'] ?? null,
            'benefits' => $benefits,
            'price' => $validated['price'],
            'promo_price' => $validated['promo_price'] ?? null,
            'stock' => $validated['stock'],
            'category_id' => $validated['category_id'] ?? null,
            'images' => array_merge($existingImages, $newImages),
            'detail_images' => array_merge($existingDetailImages, $newDetailImages),
            'is_active' => $validated['is_active'] ?? true,
            'is_popular' => $validated['is_popular'] ?? false,
        ];
    }

    private function uploadPublic($file, string $folder): string
    {
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        // La vraie racine web (DOCUMENT_ROOT) n'est PAS public/ sur cet hébergeur.
        // public_path() = /htdocs/project/public mais le serveur sert depuis /htdocs.
        // On écrit donc dans dirname(base_path())/uploads = la racine réellement servie.
        $webRoot = dirname(base_path());
        $destPath = $webRoot . '/uploads/' . $folder;
        if (!is_dir($destPath)) {
            mkdir($destPath, 0755, true);
        }
        $destFile = $destPath . '/' . $filename;
        $tmpPath = $file->getPathname();

        // copy() est plus fiable que move() cross-filesystem
        if (!copy($tmpPath, $destFile)) {
            $err = error_get_last()['message'] ?? 'unknown';
            throw new \Exception("Upload échoué: $err | tmp=$tmpPath | dest=$destFile");
        }
        if (!file_exists($destFile)) {
            throw new \Exception("Fichier non créé après copy: $destFile");
        }

        return url('uploads/' . $folder . '/' . $filename);
    }

    private function uploadImages(?array $files): array
    {
        if (!$files) return [];
        $urls = [];
        foreach ($files as $file) {
            if ($file && $file->isValid()) {
                $urls[] = $this->uploadPublic($file, 'products');
            }
        }
        return $urls;
    }

    public function destroyProduct(string $id)
    {
        $product = Product::findOrFail($id);
        $this->logActivity('delete_product', "Suppression du produit {$product->name}", Product::class, $product->id);
        $product->delete();
        return redirect('/admin/products')->with('success', 'Produit supprimé');
    }

    public function toggleProduct(string $id)
    {
        $product = Product::findOrFail($id);
        $product->update(['is_active' => !$product->is_active]);
        $this->logActivity('toggle_product', ($product->is_active ? 'Activation' : 'Désactivation') . " du produit {$product->name}", Product::class, $product->id);
        return redirect('/admin/products')->with('success', $product->is_active ? 'Produit activé' : 'Produit désactivé');
    }

    public function adjustStock(Request $request, string $id)
    {
        $product = Product::findOrFail($id);
        $validated = $request->validate([
            'amount' => 'required|integer',
            'reason' => 'nullable|string|max:200',
        ]);
        $newStock = max(0, $product->stock + $validated['amount']);
        $product->update(['stock' => $newStock]);
        $this->logActivity('stock_adjust', "Stock ajusté: {$product->name} ({$validated['amount']}) -> {$newStock}", Product::class, $product->id);
        return redirect('/admin/products')->with('success', 'Stock mis à jour');
    }

    public function setPromo(Request $request, string $id)
    {
        $product = Product::findOrFail($id);
        $validated = $request->validate([
            'promo_price' => 'nullable|numeric|min:0',
            'promo_ends_at' => 'nullable|date',
        ]);
        $product->update([
            'promo_price' => $validated['promo_price'] ?: null,
            'promo_ends_at' => $validated['promo_ends_at'] ?: null,
        ]);
        $this->logActivity('set_promo', "Mise à jour promo du produit {$product->name}", Product::class, $product->id);
        return redirect('/admin/products')->with('success', 'Promotion mise à jour');
    }

    public function categories(Request $request)
    {
        $categories = Category::with('products')->orderBy('sort_order')->get();
        $editing = $request->has('edit') ? Category::find($request->edit) : null;
        return view('admin.categories', compact('categories', 'editing'));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'slug' => 'nullable|string|max:200',
            'description' => 'nullable|string|max:1000',
            'sort_order' => 'required|integer|min:0',
        ]);

        $data = [
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?: \Illuminate\Support\Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'],
        ];

        if ($request->hasFile('image')) {
            $data['image_url'] = $this->uploadSingleImage($request->file('image'));
        }

        $category = Category::create($data);
        $this->logActivity('create_category', "Création de la catégorie {$category->name}", Category::class, $category->id);
        return redirect('/admin/categories')->with('success', 'Catégorie créée');
    }

    public function updateCategory(Request $request, string $id)
    {
        $category = Category::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'slug' => 'nullable|string|max:200',
            'description' => 'nullable|string|max:1000',
            'sort_order' => 'required|integer|min:0',
        ]);

        $data = [
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?: \Illuminate\Support\Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'],
        ];

        if ($request->hasFile('image')) {
            $data['image_url'] = $this->uploadSingleImage($request->file('image'));
        } elseif ($request->has('remove_image')) {
            $data['image_url'] = null;
        }

        $category->update($data);
        $this->logActivity('update_category', "Mise à jour de la catégorie {$category->name}", Category::class, $category->id);
        return redirect('/admin/categories')->with('success', 'Catégorie mise à jour');
    }

    private function uploadSingleImage($file): string
    {
        return $this->uploadPublic($file, 'categories');
    }

    public function destroyCategory(string $id)
    {
        $category = Category::findOrFail($id);
        $this->logActivity('delete_category', "Suppression de la catégorie {$category->name}", Category::class, $category->id);
        $category->delete();
        return redirect('/admin/categories')->with('success', 'Catégorie supprimée');
    }

    public function communes()
    {
        $communes = Commune::orderBy('zone')->orderBy('name')->get();
        return view('admin.communes', compact('communes'));
    }

    public function storeCommune(Request $request)
    {
        $commune = Commune::create($request->all());
        $this->logActivity('create_commune', "Création de la commune {$commune->name}", Commune::class, $commune->id);
        return redirect('/admin/communes')->with('success', 'Commune créée');
    }

    public function updateCommune(Request $request, string $id)
    {
        $commune = Commune::findOrFail($id);
        $commune->update($request->all());
        $this->logActivity('update_commune', "Mise à jour de la commune {$commune->name}", Commune::class, $commune->id);
        return redirect('/admin/communes')->with('success', 'Commune mise à jour');
    }

    public function toggleCommune(string $id)
    {
        $commune = Commune::findOrFail($id);
        $commune->update(['is_active' => !$commune->is_active]);
        $this->logActivity('toggle_commune', ($commune->is_active ? 'Activation' : 'Désactivation') . " de la commune {$commune->name}", Commune::class, $commune->id);
        return redirect('/admin/communes')->with('success', $commune->is_active ? 'Commune activée' : 'Commune désactivée');
    }

    public function destroyCommune(string $id)
    {
        $commune = Commune::findOrFail($id);
        $this->logActivity('delete_commune', "Suppression de la commune {$commune->name}", Commune::class, $commune->id);
        $commune->delete();
        return redirect('/admin/communes')->with('success', 'Commune supprimée');
    }

    public function orders(Request $request)
    {
        $query = Order::with('items')->orderByDesc('created_at');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sq) use ($q) {
                $sq->where('order_number', 'like', "%{$q}%")
                    ->orWhere('customer_name', 'like', "%{$q}%")
                    ->orWhere('customer_phone', 'like', "%{$q}%");
            });
        }

        $orders = $query->limit(500)->get();
        $statuses = [
            'pending' => 'En attente',
            'confirmed' => 'Confirmée',
            'processing' => 'En cours',
            'shipped' => 'Expédiée',
            'delivered' => 'Livrée',
            'cancelled' => 'Annulée',
        ];

        return view('admin.orders', compact('orders', 'statuses'));
    }

    public function pendingOrdersCount()
    {
        return response()->json([
            'count' => Order::pending()->count(),
        ]);
    }

    public function updateOrderStatus(Request $request, string $id)
    {
        $order = Order::findOrFail($id);
        $oldStatus = $order->status;
        $newStatus = $request->status;

        $order->update(['status' => $newStatus]);
        $this->logActivity('update_order_status', "Changement statut commande #{$order->order_number} -> {$newStatus}", Order::class, $order->id);

        // Gestion du stock selon le changement de statut
        if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
            // Annulation : restocker les produits
            foreach ($order->items as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->increment('stock', $item->quantity);
                }
            }
        } elseif ($oldStatus === 'cancelled' && $newStatus !== 'cancelled') {
            // Réactivation d'une commande annulée : déstocker
            foreach ($order->items as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->decrement('stock', $item->quantity);
                }
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'status' => $order->status, 'message' => 'Statut mis à jour']);
        }

        return redirect('/admin/orders')->with('success', 'Statut mis à jour');
    }

    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'string',
            'status' => 'required|string',
        ]);

        $newStatus = $request->status;
        $orders = Order::with('items')->whereIn('id', $request->ids)->get();
        $updated = 0;

        foreach ($orders as $order) {
            $oldStatus = $order->status;
            if ($oldStatus === $newStatus) continue;

            $order->update(['status' => $newStatus]);
            $this->logActivity('bulk_update_order_status', "Changement statut (lot) commande #{$order->order_number} -> {$newStatus}", Order::class, $order->id);

            if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
                foreach ($order->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) $product->increment('stock', $item->quantity);
                }
            } elseif ($oldStatus === 'cancelled' && $newStatus !== 'cancelled') {
                foreach ($order->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) $product->decrement('stock', $item->quantity);
                }
            }
            $updated++;
        }

        return response()->json([
            'success' => true,
            'updated' => $updated,
            'message' => $updated . ' commande(s) mise(s) à jour',
        ]);
    }

    public function testimonials()
    {
        $testimonials = Testimonial::orderBy('sort_order')->orderByDesc('created_at')->get();
        return view('admin.testimonials', compact('testimonials'));
    }

    private function prepareTestimonialData(Request $request): array
    {
        $validated = $request->validate([
            'author_name' => 'nullable|string|max:120',
            'role' => 'nullable|string|max:120',
            'content' => 'nullable|string|max:2000',
            'rating' => 'required|integer|min:1|max:5',
            'media_type' => 'required|in:image,video',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'required|integer|min:0',
        ]);

        $data = [
            'author_name' => $validated['author_name'] ?: '—',
            'role' => $validated['role'] ?: null,
            'content' => $validated['content'] ?: '',
            'rating' => $validated['rating'],
            'media_type' => $validated['media_type'],
            'is_active' => $validated['is_active'] ?? false,
            'sort_order' => $validated['sort_order'],
        ];

        if ($request->hasFile('media')) {
            $folder = $data['media_type'] === 'video' ? 'testimonials/videos' : 'testimonials/images';
            $data['media_url'] = $this->uploadPublic($request->file('media'), $folder);
        } elseif ($request->has('remove_media')) {
            $data['media_url'] = null;
        }

        return $data;
    }

    public function storeTestimonial(Request $request)
    {
        $testimonial = Testimonial::create($this->prepareTestimonialData($request));
        $this->logActivity('create_testimonial', "Création du témoignage de {$testimonial->author_name}", Testimonial::class, $testimonial->id);
        return redirect('/admin/testimonials')->with('success', 'Témoignage créé');
    }

    public function updateTestimonial(Request $request, string $id)
    {
        $t = Testimonial::findOrFail($id);
        $t->update($this->prepareTestimonialData($request));
        $this->logActivity('update_testimonial', "Mise à jour du témoignage de {$t->author_name}", Testimonial::class, $t->id);
        return redirect('/admin/testimonials')->with('success', 'Témoignage mis à jour');
    }

    public function destroyTestimonial(string $id)
    {
        $t = Testimonial::findOrFail($id);
        $this->logActivity('delete_testimonial', "Suppression du témoignage de {$t->author_name}", Testimonial::class, $t->id);
        $t->delete();
        return redirect('/admin/testimonials')->with('success', 'Témoignage supprimé');
    }

    public function banners()
    {
        $banner = PromoBanner::where('key', 'catalogue')->first();
        return view('admin.banners', compact('banner'));
    }

    public function updateBanner(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:200',
            'subtitle' => 'nullable|string|max:500',
            'cta_label' => 'nullable|string|max:50',
            'cta_url' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $data = [
            'title' => $validated['title'] ?: null,
            'subtitle' => $validated['subtitle'] ?: null,
            'cta_label' => $validated['cta_label'] ?: null,
            'cta_url' => $validated['cta_url'] ?: null,
            'is_active' => $validated['is_active'] ?? false,
        ];

        if ($request->hasFile('image')) {
            $data['image_url'] = $this->uploadPublic($request->file('image'), 'banners');
        } elseif ($request->has('remove_image')) {
            $data['image_url'] = null;
        }

        PromoBanner::updateOrCreate(['key' => 'catalogue'], $data);
        $this->logActivity('update_banner', "Mise à jour de la bannière catalogue");
        return redirect('/admin/banners')->with('success', 'Bannière enregistrée');
    }

    public function destroyOrder(string $id)
    {
        $order = Order::findOrFail($id);
        $this->logActivity('delete_order', "Suppression de la commande #{$order->order_number}", Order::class, $order->id);
        $order->delete();
        return redirect('/admin/orders')->with('success', 'Commande supprimée');
    }

    // ==================== USERS (Super Admin only) ====================
    public function users(Request $request)
    {
        $q = $request->get('q', '');
        $usersQuery = User::with('userRoles');

        if ($q) {
            $usersQuery->where(function($query) use ($q) {
                $query->where('identifier', 'like', "%{$q}%")
                    ->orWhere('full_name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $users = $usersQuery->orderBy('name')->get();
        $availableRoles = Role::pluck('key')->toArray();
        return view('admin.users', compact('users', 'availableRoles', 'q'));
    }

    public function storeUser(Request $request)
    {
        $validRoles = Role::pluck('key')->toArray();
        $validated = $request->validate([
            'full_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => ['required', 'string', Rule::in($validRoles)],
        ]);

        // Génération identifiant unique
        do {
            $identifier = AuthController::generateIdentifier();
        } while (User::where('identifier', $identifier)->exists());

        $user = User::create([
            'identifier' => $identifier,
            'email' => $validated['email'],
            'name' => $validated['full_name'] ?? $identifier,
            'full_name' => $validated['full_name'] ?? null,
            'password' => Hash::make(Str::random(32)), // mot de passe aléatoire, l'utilisateur va le reset
            'email_verified_at' => now(),
        ]);

        // Rôle métier
        UserRole::create(['user_id' => $user->id, 'role' => $validated['role']]);

        // Sync avec permissions granulaires
        $roleModel = Role::firstOrCreate(['key' => $validated['role']], ['name' => ucfirst($validated['role'])]);
        $user->roles()->attach($roleModel->id);

        // Générer token reset password pour l'invitation
        $token = app('auth.password.broker')->createToken($user);
        $resetUrl = url(route('password.reset', ['token' => $token, 'email' => $user->email], false));

        // Envoyer l'email d'invitation
        $mailError = null;
        try {
            Mail::to($user->email)->send(new UserInvitation($identifier, $resetUrl, $user->full_name ?? ''));
        } catch (\Throwable $e) {
            $mailError = $e->getMessage();
            \Illuminate\Support\Facades\Log::error('Erreur envoi mail invitation', [
                'error' => $e->getMessage(),
                'email' => $user->email,
                'user_id' => $user->id,
            ]);
        }

        $this->logActivity('create_user', "Création du compte staff {$identifier}", null, null, ['role' => $validated['role'], 'mail_error' => $mailError]);

        if ($mailError) {
            return redirect('/admin/users')->with('warning', "Compte créé, mais l'email n'a pas pu être envoyé. Vérifiez votre configuration SMTP ou vos logs.");
        }

        return redirect('/admin/users')->with('success', "Compte staff créé. Un email d'invitation a été envoyé à {$validated['email']}.");
    }

    public function destroyUser(string $id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return redirect('/admin/users')->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        if ($user->isSuperAdmin()) {
            $superCount = UserRole::where('role', 'super_admin')->count();
            if ($superCount <= 1) {
                return redirect('/admin/users')->with('error', 'Impossible de supprimer le dernier super admin.');
            }
        }

        $this->logActivity('delete_user', "Suppression du compte {$user->identifier}", null, null);
        $user->delete();
        return redirect('/admin/users')->with('success', 'Utilisateur supprimé');
    }

    public function updateUserRole(Request $request, string $id)
    {
        $validRoles = Role::pluck('key')->toArray();
        $validated = $request->validate([
            'role' => ['required', 'string', Rule::in($validRoles)],
            'action' => 'required|in:add,remove',
        ]);

        $user = User::findOrFail($id);

        if ($validated['action'] === 'add') {
            UserRole::firstOrCreate(['user_id' => $user->id, 'role' => $validated['role']]);
            $roleModel = Role::firstOrCreate(['key' => $validated['role']], ['name' => ucfirst($validated['role'])]);
            $user->roles()->syncWithoutDetaching($roleModel->id);
            $this->logActivity('assign_role', "Attribution du rôle {$validated['role']} à {$user->identifier}", null, null);
        } else {
            if ($validated['role'] === 'super_admin' && $user->isSuperAdmin()) {
                $superCount = UserRole::where('role', 'super_admin')->count();
                if ($superCount <= 1) {
                    return redirect('/admin/users')->with('error', 'Impossible de retirer le dernier super admin.');
                }
            }
            UserRole::where('user_id', $user->id)->where('role', $validated['role'])->delete();
            $roleModel = Role::where('key', $validated['role'])->first();
            if ($roleModel) {
                $user->roles()->detach($roleModel->id);
            }
            $this->logActivity('remove_role', "Retrait du rôle {$validated['role']} de {$user->identifier}", null, null);
        }

        return redirect('/admin/users')->with('success', 'Rôle mis à jour');
    }

    public function resetUserPassword(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $token = app('auth.password.broker')->createToken($user);
        $resetUrl = url(route('password.reset', ['token' => $token, 'email' => $user->email], false));

        $mailError = null;
        try {
            Mail::to($user->email)->send(new UserInvitation($user->identifier, $resetUrl, $user->full_name ?? ''));
        } catch (\Throwable $e) {
            $mailError = $e->getMessage();
            \Illuminate\Support\Facades\Log::error('Erreur renvoi mail invitation', [
                'error' => $e->getMessage(),
                'email' => $user->email,
                'user_id' => $user->id,
            ]);
        }

        $this->logActivity('reset_password', "Demande de réinitialisation du mot de passe pour {$user->identifier}", null, null, ['mail_error' => $mailError]);

        if ($mailError) {
            return redirect('/admin/users')->with('warning', "L'email n'a pas pu être envoyé. Vérifiez votre configuration SMTP ou vos logs.");
        }

        return redirect('/admin/users')->with('success', "Email de réinitialisation envoyé à {$user->email}.");
    }

    public function userDetails(Request $request, string $id)
    {
        $user = User::with('userRoles')->findOrFail($id);
        $logFilter = $request->get('log_action', '');

        $logsQuery = ActivityLog::where('user_id', $user->id)->orderByDesc('created_at')->limit(100);
        if ($logFilter) {
            $logsQuery->where('action', $logFilter);
        }
        $logs = $logsQuery->get();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'identifier' => $user->identifier,
                'name' => $user->full_name ?? $user->name,
                'email' => $user->email,
                'roles' => $user->userRoles->pluck('role'),
                'created_at' => $user->created_at->format('d/m/Y H:i'),
            ],
            'logs' => $logs->map(fn($log) => [
                'action' => $log->action,
                'description' => $log->description,
                'created_at' => $log->created_at->format('d/m/Y H:i'),
                'ip' => $log->ip_address,
            ]),
            'log_actions' => ActivityLog::where('user_id', $user->id)->distinct()->pluck('action'),
        ]);
    }

    public function userLogs(Request $request, string $id)
    {
        $user = User::with('userRoles')->findOrFail($id);

        $query = ActivityLog::with('user')->where('user_id', $user->id)->orderByDesc('created_at');

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('action', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('model_type', 'like', "%{$q}%");
            });
        }

        $logs = $query->paginate(50)->withQueryString();

        $allActions = collect([
            'stock_adjust', 'create_product', 'update_product', 'delete_product', 'toggle_product', 'set_promo',
            'create_category', 'update_category', 'delete_category',
            'create_commune', 'update_commune', 'delete_commune', 'toggle_commune',
            'update_order_status', 'delete_order',
            'create_testimonial', 'update_testimonial', 'delete_testimonial',
            'update_banner',
            'create_user', 'delete_user', 'assign_role', 'remove_role', 'reset_password',
            'update_profile', 'update_password',
            'create_role', 'update_role', 'delete_role',
            'login', 'logout',
            'unauthorized_access', 'validation_error', 'error',
        ]);
        $loggedActions = ActivityLog::where('user_id', $user->id)->distinct()->orderBy('action')->pluck('action');
        $actions = $allActions->merge($loggedActions)->unique()->sort()->values();

        return view('admin.user-logs', compact('logs', 'actions', 'user'));
    }

    public function logs(Request $request)
    {
        $query = ActivityLog::with('user')->orderByDesc('created_at');

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('action', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('model_type', 'like', "%{$q}%");
            });
        }

        $logs = $query->paginate(50)->withQueryString();

        if ($request->ajax() || $request->get('format') === 'json') {
            return response()->json([
                'logs' => $logs->items(),
                'current_page' => $logs->currentPage(),
                'total' => $logs->total(),
                'today' => ActivityLog::whereDate('created_at', today())->count(),
                'this_week' => ActivityLog::where('created_at', '>=', now()->subDays(6))->count(),
            ]);
        }

        $allActions = collect([
            'stock_adjust', 'create_product', 'update_product', 'delete_product', 'toggle_product', 'set_promo',
            'create_category', 'update_category', 'delete_category',
            'create_commune', 'update_commune', 'delete_commune',
            'update_order_status', 'delete_order',
            'create_testimonial', 'update_testimonial', 'delete_testimonial',
            'update_banner',
            'create_user', 'delete_user', 'assign_role', 'remove_role', 'reset_password',
            'update_profile', 'update_password',
            'create_role', 'update_role', 'delete_role',
            'login', 'logout',
            'unauthorized_access', 'validation_error', 'error',
        ]);
        $loggedActions = ActivityLog::distinct()->orderBy('action')->pluck('action');
        $actions = $allActions->merge($loggedActions)->unique()->sort()->values();
        $users = User::orderBy('name')->get(['id', 'name', 'identifier']);

        return view('admin.logs', compact('logs', 'actions', 'users'));
    }

    private function logActivity(string $action, ?string $description = null, ?string $modelType = null, ?string $modelId = null, ?array $metadata = null): void
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'ip_address' => request()->ip(),
            'metadata' => $metadata,
        ]);
    }

    // ==================== PROFILE ====================
    public function profile()
    {
        $user = auth()->user();
        return view('admin.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'full_name' => 'nullable|string|max:255',
        ]);

        $user->update($validated);
        $this->logActivity('update_profile', "Mise à jour du profil");
        return redirect('/admin/profil')->with('success', 'Profil mis à jour');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Le mot de passe actuel est incorrect.']);
        }

        $user->update(['password' => Hash::make($request->password)]);
        $this->logActivity('update_password', "Changement de mot de passe");
        return redirect('/admin/profil')->with('success', 'Mot de passe changé avec succès');
    }

    // ==================== ROLES (Super Admin only) ====================
    public function roles()
    {
        $roles = Role::with('permissions')->orderBy('name')->get();
        $permissions = Permission::orderBy('group')->orderBy('name')->get()->groupBy('group');
        return view('admin.roles', compact('roles', 'permissions'));
    }

    public function storeRole(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|unique:roles|max:50',
            'name' => 'required|string|max:255',
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'key' => $validated['key'],
            'name' => $validated['name'],
        ]);

        if (!empty($validated['permission_ids'])) {
            $role->permissions()->sync($validated['permission_ids']);
        }
        $this->logActivity('create_role', "Création du rôle {$role->name}");
        return redirect('/admin/roles')->with('success', 'Rôle créé');
    }

    public function editRole(string $id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $permissions = Permission::orderBy('group')->orderBy('name')->get()->groupBy('group');
        $roles = Role::with('permissions')->orderBy('name')->get();
        return view('admin.roles', compact('role', 'permissions', 'roles'));
    }

    public function updateRole(Request $request, string $id)
    {
        $role = Role::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $role->update(['name' => $validated['name']]);
        $role->permissions()->sync($validated['permission_ids'] ?? []);
        $this->logActivity('update_role', "Mise à jour du rôle {$role->name}");
        return redirect('/admin/roles')->with('success', 'Rôle mis à jour');
    }

    public function destroyRole(string $id)
    {
        $role = Role::findOrFail($id);
        if ($role->key === 'super_admin') {
            return redirect('/admin/roles')->with('error', 'Impossible de supprimer le rôle super admin');
        }
        $this->logActivity('delete_role', "Suppression du rôle {$role->name}");
        $role->delete();
        return redirect('/admin/roles')->with('success', 'Rôle supprimé');
    }

    /* ---- Gérants de commandes (notifications Telegram) ---- */

    public function orderManagers()
    {
        $managers = \App\Models\OrderManager::orderBy('name')->get();
        return view('admin.order-managers', compact('managers'));
    }

    public function storeOrderManager(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
        ]);

        \App\Models\OrderManager::create($validated);
        $this->logActivity('create_order_manager', "Ajout du gérant {$validated['name']}");
        return redirect('/admin/order-managers')->with('success', 'Gérant ajouté');
    }

    public function updateOrderManager(Request $request, string $id)
    {
        $manager = \App\Models\OrderManager::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'is_active' => 'nullable|boolean',
        ]);

        $manager->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'is_active' => $request->boolean('is_active', true),
        ]);
        $this->logActivity('update_order_manager', "Mise à jour du gérant {$manager->name}");
        return redirect('/admin/order-managers')->with('success', 'Gérant mis à jour');
    }

    public function toggleOrderManager(string $id)
    {
        $manager = \App\Models\OrderManager::findOrFail($id);
        $manager->update(['is_active' => !$manager->is_active]);
        $action = $manager->is_active ? 'réactivé' : 'suspendu';
        $this->logActivity('toggle_order_manager', "Gérant {$manager->name} {$action}");
        return redirect('/admin/order-managers')->with('success', "Gérant {$action}");
    }

    public function destroyOrderManager(string $id)
    {
        $manager = \App\Models\OrderManager::findOrFail($id);
        $this->logActivity('delete_order_manager', "Suppression du gérant {$manager->name}");
        $manager->delete();
        return redirect('/admin/order-managers')->with('success', 'Gérant supprimé');
    }

    /* ---- Codes pays (téléphones) ---- */

    public function countryCodes()
    {
        $codes = \App\Models\CountryCode::orderBy('sort_order')->get();
        return view('admin.country-codes', compact('codes'));
    }

    public function storeCountryCode(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10',
            'iso' => 'required|string|size:3',
            'digits' => 'required|integer|min:1|max:20',
            'format' => 'required|string|max:50',
            'pattern' => 'required|string|max:100',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['is_active'] = true;
        \App\Models\CountryCode::create($validated);
        $this->logActivity('create_country_code', "Ajout du code pays {$validated['name']} ({$validated['code']})");
        return redirect('/admin/country-codes')->with('success', 'Code pays ajouté');
    }

    public function updateCountryCode(Request $request, string $id)
    {
        $code = \App\Models\CountryCode::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10',
            'iso' => 'required|string|size:3',
            'digits' => 'required|integer|min:1|max:20',
            'format' => 'required|string|max:50',
            'pattern' => 'required|string|max:100',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $code->update([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'iso' => $validated['iso'],
            'digits' => $validated['digits'],
            'format' => $validated['format'],
            'pattern' => $validated['pattern'],
            'sort_order' => $validated['sort_order'] ?? $code->sort_order,
            'is_active' => $request->boolean('is_active', $code->is_active),
        ]);
        $this->logActivity('update_country_code', "Mise à jour du code pays {$code->name}");
        return redirect('/admin/country-codes')->with('success', 'Code pays mis à jour');
    }

    public function toggleCountryCode(string $id)
    {
        $code = \App\Models\CountryCode::findOrFail($id);
        $code->update(['is_active' => !$code->is_active]);
        $status = $code->fresh()->is_active ? 'activé' : 'désactivé';
        $this->logActivity('toggle_country_code', "Code pays {$code->name} {$status}");
        return redirect('/admin/country-codes')->with('success', "Code pays {$status}");
    }

    public function destroyCountryCode(string $id)
    {
        $code = \App\Models\CountryCode::findOrFail($id);
        $this->logActivity('delete_country_code', "Suppression du code pays {$code->name}");
        $code->delete();
        return redirect('/admin/country-codes')->with('success', 'Code pays supprimé');
    }
}
