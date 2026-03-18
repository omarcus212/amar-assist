<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Rotas públicas
Route::post('/login', [AuthController::class, 'login'])->name('auth.login');

// Rotas protegidas
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);

    // Products
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');

    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');

    Route::put('/products/desactive/{id}', [ProductController::class, 'desactive'])->name('products.desactive');
    Route::put('/products/restore/{id}', [ProductController::class, 'activate'])->name('products.activate');

    Route::delete('/products/{id}/images/{imageId}', [ProductController::class, 'removeImage'])->name('products.removeImage');

    // Users
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');

    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');

    Route::put('/users/desactive/{id}', [UserController::class, 'desactive'])->name('users.desactive');

});

