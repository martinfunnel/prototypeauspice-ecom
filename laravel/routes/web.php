<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\AdminController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/catalogue', [CatalogController::class, 'index'])->name('catalog');
Route::get('/produit/{slug}', [ProductController::class, 'show'])->name('product');

Route::get('/panier', [CartController::class, 'index'])->name('cart');
Route::post('/panier/ajouter', [CartController::class, 'add'])->name('cart.add');
Route::post('/panier/supprimer', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/panier/maj', [CartController::class, 'update'])->name('cart.update');

Route::get('/commande', [OrderController::class, 'create'])->name('order.create');
Route::post('/commande', [OrderController::class, 'store'])->name('order.store');
Route::post('/commande-directe', [OrderController::class, 'storeDirect'])->name('order.direct');

Route::get('/suivi', [TrackController::class, 'index'])->name('track');
Route::post('/suivi', [TrackController::class, 'search'])->name('track.search');

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Password Reset
Route::get('/password/reset/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [PasswordResetController::class, 'reset'])->name('password.update');

// Claim first admin (doit être connecté mais pas forcément admin)
Route::middleware(['auth'])->post('/admin/claim-first-admin', [AuthController::class, 'claimFirstAdmin'])->name('admin.claim');

// Admin — protégé par permissions granulaires
Route::middleware([\App\Http\Middleware\AdminMiddleware::class])->prefix('admin')->group(function () {

    Route::middleware('can:view_dashboard')->get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    // Profile
    Route::get('/profil', [AdminController::class, 'profile'])->name('admin.profile');
    Route::patch('/profil', [AdminController::class, 'updateProfile']);
    Route::post('/profil/password', [AdminController::class, 'updatePassword']);

    // Products
    Route::middleware('can:view_products')->get('/products', [AdminController::class, 'products'])->name('admin.products');
    Route::middleware('can:create_products')->post('/products', [AdminController::class, 'storeProduct']);
    Route::middleware('can:edit_products')->patch('/products/{id}', [AdminController::class, 'updateProduct']);
    Route::middleware('can:delete_products')->delete('/products/{id}', [AdminController::class, 'destroyProduct']);
    Route::middleware('can:edit_products')->post('/products/{id}/toggle', [AdminController::class, 'toggleProduct'])->name('admin.products.toggle');
    Route::middleware('can:edit_products')->post('/products/{id}/stock', [AdminController::class, 'adjustStock'])->name('admin.products.stock');
    Route::middleware('can:edit_products')->post('/products/{id}/promo', [AdminController::class, 'setPromo'])->name('admin.products.promo');

    // Categories
    Route::middleware('can:view_categories')->get('/categories', [AdminController::class, 'categories'])->name('admin.categories');
    Route::middleware('can:create_categories')->post('/categories', [AdminController::class, 'storeCategory']);
    Route::middleware('can:edit_categories')->patch('/categories/{id}', [AdminController::class, 'updateCategory']);
    Route::middleware('can:delete_categories')->delete('/categories/{id}', [AdminController::class, 'destroyCategory']);

    // Communes
    Route::middleware('can:view_communes')->get('/communes', [AdminController::class, 'communes'])->name('admin.communes');
    Route::middleware('can:create_communes')->post('/communes', [AdminController::class, 'storeCommune']);
    Route::middleware('can:delete_communes')->delete('/communes/{id}', [AdminController::class, 'destroyCommune']);

    // Orders
    Route::middleware('can:view_orders')->get('/orders', [AdminController::class, 'orders'])->name('admin.orders');
    Route::middleware('can:update_orders')->patch('/orders/{id}/status', [AdminController::class, 'updateOrderStatus']);
    Route::middleware('can:delete_orders')->delete('/orders/{id}', [AdminController::class, 'destroyOrder']);

    // Testimonials
    Route::middleware('can:view_testimonials')->get('/testimonials', [AdminController::class, 'testimonials'])->name('admin.testimonials');
    Route::middleware('can:create_testimonials')->post('/testimonials', [AdminController::class, 'storeTestimonial']);
    Route::middleware('can:edit_testimonials')->patch('/testimonials/{id}', [AdminController::class, 'updateTestimonial']);
    Route::middleware('can:delete_testimonials')->delete('/testimonials/{id}', [AdminController::class, 'destroyTestimonial']);

    // Banners
    Route::middleware('can:view_banners')->get('/banners', [AdminController::class, 'banners'])->name('admin.banners');
    Route::middleware('can:edit_banners')->post('/banners', [AdminController::class, 'updateBanner']);

    // Activity Logs
    Route::middleware('super_admin')->get('/logs', [AdminController::class, 'logs'])->name('admin.logs');

    // Users & Roles — Super Admin only
    Route::middleware('super_admin')->group(function () {
        Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
        Route::post('/users', [AdminController::class, 'storeUser']);
        Route::delete('/users/{id}', [AdminController::class, 'destroyUser']);
        Route::post('/users/{id}/role', [AdminController::class, 'updateUserRole'])->name('admin.users.role');
        Route::post('/users/{id}/reset-password', [AdminController::class, 'resetUserPassword'])->name('admin.users.reset');
        Route::get('/users/{id}/details', [AdminController::class, 'userDetails'])->name('admin.users.details');

        Route::get('/roles', [AdminController::class, 'roles'])->name('admin.roles');
        Route::post('/roles', [AdminController::class, 'storeRole']);
        Route::get('/roles/{id}/edit', [AdminController::class, 'editRole'])->name('admin.roles.edit');
        Route::patch('/roles/{id}', [AdminController::class, 'updateRole']);
        Route::delete('/roles/{id}', [AdminController::class, 'destroyRole']);
    });
});
