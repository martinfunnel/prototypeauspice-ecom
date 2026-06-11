<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\AuthController;
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
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Admin
Route::middleware([\App\Http\Middleware\AdminMiddleware::class])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/products', [AdminController::class, 'products'])->name('admin.products');
    Route::post('/products', [AdminController::class, 'storeProduct']);
    Route::delete('/products/{id}', [AdminController::class, 'destroyProduct']);
    Route::get('/categories', [AdminController::class, 'categories'])->name('admin.categories');
    Route::post('/categories', [AdminController::class, 'storeCategory']);
    Route::delete('/categories/{id}', [AdminController::class, 'destroyCategory']);
    Route::get('/communes', [AdminController::class, 'communes'])->name('admin.communes');
    Route::post('/communes', [AdminController::class, 'storeCommune']);
    Route::delete('/communes/{id}', [AdminController::class, 'destroyCommune']);
    Route::get('/orders', [AdminController::class, 'orders'])->name('admin.orders');
    Route::patch('/orders/{id}/status', [AdminController::class, 'updateOrderStatus']);
    Route::get('/testimonials', [AdminController::class, 'testimonials'])->name('admin.testimonials');
    Route::post('/testimonials', [AdminController::class, 'storeTestimonial']);
    Route::delete('/testimonials/{id}', [AdminController::class, 'destroyTestimonial']);
    Route::get('/banners', [AdminController::class, 'banners'])->name('admin.banners');
    Route::patch('/banners/{id}', [AdminController::class, 'updateBanner']);
});
