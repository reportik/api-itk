<?php

namespace App\Http\Controllers\CapitalHumano;

use DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmpleadosSistemaController extends Controller
{
    public function changePassword(Request $request)
    {
        try {
            $userdata = auth()->user();
            if (!$userdata) {
                return response()->json([
                    'Status' => 'Error',
                    'Mensaje' => 'No se encontró el empleado en sesión.',
                ], 401);
            }

            $currentPassword = trim((string) $request->input('current_password', ''));
            $newPassword = trim((string) $request->input('password', ''));
            $confirmation = trim((string) $request->input('password_confirmation', ''));

            if ($currentPassword === '' || $newPassword === '' || $confirmation === '') {
                return response()->json([
                    'Status' => 'Error',
                    'Mensaje' => 'Contraseña actual, nueva y confirmación son obligatorias.',
                ]);
            }

            if ($newPassword !== $confirmation) {
                return response()->json([
                    'Status' => 'Error',
                    'Mensaje' => 'La confirmación de la contraseña no coincide.',
                ]);
            }

            if ($currentPassword === $newPassword) {
                return response()->json([
                    'Status' => 'Error',
                    'Mensaje' => 'La nueva contraseña debe ser diferente a la actual.',
                ]);
            }

            $empleadoId = $userdata['USU_EMP_EmpleadoId'];
            $usuario = DB::table('Usuarios')
                ->where('USU_EMP_EmpleadoId', $empleadoId)
                ->first();

            if (!$usuario) {
                return response()->json([
                    'Status' => 'Error',
                    'Mensaje' => 'No se encontró el usuario.',
                ], 404);
            }

            if ($usuario->USU_Contrasenia !== $currentPassword) {
                return response()->json([
                    'Status' => 'Error',
                    'Mensaje' => 'La contraseña actual no es correcta.',
                ], 401);
            }

            DB::beginTransaction();

            DB::table('Usuarios')
                ->where('USU_Nombre', $usuario->USU_Nombre)
                ->update(['USU_Contrasenia' => $newPassword]);

            DB::table('RPT_Usuarios')
                ->where('nomina', $usuario->USU_Nombre)
                ->update(['password' => Hash::make($newPassword)]);

            DB::commit();

            return response()->json([
                'Status' => 'Valido',
                'Mensaje' => 'Tu contraseña fue actualizada correctamente.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'Status' => 'Error',
                'Mensaje' => 'No fue posible cambiar la contraseña. ' . $e->getMessage(),
            ], 500);
        }
    }
}
