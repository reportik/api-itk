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

    $menu = DB::select("SELECT mitn.MINVT_Nombre AS MPC_Nodo, MPC_orden, MPC_NombreNodo FROM MenuPrincipalConfiguracion INNER JOIN (select PER_PermisoId, PER_TipoPermiso, PERD_MPC_NodoId
        from Permisos inner join PermisosDetalle on PER_PermisoId = PERD_PER_PermisoId
        inner join Usuarios on USU_PER_PermisoId=PER_PermisoId 
        WHERE USU_Nombre = '777')
        AS aaa ON MPC_NodoId = PERD_MPC_NodoId 
        inner join RPT_Menu_Invtek mit on mit.INVT_MPC_Id = MPC_NodoId
		inner join RPT_Menu_Invtek_Nodos mitn on mitn.MINVT_Id = mit.INVT_Nodo
        WHERE MPC_App = 1 AND MPC_Activo = 1 order by MPC_Orden");

    $nodos = Arr::pluck($menu, 'MPC_Nodo');
    $nodos = array_unique($nodos);
    //return $nodos;
    $menu_app = [];
    foreach ($nodos as $key => $value) {
        $array_w = Arr::where($menu, function($val) use ($value){
            return $val->MPC_Nodo == $value;
        });
        
        $menu_app [$value] = Arr::pluck($array_w, 'MPC_NombreNodo');
    }

    return $menu_app;    

    return collect(DB::select('select EMP_Nombre, EMP_RelacionContacto from Empleados'))->keyBy('EMP_RelacionContacto')->toArray();
    return view('welcome');
});

Route::group(['namespace' => 'App\Http\Controllers\CapitalHumano'], function () {
    Route::any('send', 'PermisosController@sendNotification');
});