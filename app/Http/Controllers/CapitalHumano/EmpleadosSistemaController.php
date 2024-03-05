<?php

namespace App\Http\Controllers\CapitalHumano;

use DB;
use App\Models\User;
use App\Models\Empleados;
use App\Models\UsuariosRPT;
use Illuminate\Support\Arr;

use Illuminate\Http\Request;
use App\Events\UserNotifyEvent;
use Tymon\JWTAuth\Facades\JWTAuth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Sistema\DataBaseSession;
use App\Models\RPT\RPT_CHECADOR_INCIDENCIA AS CHI;

class EmpleadosSistemaController extends Controller
{
   
    public function changePassword(Request $request)
    {
        $userdata = auth()->user();

        try {
            // data validation
           

            $userdata = auth()->user();
            $empleadoId = $userdata['USU_EMP_EmpleadoId'];

            $password = $_POST['password'];

            $user = \DB::table('Usuarios')
                ->join('Empleados', 'EMP_EmpleadoId', '=', 'USU_EMP_EmpleadoId')              
                ->where('USU_EMP_EmpleadoId', '=', $empleadoId)
                ->get();

            DB::table('Usuarios')
                ->where('USU_Nombre', $user->USU_Nombre)
                ->update(['USU_Contrasenia' => $password]);

            $password = Hash::make($password);

            DB::table('RPT_Usuarios')
                ->where('nomina', $user->USU_Nombre)
                ->update(['password' => $password]);

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(array('msg' => $e->getMessage()));
        }

        return response()->json([
            "status" => true,
            "message" => "Password Changed successfully"
        ]);
    }

    
    

}
