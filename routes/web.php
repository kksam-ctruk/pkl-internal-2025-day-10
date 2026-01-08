<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Import Controllers// routes/api.php
use App\Http\Controllers\PaymentNotificationController;
use App\Http\Controllers\{HomeController, CatalogController, CartController, WishlistController, CheckoutController, OrderController, ProfileController, PaymentController};
use App\Http\Controllers\Admin\{DashboardController, UserController, ReportController};
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Auth\GoogleController;

// ================================================
// ROUTE PUBLIK
// ================================================
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/products', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/products/{slug}', [CatalogController::class, 'show'])->name('catalog.show');

// Google Auth
Route::controller(GoogleController::class)->group(function () {
    Route::get('/auth/google', 'redirect')->name('auth.google');
    Route::get('/auth/google/callback', 'callback')->name('auth.google.callback');
});

// ================================================
// ROUTE CUSTOMER (Login Required)
// ================================================
Route::middleware('auth')->group(function () {
    // Keranjang, Wishlist, Checkout
    Route::resource('cart', CartController::class)->only(['index', 'update', 'destroy']);
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/wishlist/toggle/{product}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    // Pesanan Saya & Payment
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/pay', [PaymentController::class, 'show'])->name('orders.pay');

    // Profile
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
        Route::patch('/profile/avatar', 'updateAvatar')->name('profile.avatar.update');
    });
});

// ================================================
// ROUTE ADMIN (Login + Admin Role)
// ================================================
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Resources (CRUD)
    Route::resource('products', AdminProductController::class);
    Route::resource('categories', AdminCategoryController::class);
    
    // Manajemen Pesanan
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    // NAMA ROUTE HARUS: admin.orders.update-status
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');

    // Manajemen User & Report
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
});

// Pastikan namanya persis: admin.reports.export-sales
Route::get('/admin/reports/sales/export', [ReportController::class, 'exportSales'])
    ->name('admin.reports.export-sales');


 Route::middleware('auth')->group(function () {
    // ... route lainnya ...
    
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    
    // TAMBAHKAN INI SEBELUM route show
    Route::get('/orders/success/{order}', [OrderController::class, 'success'])->name('orders.success');
    
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
});

Route::middleware('auth')->group(function () {
    // Cari bagian ini
    Route::resource('cart', CartController::class)->only(['index', 'update', 'destroy']);
    
    // Tambahkan baris ini tepat di bawahnya sebagai alias
    Route::delete('/cart/{cart}', [CartController::class, 'destroy'])->name('cart.remove');
    
    // ... sisanya
});

Route::middleware('auth')->group(function () {
    // ... route lainnya ...

    // Tambahkan ini
    Route::get('/orders/pending', [OrderController::class, 'pending'])->name('orders.pending');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/success/{order}', [OrderController::class, 'success'])->name('orders.success');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
});


// Profile
Route::controller(ProfileController::class)->group(function () {
    Route::get('/profile', 'edit')->name('profile.edit');
    Route::patch('/profile', 'update')->name('profile.update');
    Route::delete('/profile', 'destroy')->name('profile.destroy');
    Route::patch('/profile/avatar', 'updateAvatar')->name('profile.avatar.update');
    // TAMBAHKAN BARIS INI:
    Route::delete('/profile/avatar', 'destroyAvatar')->name('profile.avatar.destroy');
});


// routes/api.php
Route::post('/midtrans/notification', [PaymentNotificationController::class, 'handle']);

// Auth Routes (Login/Register)
Auth::routes();