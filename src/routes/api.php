<?php

use App\Http\Controllers\AddressesController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\DiscountsController;
use App\Models\categories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user/profile', function (Request $request) {
    return $request->user();

});

Route::middleware('auth:sanctum')->group(function () {

    // ROTAS DE USUÁRIOS
    Route::get('/user/me', [AuthController::class, 'showMe']);
    Route::put('/user/me', [AuthController::class, 'update']);
    Route::post('/user/create-moderator', [AuthController::class, 'createModerator']);
    Route::delete('/user/me', [AuthController::class, 'destroy']);

    // ROTAS DE ENDEREÇOS
    Route::post('/address/create', [AddressesController::class, 'create']);
    Route::get('/address/me', [AddressesController::class, 'myAddresses']);
    Route::get('/user/{user}/addresses', [AddressesController::class, 'getUserAddresses']);
    Route::get('/address/user/{user}', [AddressesController::class, 'getUserAddresses']);
    Route::get('/address/{addresses}', [AddressesController::class, 'show']);
    Route::delete('/address/{addresses}', [AddressesController::class, 'destroy']);
    Route::put('/address/{addresses}', [AddressesController::class, 'update']);

    // ROTAS DE CATEGORIAS
    Route::post('/categories', [CategoriesController::class, 'createCategorie']);
    Route::put('/categories/{id}', [CategoriesController::class, 'updateCategorie']);
    Route::delete('/categories/{id}', [CategoriesController::class, 'destroyCategorie']);

    // ROTAS DE PRODUTOS
    Route::post('/products', [ProductsController::class, 'createAnProduct']);
    Route::put('/products/{id}', [ProductsController::class, 'updateProduct']);
    Route::delete('/products/{id}', [ProductsController::class, 'destroyProduct']);
    Route::get('/products/user/{userId}', [ProductsController::class, 'getProductsByUser']);
    Route::put('/products/{id}/stock', [ProductsController::class, 'updateStockProduct']);
    
    // ROTAS DE DESCONTO
    Route::post('/discount', [DiscountsController::class, 'createAnDiscount']);
    Route::put('/discount/{id}', [DiscountsController::class, 'updateAnDiscount']);
    Route::delete('/discount/{id}', [DiscountsController::class, 'destroyAnDiscount']);
});


Route::post('/login', function (Request $request) {
    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {
        $user = $request->user();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            "access_token" => $token,
            "token_type" => "Bearer",
        ]);
    }

    return response()->json([
        "message" => "Invalid credentials",
    ], 401);

});

Route::post('/register', [AuthController::class, 'register']);
Route::get('/categories', [CategoriesController::class, 'getAllCategories']);
Route::get('/products', [ProductsController::class, 'getAllProducts']);
Route::get('/products/{id}', [ProductsController::class, 'showProduct']);
Route::get('/products/category/{categoryId}', [ProductsController::class, 'getProductsByCategory']);
Route::get('/categories/{categories}', [CategoriesController::class, 'getCategoriesID']);
Route::get('/discount', [DiscountsController::class, 'getAllDiscounts']);


