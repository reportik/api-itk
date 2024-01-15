<?php

namespace App\Http\Controllers\CapitalHumano;

use DB;
use App\Models\User;
use App\Models\Empleados;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class PermisosController extends Controller
{
    // 
    public function permisosTipos()
    {
        $permisos = DB::select("SELECT CHE_id AS permisoId, CHE_Estatus AS permiso FROM RPT_Checador_ConfigEstatus WHERE CHE_Tipo = 'INCIDENCIA' AND CHE_Activo = 1");
    
        return response()->json([
            "permisos" => $permisos
        ]);
    }

}
