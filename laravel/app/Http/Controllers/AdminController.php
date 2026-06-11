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
            'total_products' => Product::count(),
            'total_customers' => Order::distinct('customer_phone')->count('customer_phone'),
        ];

        $recentOrders = Order::with('items')->orderByDesc('created_at')->limit(10)->get();

        $salesByDay = Order::where('status', '!=', 'cancelled')
            ->where('created_at', '>=', now()->subDays(6))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as revenue'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.dashboard', compact('stats', 'recentOrders', 'salesByDay'));
    }

    public function products()
    {
        $products = Product::with('category')->orderBy('name')->paginate(20);
        $categories = Category::orderBy('name')->get();
        return view('admin.products', compact('products', 'categories'));
    }

    public function storeProduct(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:products',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        Product::create($request->all());
        return redirect('/admin/products')->with('success', 'Produit créé');
    }

    public function updateProduct(Request $request, string $id)
    {
        $product = Product::findOrFail($id);
        $product->update($request->all());
        return redirect('/admin/products')->with('success', 'Produit mis à jour');
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

    public function orders()
    {
        $orders = Order::with('items')->orderByDesc('created_at')->paginate(20);
        return view('admin.orders', compact('orders'));
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
        $users = User::with('roles')->orderBy('name')->paginate(20);
        $roles = Role::orderBy('name')->get();
        return view('admin.users', compact('users', 'roles'));
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'role_ids' => 'nullable|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        if (!empty($validated['role_ids'])) {
            $user->roles()->sync($validated['role_ids']);
        }

        return redirect('/admin/users')->with('success', 'Utilisateur créé');
    }

    public function editUser(string $id)
    {
        $user = User::with('roles')->findOrFail($id);
        $roles = Role::orderBy('name')->get();
        return view('admin.users', compact('user', 'roles'));
    }

    public function updateUser(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'role_ids' => 'nullable|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        $user->roles()->sync($validated['role_ids'] ?? []);

        return redirect('/admin/users')->with('success', 'Utilisateur mis à jour');
    }

    public function destroyUser(string $id)
    {
        $user = User::findOrFail($id);
        if ($user->isSuperAdmin() && User::whereHas('roles', fn ($q) => $q->where('key', 'super_admin'))->count() <= 1) {
            return redirect('/admin/users')->with('error', 'Impossible de supprimer le dernier super admin');
        }
        $user->delete();
        return redirect('/admin/users')->with('success', 'Utilisateur supprimé');
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
