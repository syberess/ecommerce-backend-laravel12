<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductTestController;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Http\Controllers\CartController;

Route::get('login', function () {
    return response()->json(['error' => 'Unauthorized'], 401);
})->name('login');
/*
|--------------------------------------------------------------------------
| TEST ROUTE
|--------------------------------------------------------------------------
*/
// Route::get('/test', fn () => response()->json(['status' => 'api loaded']));


/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });
});


/*
|--------------------------------------------------------------------------
| CATEGORY ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/{id}', [CategoryController::class, 'show']);

    Route::post('/', [CategoryController::class, 'store'])
        ->middleware(['auth:api', 'role:admin,seller']);

    Route::put('/{id}', [CategoryController::class, 'update'])
        ->middleware(['auth:api', 'role:admin,seller']);

    Route::delete('/{id}', [CategoryController::class, 'destroy'])
        ->middleware(['auth:api', 'role:admin']);
});


/*
|--------------------------------------------------------------------------
| PRODUCT ROUTES
|--------------------------------------------------------------------------
| GET -> herkese açık
| POST/PUT -> giriş yapan kullanıcı
| DELETE -> yalnızca admin
*/


Route::prefix('products')->group(function () {
    // 🔹 Temel CRUD
    Route::get('/', [ProductController::class, 'index']);
    Route::post('/', [ProductController::class, 'store'])->middleware('auth:api');
    Route::put('/{id}', [ProductController::class, 'update'])->middleware('auth:api');
    Route::delete('/{id}', [ProductController::class, 'destroy'])
        ->middleware(['auth:api', 'role:admin']);

    // 🔹 Gelişmiş özellikler
    Route::get('/search', [ProductController::class, 'search']);
    Route::get('/filter', [ProductController::class, 'filterByCategory']);
    Route::get('/paginate', [ProductController::class, 'paginate']);
    Route::get('/filter-paginate', [ProductController::class, 'paginateWithFilters']);

    // ⚠️ DİNAMİK ROTA EN SONA GELMELİ
    Route::get('/{id}', [ProductController::class, 'show']);
});




Route::prefix('orders')->middleware('auth:api')->group(function () {
    // 🔹 Sadece giriş yapan kullanıcı kendi siparişlerini görür
    Route::get('/', [App\Http\Controllers\OrderController::class, 'index']);

    // 🔹 Tek sipariş (kullanıcı sadece kendine ait olanı görür)
    Route::get('/{id}', [App\Http\Controllers\OrderController::class, 'show']);

    // 🔹 Yeni sipariş oluşturma (herkes kendi adına)
    Route::post('/', [App\Http\Controllers\OrderController::class, 'store']);

    // 🔹 Admin: tüm siparişleri gör
    Route::get('/all', [App\Http\Controllers\OrderController::class, 'all'])
        ->middleware('role:admin');

    // 🔹 Admin: belirli kullanıcının siparişlerini gör
    Route::get('/user', [App\Http\Controllers\OrderController::class, 'getByUser'])
        ->middleware('role:admin');

    // 🔹 Admin: sipariş durumunu güncelle
    Route::put('/{id}/status', [App\Http\Controllers\OrderController::class, 'updateStatus'])
        ->middleware('role:admin');

    // 🔹 Admin: sipariş loglarını görüntüle
    Route::get('/{id}/logs', [App\Http\Controllers\OrderController::class, 'logs']);
});



Route::prefix('payments')->middleware('auth:api')->group(function () {
    Route::post('/', [App\Http\Controllers\PaymentController::class, 'store']);
    Route::get('/{orderId}', [App\Http\Controllers\PaymentController::class, 'show']);
    Route::put('/{id}/status', [App\Http\Controllers\PaymentController::class, 'updateStatus'])
        ->middleware('role:admin');
});

Route::prefix('reports')->middleware(['auth:api', 'role:admin'])->group(function () {
    Route::get('/sales', [App\Http\Controllers\ReportController::class, 'sales']);
});

// Route::prefix('cart')->middleware('auth:api')->group(function () {
//     Route::get('/', [App\Http\Controllers\CartController::class, 'index']);
//     Route::post('/', [App\Http\Controllers\CartController::class, 'store']);
//     Route::put('/{id}', [App\Http\Controllers\CartController::class, 'update']);
//     Route::delete('/{id}', [App\Http\Controllers\CartController::class, 'destroy']);
//     Route::delete('/', [App\Http\Controllers\CartController::class, 'clear']);
// });

Route::middleware('auth:api')->group(function () {
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/items', [CartController::class, 'store']);
    // routes/api.php
    Route::match(['put','patch'], '/cart/items/{id}', [CartController::class, 'update']);

    Route::delete('/cart/items/{id}', [CartController::class, 'destroy']);
    Route::delete('/cart', [CartController::class, 'clear']);
});



Route::prefix('orders')->middleware('auth:api')->group(function () {
    Route::post('/from-cart', [App\Http\Controllers\OrderController::class, 'createFromCart']);
});


Route::get('/ok', fn () => tap(['ok' => true], fn() => Log::info('ok')));
Route::get('/crash', fn () => throw new \RuntimeException('Manual crash'));

Route::post('/validate-test', function (Request $r) {
    $r->validate(['name' => 'required']);
    return ['ok' => true];
});