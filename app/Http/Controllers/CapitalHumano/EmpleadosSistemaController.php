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

class EmpleadosSistemaController extends Controller
{
   
    public function changePassword(Request $request)
    {
        $userdata = auth()->user();

        try {
            // data validation
            $request->validate([
                'password' => 'required|confirmed'
            ]);

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
            return response()->json(['error' => $e->getMessage()], 401);
           
        }

        return response()->json([
            "status" => true,
            "message" => "Password Changed successfully"
        ]);
    }

    
    

}
