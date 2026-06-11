<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['key' => 'view_dashboard', 'name' => 'Voir le tableau de bord', 'group' => 'dashboard'],
            ['key' => 'view_products', 'name' => 'Voir les produits', 'group' => 'products'],
            ['key' => 'create_products', 'name' => 'Créer un produit', 'group' => 'products'],
            ['key' => 'edit_products', 'name' => 'Modifier un produit', 'group' => 'products'],
            ['key' => 'delete_products', 'name' => 'Supprimer un produit', 'group' => 'products'],
            ['key' => 'view_categories', 'name' => 'Voir les catégories', 'group' => 'categories'],
            ['key' => 'create_categories', 'name' => 'Créer une catégorie', 'group' => 'categories'],
            ['key' => 'edit_categories', 'name' => 'Modifier une catégorie', 'group' => 'categories'],
            ['key' => 'delete_categories', 'name' => 'Supprimer une catégorie', 'group' => 'categories'],
            ['key' => 'view_communes', 'name' => 'Voir les communes', 'group' => 'communes'],
            ['key' => 'create_communes', 'name' => 'Créer une commune', 'group' => 'communes'],
            ['key' => 'edit_communes', 'name' => 'Modifier une commune', 'group' => 'communes'],
            ['key' => 'delete_communes', 'name' => 'Supprimer une commune', 'group' => 'communes'],
            ['key' => 'view_orders', 'name' => 'Voir les commandes', 'group' => 'orders'],
            ['key' => 'update_orders', 'name' => 'Modifier le statut d\'une commande', 'group' => 'orders'],
            ['key' => 'delete_orders', 'name' => 'Supprimer une commande', 'group' => 'orders'],
            ['key' => 'view_testimonials', 'name' => 'Voir les témoignages', 'group' => 'testimonials'],
            ['key' => 'create_testimonials', 'name' => 'Créer un témoignage', 'group' => 'testimonials'],
            ['key' => 'edit_testimonials', 'name' => 'Modifier un témoignage', 'group' => 'testimonials'],
            ['key' => 'delete_testimonials', 'name' => 'Supprimer un témoignage', 'group' => 'testimonials'],
            ['key' => 'view_banners', 'name' => 'Voir les bannières', 'group' => 'banners'],
            ['key' => 'create_banners', 'name' => 'Créer une bannière', 'group' => 'banners'],
            ['key' => 'edit_banners', 'name' => 'Modifier une bannière', 'group' => 'banners'],
            ['key' => 'delete_banners', 'name' => 'Supprimer une bannière', 'group' => 'banners'],
            ['key' => 'view_users', 'name' => 'Voir les utilisateurs', 'group' => 'users'],
            ['key' => 'create_users', 'name' => 'Créer un utilisateur', 'group' => 'users'],
            ['key' => 'edit_users', 'name' => 'Modifier un utilisateur', 'group' => 'users'],
            ['key' => 'delete_users', 'name' => 'Supprimer un utilisateur', 'group' => 'users'],
            ['key' => 'manage_roles', 'name' => 'Gérer les rôles', 'group' => 'roles'],
            ['key' => 'manage_permissions', 'name' => 'Gérer les permissions', 'group' => 'roles'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['key' => $perm['key']], $perm);
        }

        // Create roles
        $superAdminRole = Role::firstOrCreate(['key' => 'super_admin'], ['name' => 'Super Admin']);
        $adminRole = Role::firstOrCreate(['key' => 'admin'], ['name' => 'Administrateur']);
        $managerRole = Role::firstOrCreate(['key' => 'manager'], ['name' => 'Manager']);
        $viewerRole = Role::firstOrCreate(['key' => 'viewer'], ['name' => 'Visualiseur']);

        // Super admin gets ALL permissions
        $allPermIds = Permission::pluck('id')->toArray();
        $superAdminRole->permissions()->sync($allPermIds);

        // Admin gets most permissions except manage_roles and manage_permissions
        $adminPermIds = Permission::whereNotIn('key', ['manage_roles', 'manage_permissions'])->pluck('id')->toArray();
        $adminRole->permissions()->sync($adminPermIds);

        // Manager gets view + update on products, orders, testimonials
        $managerPermIds = Permission::whereIn('key', [
            'view_dashboard', 'view_products', 'view_categories', 'view_communes',
            'view_orders', 'update_orders', 'view_testimonials', 'view_banners',
        ])->pluck('id')->toArray();
        $managerRole->permissions()->sync($managerPermIds);

        // Viewer gets only view permissions
        $viewerPermIds = Permission::where('key', 'like', 'view_%')->pluck('id')->toArray();
        $viewerRole->permissions()->sync($viewerPermIds);

        // Create default super admin user
        $superAdmin = User::firstOrCreate(
            ['email' => 'super@admin.com'],
            ['name' => 'Super Admin', 'password' => Hash::make('superadmin123')]
        );
        $superAdmin->roles()->syncWithoutDetaching($superAdminRole->id);

        // Create default admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin', 'password' => Hash::make('admin123')]
        );
        $admin->roles()->syncWithoutDetaching($adminRole->id);
    }
}
