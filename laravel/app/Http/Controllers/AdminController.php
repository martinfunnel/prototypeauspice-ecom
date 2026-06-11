<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Commune;
use App\Models\Order;
use App\Models\Permission;
use App\Models\Product;
use App\Models\PromoBanner;
use App\Models\Role;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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

        $salesByDay = Order::where('status', '!=', 'cancelled')
            ->where('created_at', '>=', now()->subDays(6))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as revenue'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

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
            'images' => 'nullable|string',
            'detail_images' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'is_popular' => 'nullable|boolean',
        ]);

        $data = $this->prepareProductData($validated);
        $data['slug'] = $data['slug'] ?: \Illuminate\Support\Str::slug($data['name']);

        Product::create($data);
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
            'images' => 'nullable|string',
            'detail_images' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'is_popular' => 'nullable|boolean',
        ]);

        $data = $this->prepareProductData($validated);
        $data['slug'] = $data['slug'] ?: \Illuminate\Support\Str::slug($data['name']);

        $product->update($data);
        return redirect('/admin/products')->with('success', 'Produit mis à jour');
    }

    private function prepareProductData(array $validated): array
    {
        $benefits = collect(explode("\n", $validated['benefits'] ?? ''))
            ->map(fn($s) => trim($s))
            ->filter()
            ->values()
            ->all();

        $images = collect(explode("\n", $validated['images'] ?? ''))
            ->map(fn($s) => trim($s))
            ->filter()
            ->values()
            ->all();

        $detailImages = collect(explode("\n", $validated['detail_images'] ?? ''))
            ->map(fn($s) => trim($s))
            ->filter()
            ->values()
            ->all();

        return [
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? null,
            'short_description' => $validated['short_description'] ?? null,
            'description' => $validated['description'] ?? null,
            'benefits' => $benefits,
            'price' => $validated['price'],
            'promo_price' => $validated['promo_price'] ?? null,
            'stock' => $validated['stock'],
            'category_id' => $validated['category_id'] ?? null,
            'images' => $images,
            'detail_images' => $detailImages,
            'is_active' => $validated['is_active'] ?? true,
            'is_popular' => $validated['is_popular'] ?? false,
        ];
    }

    public function destroyProduct(string $id)
    {
        Product::findOrFail($id)->delete();
        return redirect('/admin/products')->with('success', 'Produit supprimé');
    }

    public function categories()
    {
        $categories = Category::orderBy('sort_order')->get();
        return view('admin.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        Category::create($request->all());
        return redirect('/admin/categories')->with('success', 'Catégorie créée');
    }

    public function updateCategory(Request $request, string $id)
    {
        Category::findOrFail($id)->update($request->all());
        return redirect('/admin/categories')->with('success', 'Catégorie mise à jour');
    }

    public function destroyCategory(string $id)
    {
        Category::findOrFail($id)->delete();
        return redirect('/admin/categories')->with('success', 'Catégorie supprimée');
    }

    public function communes()
    {
        $communes = Commune::orderBy('zone')->orderBy('name')->get();
        return view('admin.communes', compact('communes'));
    }

    public function storeCommune(Request $request)
    {
        Commune::create($request->all());
        return redirect('/admin/communes')->with('success', 'Commune créée');
    }

    public function updateCommune(Request $request, string $id)
    {
        Commune::findOrFail($id)->update($request->all());
        return redirect('/admin/communes')->with('success', 'Commune mise à jour');
    }

    public function destroyCommune(string $id)
    {
        Commune::findOrFail($id)->delete();
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

    public function updateOrderStatus(Request $request, string $id)
    {
        $order = Order::findOrFail($id);
        $order->update(['status' => $request->status]);
        return redirect('/admin/orders')->with('success', 'Statut mis à jour');
    }

    public function testimonials()
    {
        $testimonials = Testimonial::orderBy('sort_order')->get();
        return view('admin.testimonials', compact('testimonials'));
    }

    public function storeTestimonial(Request $request)
    {
        Testimonial::create($request->all());
        return redirect('/admin/testimonials')->with('success', 'Témoignage créé');
    }

    public function updateTestimonial(Request $request, string $id)
    {
        Testimonial::findOrFail($id)->update($request->all());
        return redirect('/admin/testimonials')->with('success', 'Témoignage mis à jour');
    }

    public function destroyTestimonial(string $id)
    {
        Testimonial::findOrFail($id)->delete();
        return redirect('/admin/testimonials')->with('success', 'Témoignage supprimé');
    }

    public function banners()
    {
        $banners = PromoBanner::all();
        return view('admin.banners', compact('banners'));
    }

    public function updateBanner(Request $request, string $id)
    {
        PromoBanner::findOrFail($id)->update($request->all());
        return redirect('/admin/banners')->with('success', 'Bannière mise à jour');
    }

    public function destroyOrder(string $id)
    {
        Order::findOrFail($id)->delete();
        return redirect('/admin/orders')->with('success', 'Commande supprimée');
    }

    // ==================== USERS (Super Admin only) ====================
    public function users()
    {
        $users = User::with('userRoles')->orderBy('name')->paginate(20);
        $availableRoles = ['super_admin', 'admin', 'vendeur', 'comptable'];
        $newCredentials = session('new_credentials');
        return view('admin.users', compact('users', 'availableRoles', 'newCredentials'));
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'nullable|string|max:255',
            'role' => 'required|string|in:super_admin,admin,vendeur,comptable',
        ]);

        // Génération identifiant unique
        do {
            $identifier = AuthController::generateIdentifier();
        } while (User::where('identifier', $identifier)->exists());

        $password = AuthController::generatePassword();
        $email = strtolower($identifier) . '@auspice.local';

        $user = User::create([
            'identifier' => $identifier,
            'email' => $email,
            'name' => $validated['full_name'] ?? $identifier,
            'full_name' => $validated['full_name'] ?? null,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);

        // Rôle métier
        UserRole::create(['user_id' => $user->id, 'role' => $validated['role']]);

        // Sync avec permissions granulaires
        $roleModel = Role::firstOrCreate(['key' => $validated['role']], ['name' => ucfirst($validated['role'])]);
        $user->roles()->attach($roleModel->id);

        return redirect('/admin/users')->with('new_credentials', [
            'identifier' => $identifier,
            'password' => $password,
        ])->with('success', 'Compte staff créé. Affichez les identifiants ci-dessous.');
    }

    public function destroyUser(string $id)
    {
        $user = User::findOrFail($id);

        // Empêcher la suppression de son propre compte
        if ($user->id === auth()->id()) {
            return redirect('/admin/users')->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        // Empêcher la suppression du dernier super_admin
        if ($user->isSuperAdmin()) {
            $superCount = UserRole::where('role', 'super_admin')->count();
            if ($superCount <= 1) {
                return redirect('/admin/users')->with('error', 'Impossible de supprimer le dernier super admin.');
            }
        }

        $user->delete();
        return redirect('/admin/users')->with('success', 'Utilisateur supprimé');
    }

    public function updateUserRole(Request $request, string $id)
    {
        $validated = $request->validate([
            'role' => 'required|string|in:super_admin,admin,vendeur,comptable',
            'action' => 'required|in:add,remove',
        ]);

        $user = User::findOrFail($id);

        if ($validated['action'] === 'add') {
            UserRole::firstOrCreate(['user_id' => $user->id, 'role' => $validated['role']]);
            $roleModel = Role::firstOrCreate(['key' => $validated['role']], ['name' => ucfirst($validated['role'])]);
            $user->roles()->syncWithoutDetaching($roleModel->id);
        } else {
            // Vérifier qu'on ne retire pas le dernier super_admin
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
        }

        return redirect('/admin/users')->with('success', 'Rôle mis à jour');
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

        return redirect('/admin/roles')->with('success', 'Rôle mis à jour');
    }

    public function destroyRole(string $id)
    {
        $role = Role::findOrFail($id);
        if ($role->key === 'super_admin') {
            return redirect('/admin/roles')->with('error', 'Impossible de supprimer le rôle super admin');
        }
        $role->delete();
        return redirect('/admin/roles')->with('success', 'Rôle supprimé');
    }
}
