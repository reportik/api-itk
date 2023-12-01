<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    //return view('welcome');
    return DB::select('select EMP_Nombre from Empleados where EMP_EmpleadoId = ?', ['17536E14-762E-44DA-B19A-6E2D1E4C9BA9']);
});
