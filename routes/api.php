<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClubDeportivo\ClubController;
use App\Http\Controllers\ClubDeportivo\TeacherCacheController;
use App\Http\Controllers\ConfigController;
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

Route::group(['middleware' => 'auth:api'], function () {
    // Route::group(['middleware' => 'user.connection'], function () {

        //club
        Route::post('clubs', [ClubController::class, 'store'])->middleware('check.permiso:crear_club');
        Route::post('update-club', [ClubController::class, 'update'])->middleware('check.permiso:editar_club');
        Route::get('clubs/{id}', [ClubController::class, 'show'])->middleware('check.permiso:ver_club');
        // Route::post('clubs/{id}/update-image', [ClubController::class, 'updateImage'])->middleware('check.permiso:ver_club');

        //propfesores
        Route::post('teachers', [TeacherCacheController::class, 'store'])->middleware('check.permiso:crear_entrenador');
        Route::get('teachersByClub/{club_id}', [TeacherCacheController::class, 'showByCLub'])->middleware('check.permiso:ver_entrenador');
        Route::post('update-teachers', [TeacherCacheController::class, 'update'])->middleware('check.permiso:editar_entrenador');
        Route::post('delete-teachers', [TeacherCacheController::class, 'destroy'])->middleware('check.permiso:eliminar_entrenador');

        //configuraciones
        Route::apiResource('configuraciones', ConfigController::class)->only(['index', 'update'])->middleware('check.permiso:editar_configuracion');
        Route::get('user/config', [ConfigController::class, 'getUserConfig'])->middleware('check.permiso:ver_configuracion');
        Route::get('club/{club}/config', [ConfigController::class, 'getClubConfig'])->middleware('check.permiso:ver_configuracion');
    // });
});

Route::get('/user', function (Request $request) {
    return $request->user();
});
