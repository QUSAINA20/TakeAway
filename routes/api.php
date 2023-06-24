<?php

use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\AuthController as ApiAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('logout', [ApiAuthController::class, 'logout']);
Route::post('/admin/logout', [AuthController::class, 'logout']);
Route::post('/admin/create-user', [AuthController::class, 'createUser']);
Route::put('/admin/users/block-unblock', [UserController::class, 'blockUser']);
Route::get('/admin/users', [UserController::class, 'showAllUsers']);
Route::get('/admin/users/{user_id}', [UserController::class, 'showOneUser']);
Route::post('/login', [ApiAuthController::class, 'login']);
Route::post('/register', [ApiAuthController::class, 'register']);
Route::post('/admin/login', [AuthController::class, 'login']);
