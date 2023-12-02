<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

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

        if (!$userToken = JWTAuth::fromUser($user)) {
            return response()->json(['error' => 'invalid_credentials'], 401);
        }
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
