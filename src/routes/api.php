<?php

use App\Http\Controllers\AddressesController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
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

    Route::get('/user/me', [AuthController::class, 'showMe']);
    Route::put('/user/me', [AuthController::class, 'update']);
    Route::post('/user/create-moderator', [AuthController::class, 'createModerator']);
    Route::delete('/user/me', [AuthController::class, 'destroy']);
    Route::post('/address/create', [AddressesController::class, 'create']);
    Route::get('/address/user/{user}', [AddressesController::class, 'getUserAddresses']);
    Route::delete('/address/{addresses}', [AddressesController::class, 'destroy']);
    Route::put('/address/{addresses}', [AddressesController::class, 'update']);
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


