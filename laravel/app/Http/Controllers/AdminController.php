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

    private function uploadImages(?array $files): array
    {
        if (!$files) return [];
        $urls = [];
        foreach ($files as $file) {
            if ($file && $file->isValid()) {
                $path = $file->store('products', 'public');
                $urls[] = asset('storage/' . $path);
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
        $path = $file->store('categories', 'public');
        return asset('storage/' . $path);
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
        $order->update(['status' => $request->status]);
        $this->logActivity('update_order_status', "Changement statut commande #{$order->order_number} -> {$request->status}", Order::class, $order->id);
        return redirect('/admin/orders')->with('success', 'Statut mis à jour');
    }

    public function testimonials()
    {
        $testimonials = Testimonial::orderBy('sort_order')->orderByDesc('created_at')->get();
        return view('admin.testimonials', compact('testimonials'));
    }

    private function prepareTestimonialData(Request $request): array
    {
        $validated = $request->validate([
            'author_name' => 'required|string|max:120',
            'role' => 'nullable|string|max:120',
            'content' => 'required|string|max:2000',
            'rating' => 'required|integer|min:1|max:5',
            'media_type' => 'required|in:image,video',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'required|integer|min:0',
        ]);

        $data = [
            'author_name' => $validated['author_name'],
            'role' => $validated['role'] ?: null,
            'content' => $validated['content'],
            'rating' => $validated['rating'],
            'media_type' => $validated['media_type'],
            'is_active' => $validated['is_active'] ?? false,
            'sort_order' => $validated['sort_order'],
        ];

        if ($request->hasFile('media')) {
            $ext = $request->file('media')->getClientOriginalExtension();
            $folder = $data['media_type'] === 'video' ? 'testimonials/videos' : 'testimonials/images';
            $path = $request->file('media')->store($folder, 'public');
            $data['media_url'] = asset('storage/' . $path);
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
            $path = $request->file('image')->store('banners', 'public');
            $data['image_url'] = asset('storage/' . $path);
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
}
