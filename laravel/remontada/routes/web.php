<?php

use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // User Management (only for owners)
    Route::resource('users', UserManagementController::class);
    
    // Category Management
    Route::resource('categories', CategoryController::class);
    
    // Product & Inventory Management
    Route::resource('products', ProductController::class);
    Route::get('products/next-sku/{prefix}', [ProductController::class, 'getNextSKU'])->name('products.next-sku');
    Route::post('products/{product}/adjust-stock', [ProductController::class, 'adjustStock'])->name('products.adjust-stock');
    
    // Sales Management
    Route::resource('sales', SaleController::class)->except(['edit', 'update']);
    
    // Customer Management
    Route::resource('customers', CustomerController::class);
});
