<?php

use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\NotificationController;

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
    
    // Sales Management (place export routes BEFORE resource to avoid route-model binding capturing "export" as {sale})
    Route::get('sales/export', [SaleController::class, 'export'])->name('sales.export');
    Route::get('sales/export/excel', [SaleController::class, 'exportExcel'])->name('sales.export.excel');
    Route::get('sales/export/sales.csv', [SaleController::class, 'exportSalesCsv'])->name('sales.export.sales');
    Route::get('sales/export/items.csv', [SaleController::class, 'exportSaleItemsCsv'])->name('sales.export.items');
    Route::resource('sales', SaleController::class)->except(['edit', 'update']);
    
    // Customer Management
    Route::resource('customers', CustomerController::class);

    // Activity Logs
    Route::get('activity', [ActivityLogController::class, 'index'])->name('activity.index');

    // Analytics
    Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('analytics/products', [AnalyticsController::class, 'products'])->name('analytics.products');
    Route::get('analytics/kpi', [AnalyticsController::class, 'kpi'])->name('analytics.kpi');
    Route::get('analytics/kpi/category-items', [AnalyticsController::class, 'kpiCategoryItems'])->name('analytics.kpi.category-items');

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
});
