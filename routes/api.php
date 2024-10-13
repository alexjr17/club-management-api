<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClubDeportivo\ClubController;
use Illuminate\Http\Request;
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

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

// Ruta para enviar el enlace de recuperación de contraseña
Route::post('password/email', [AuthController::class, 'sendResetLinkEmail']);

// Ruta para restablecer la contraseña
Route::post('password/reset', [AuthController::class, 'reset']);

// Route::group(['middleware' => 'auth:api'], function () {
//     Route::group(['middleware' => 'user.connection'], function () {
        Route::Post('clubs', [ClubController::class, 'store']);
        Route::put('clubs/{id}', [ClubController::class, 'update']);
        Route::get('clubs/{id}', [ClubController::class, 'show']);
//     });
// });

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

