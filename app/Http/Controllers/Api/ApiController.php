<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Empleados;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use DB;
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

        if (count($user) == 0) {
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
        //USU_EMP_EmpleadoId
        //USU_PER_PermisoId
        return response()->json([
            "usuarioId" => $userdata['USU_UsuarioId'],
            "usuarioNombre" => $userdata['USU_UsuarioId'],
        ]);
    }
    public function home()
    {
        $userdata = auth()->user();
        $empleadoId = $userdata['USU_EMP_EmpleadoId'];
        $empleado = Empleados::find($empleadoId);
        //USU_EMP_EmpleadoId
        //USU_PER_PermisoId 
        $menu = DB::select("SELECT MPC_orden, MPC_NombreNodo FROM MenuPrincipalConfiguracion INNER JOIN (select PER_PermisoId, PER_TipoPermiso, PERD_MPC_NodoId
        from Permisos inner join PermisosDetalle on PER_PermisoId = PERD_PER_PermisoId
        inner join Usuarios on USU_PER_PermisoId=PER_PermisoId WHERE USU_UsuarioId = ?)
        AS aaa ON MPC_NodoId = PERD_MPC_NodoId WHERE MPC_App = 1 AND MPC_Activo = 1 order by MPC_Orden", [$userdata['USU_UsuarioId']]);
        
        return response()->json([
            "usuarioId" => $userdata['USU_UsuarioId'],
            "usuarioNombre" => $empleado->EMP_CodigoEmpleado . ' ' . $empleado->EMP_Nombre .' '. $empleado->EMP_PrimerApellido .' '. $empleado->EMP_SegundoApellido,
            "usuarioMenu" => $menu
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
