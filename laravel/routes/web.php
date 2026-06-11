<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TrackController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/catalogue', [CatalogController::class, 'index'])->name('catalog');
Route::get('/produit/{slug}', [ProductController::class, 'show'])->name('product');

Route::get('/panier', [CartController::class, 'index'])->name('cart');
Route::post('/panier/ajouter', [CartController::class, 'add'])->name('cart.add');
Route::post('/panier/supprimer', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/panier/maj', [CartController::class, 'update'])->name('cart.update');

Route::get('/commande', [OrderController::class, 'create'])->name('order.create');
Route::post('/commande', [OrderController::class, 'store'])->name('order.store');

Route::get('/suivi', [TrackController::class, 'index'])->name('track');
Route::post('/suivi', [TrackController::class, 'search'])->name('track.search');
