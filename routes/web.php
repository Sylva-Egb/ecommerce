<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardAdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OrderController;
use App\Models\Product;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ProductController;

Route::middleware(['auth'])->group(function () {
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');

    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    // Admin routes
    Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::post('/orders/{order}/items', [OrderController::class, 'addItem'])->name('orders.add-item');
    Route::delete('/orders/items/{item}', [OrderController::class, 'removeItem'])->name('orders.remove-item');
});

Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('categories', [CategoryController::class, 'index'])->name('admin.categories.index');
    Route::get('categories/create', [CategoryController::class, 'create'])->name('admin.categories.create');
    Route::post('categories', [CategoryController::class, 'store'])->name('admin.categories.store');
    Route::post('/categories/{category}', [CategoryController::class, 'update'])->name('admin.categories.update');
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');

    Route::get('products', [ProductController::class, 'index'])->name('admin.products.index');
    Route::get('products/create', [ProductController::class, 'create'])->name('admin.products.create');
    Route::post('products', [ProductController::class, 'store'])->name('admin.products.store');
    Route::post('/products/{product}', [ProductController::class, 'update'])->name('admin.products.update');
    Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('admin.products.destroy');
});

Route::prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'index']);
    Route::post('/add', [CartController::class, 'addItem']);
    Route::put('/update/{product}', [CartController::class, 'updateItem']);
    Route::delete('/remove/{product}', [CartController::class, 'removeItem']);
    Route::delete('/clear', [CartController::class, 'clearCart']);
});


Route::get('/', function () {
    return Inertia::render('Welcome', [
        'authUser' => Auth::user(),
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'products' => Product::where('is_vedette', true)->latest()->take(4)->get(),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('home');

Route::get('/products', [ProductController::class, 'index'])->name('products.index');

Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');

Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');

Route::get('/orders/pdf/{order}', [OrderController::class, 'invoice'])->name('order.invoice');

Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::get('admin/dashboard', [DashboardAdminController::class, 'index'])->middleware(['auth', 'verified'])->name('admin.dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/commande/success', function () {
    return Inertia::render('Order/Success', [
        'order' => [
            'order_number' => request('order_number'),
            'total_price' => request('total_price'),
            'invoice_url' => request('invoice_url')
        ],
        'products' => request('products')
    ]);
})->name('orders.success');

require __DIR__.'/auth.php';
