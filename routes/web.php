<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication routes - accessible to guest users
Route::middleware('guest')->group(function () {
    Route::get('login', function () {
        return view('auth.login');
    })->name('login');
    
    Route::get('register', function () {
        return view('auth.register');
    })->name('register');
    
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::put('profile/password', [AuthController::class, 'updatePassword'])->name('profile.password.update');
    
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Transactions (Cashier) - accessible to all authenticated users
    Route::resource('transactions', TransactionController::class)->except(['update', 'destroy']);
    Route::get('transactions/latest', [TransactionController::class, 'latest'])->name('transactions.latest');
    Route::get('transactions/daily-income', [TransactionController::class, 'dailyIncome'])->name('transactions.daily-income');
    
    // Admin-only routes
    Route::middleware('role:admin')->group(function () {
        // Categories
        Route::resource('categories', CategoryController::class);
        
        // Ingredients
        Route::resource('ingredients', IngredientController::class);
        Route::get('ingredients-json', [IngredientController::class, 'jsonList'])->name('ingredients.json');
    });
    
    // Admin and Cashier routes
    Route::middleware('role:admin,cashier')->group(function () {
        // Products
        Route::resource('products', ProductController::class);
        Route::put('products/{product}/stock', [ProductController::class, 'updateStock'])->name('products.stock.update');
        
        // Expenses
        Route::resource('expenses', ExpenseController::class);
        Route::get('expenses/daily', [ExpenseController::class, 'dailyExpense'])->name('expenses.daily');
        
        // Reports
        Route::get('reports/best-selling-products', [ReportController::class, 'bestSellingProducts'])->name('reports.best-selling-products');
        Route::get('reports/busiest-hours', [ReportController::class, 'busiestHours'])->name('reports.busiest-hours');
        Route::get('reports/daily-sales', [ReportController::class, 'dailySalesReport'])->name('reports.daily-sales');
        Route::get('reports/daily-profit', [ReportController::class, 'dailyProfitReport'])->name('reports.daily-profit');
    });
});