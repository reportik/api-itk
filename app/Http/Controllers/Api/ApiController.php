<?php

namespace App\Http\Controllers\Api;

use DB;
use App\Models\User;
use App\Models\Empleados;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class ApiController extends Controller
{
    // User Register (POST, formdata)
    public function register(Request $request)
    {

        // data validation
        $request->validate([
            "name" => "required",
            "email" => "required|email|unique:users",
            "password" => "required|confirmed"
        ]);

        // User Model
        User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => Hash::make($request->password)
        ]);

        // Response
        return response()->json([
            "status" => true,
            "message" => "User registered successfully"
        ]);
    }

    // User Login (POST, formdata)
    public function login(Request $request)
    {

        // data validation
        $request->validate([
            "user" => "required",
            "password" => "required"
        ]);

        // JWTAuth
        $user = User::where('USU_Nombre', $request->user)->where('USU_Contrasenia', $request->password)->first();

        if (is_null($user)) {
            return response()->json(['error' => 'invalid_credentials'], 401);
        }
        $userToken = JWTAuth::fromUser($user);
        //JWTAuth::setToken($userToken)->toUser();
        return response()->json(compact('userToken'));
       
    }

    // User Profile (GET)
    public function profile()
    {
        $userdata = auth()->user();
        $empleado = DB::select("SELECT EMP_EmpleadoId, e.EMP_CodigoEmpleado, e.EMP_Fotografia, 
            e.EMP_Nombre +' '+ e.EMP_PrimerApellido +' '+ e.EMP_SegundoApellido AS EMP_Nombre 
            , d.DEP_DeptoId, d.DEP_Nombre, d.DEP_Codigo
            FROM Empleados e
            LEFT JOIN Departamentos d on d.DEP_DeptoId = e.EMP_DEP_DeptoId
            WHERE EMP_EmpleadoId = ? ", [$userdata['USU_EMP_EmpleadoId']])[0];
        if (!$empleado) {
            return response()->json(['error' => 'user not found'], 404);
        }

        return response()->json([
            "EMP_EmpleadoId" => $empleado->EMP_EmpleadoId,
            "EMP_CodigoEmpleado" => $empleado->EMP_CodigoEmpleado,
            "EMP_Fotografia" => $empleado->EMP_Fotografia,
            "EMP_Nombre" => $empleado->EMP_Nombre,
            "DEP_DeptoId" => $empleado->DEP_DeptoId,
            "DEP_Nombre" => $empleado->DEP_Nombre, 
            "DEP_Codigo" => $empleado->DEP_Codigo
        ]);
    }
    public function home()
    {
        $userdata = auth()->user();
        $empleadoId = $userdata['USU_EMP_EmpleadoId'];
        //$empleado = Empleados::find($empleadoId);
        
           
        //USU_PER_PermisoId 
        $menu = DB::select("SELECT  mitn.MINVT_Nombre AS MPC_Nodo, MPC_NombreNodo FROM MenuPrincipalConfiguracion INNER JOIN (select PER_PermisoId, PER_TipoPermiso, PERD_MPC_NodoId
        from Permisos inner join PermisosDetalle on PER_PermisoId = PERD_PER_PermisoId
        inner join Usuarios on USU_PER_PermisoId=PER_PermisoId WHERE USU_UsuarioId = ?)
        AS aaa ON MPC_NodoId = PERD_MPC_NodoId 
         inner join RPT_Menu_Invtek mit on mit.INVT_MPC_Id = MPC_NodoId
		inner join RPT_Menu_Invtek_Nodos mitn on mitn.MINVT_Id = mit.INVT_Nodo
        WHERE MPC_App = 1 AND MPC_Activo = 1 order by MPC_Orden", [$userdata['USU_UsuarioId']]);
        
        $nodos = Arr::pluck($menu, 'MPC_Nodo');
        $nodos = array_unique($nodos);
        //return $nodos;
        $menu_app = [];
        foreach ($nodos as $key => $value) {
            $array_w = Arr::where($menu, function ($val) use ($value) {
                return $val->MPC_Nodo == $value;
            });

            $menu_app[$value] = Arr::pluck($array_w, 'MPC_NombreNodo');
        }
        return response()->json([
            "usuarioId" => $userdata['USU_UsuarioId'],
            //"usuarioNombre" => $empleado->EMP_CodigoEmpleado . ' ' . $empleado->EMP_Nombre .' '. $empleado->EMP_PrimerApellido .' '. $empleado->EMP_SegundoApellido,
            "usuarioMenu" => $menu_app
        ]);
    }

    // To generate refresh token value
    public function refreshToken()
    {

        $newToken = auth()->refresh();

        return response()->json([
            "message" => "New access token",
            "token" => $newToken
        ]);
    }

    // User Logout (GET)
    public function logout()
    {

        auth()->logout();

        return response()->json([
            "message" => "User logged out successfully"
        ]);
    }
}
