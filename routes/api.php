<?php

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
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\CapitalHumano\PermisosController;
//Route::post("register", [ApiController::class, "register"]);
Route::post("login", [ApiController::class, "login"]);

Route::group([
    "middleware" => ["auth:api"]
], function () {
    Route::get("home", [ApiController::class, "home"]);
    Route::get("refresh", [ApiController::class, "refreshToken"]);
    Route::get("logout", [ApiController::class, "logout"]);
    Route::post('guarda-token', [ApiController::class, "guardaToken"]);
});

Route::group(["middleware" => ["auth:api"], 'namespace' => 'App\Http\Controllers\Produccion'], function () {
    Route::any('recibo-ot-bulto-registros', 'ReciboOTBultoController@bultosRegistros');
    Route::any('recibo-ot-bulto-guarda', 'ReciboOTBultoController@reciboBultoMovil');
});
Route::group(["prefix" => "capital-humano", "middleware" => ["auth:api"], 'namespace' => 'App\Http\Controllers\CapitalHumano'], function () {
    Route::any('permisos/tipos', 'PermisosController@permisosTipos');
    Route::any('permisos/mis-registros', 'PermisosController@getMisPermisos');
    Route::any('permisos/resumen', 'PermisosController@getResumen');
    Route::any('permisos/vacaciones-disponibles', 'PermisosController@getVacacionesDisponibles');
    Route::any('permisos/crear', 'PermisosController@crear');
    Route::any('permisos/eliminar', 'PermisosController@eliminar');
    Route::any('send', 'PermisosController@sendNotification');
    Route::resource('permisos', 'PermisosController');
    Route::post('permisos-delete', 'PermisosController@eliminar');
    Route::post('permisos-update', 'PermisosController@update');

    Route::post('change-password', 'EmpleadosSistemaController@changePassword');
});
