<?php

use App\Http\Controllers\Api\Admin\AuthController;
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
Route::group(['middleware' => ['jwt.auth']], function () {
    Route::post('logout', [ApiAuthController::class, 'logout']);
    Route::post('/admin/logout', [AuthController::class, 'logout']);
    Route::put('/admin/users/{user_id}/{action}', [UserController::class, 'blockUser']);
});
Route::post('/login', [ApiAuthController::class, 'login']);
Route::post('/admin/login', [AuthController::class, 'login']);
Route::post('/register', [ApiAuthController::class, 'register']);
