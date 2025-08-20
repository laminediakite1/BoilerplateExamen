<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\CategoriesController;
use App\Http\Controllers\Admin\ProductsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('admin.dashboard.index');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Routes d'administration
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');
    
    // Users
    Route::resource('users', UsersController::class);
    Route::post('users/bulk-action', [UsersController::class, 'bulkAction'])->name('users.bulk-action');
    Route::get('users/export', [UsersController::class, 'export'])->name('users.export');
    
    // Categories
    Route::resource('categories', CategoriesController::class);
    Route::post('categories/bulk-action', [CategoriesController::class, 'bulkAction'])->name('categories.bulk-action');
    Route::get('categories/export', [CategoriesController::class, 'export'])->name('categories.export');
    
    // Products
    Route::resource('products', ProductsController::class);
    Route::post('products/bulk-action', [ProductsController::class, 'bulkAction'])->name('products.bulk-action');
    Route::get('products/export', [ProductsController::class, 'export'])->name('products.export');
});

require __DIR__.'/auth.php';