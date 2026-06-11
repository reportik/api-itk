<?php

namespace App\Services;

use DB;
use Log;
use App\Models\Empleados;
use App\Models\UsuariosRPT;
use App\Events\UserNotifyEvent;

class RhPermisosNotificacionService
{
    const LOG_PREFIX = '[RH_NOTIF]';

    public function notificarNuevoPermiso($folio, $empleadoId)
    {
        $this->notificarGestores('permiso', $folio, $empleadoId, function ($empleadoRemitente) use ($folio) {
            return [
                'titulo' => 'Nuevo Permiso Folio #' . $folio . '. ('
                    . $empleadoRemitente->EMP_Nombre . ' ' . $empleadoRemitente->EMP_PrimerApellido . ')',
                'cuerpo' => '¿Desea revisar el permiso ahora?',
            ];
        });
    }

    public function notificarCambioPerfil($folio, $empleadoId, $totalCambios)
    {
        $this->notificarGestores('cambio_perfil', $folio, $empleadoId, function ($empleadoRemitente) use ($folio, $totalCambios) {
            return [
                'titulo' => 'Solicitud cambio de perfil Folio #' . $folio . '. ('
                    . $empleadoRemitente->EMP_Nombre . ' ' . $empleadoRemitente->EMP_PrimerApellido . ')',
                'cuerpo' => 'El empleado solicitó modificar ' . $totalCambios . ' dato(s). ¿Desea revisar la solicitud?',
            ];
        });
    }

    public function enviarAlerta(
        $codigoEmpDestinatario,
        $tituloMensaje,
        $cuerpoMensaje,
        $tipoMensaje,
        $fotoMensaje,
        $accionMensaje,
        $contexto = 'manual',
        $folio = null
    ) {
        Log::info(self::LOG_PREFIX . ' enviarAlerta inicio', [
            'contexto' => $contexto,
            'folio' => $folio,
            'destinatario' => $codigoEmpDestinatario,
        ]);

        $user = UsuariosRPT::where('nomina', $codigoEmpDestinatario)->first();
        if (!$user) {
            Log::warning(self::LOG_PREFIX . ' Usuario RPT no encontrado', [
                'contexto' => $contexto,
                'folio' => $folio,
                'destinatario' => $codigoEmpDestinatario,
            ]);
            throw new \RuntimeException('Usuario RPT no encontrado: ' . $codigoEmpDestinatario);
        }

        $details = [
            'title' => $tituloMensaje,
            'body' => $cuerpoMensaje,
            'type' => $tipoMensaje,
            'foto' => $fotoMensaje,
            'action' => $accionMensaje,
            'aplicativo' => "'mi-nomina', 'web'",
        ];

        $this->ejecutarPasoNotificacion('storeNewNotification', $contexto, $folio, $user->id, function () use ($user, $details) {
            $user->storeNewNotification($details);
        });

        $this->ejecutarPasoNotificacion('UserNotifyEvent', $contexto, $folio, $user->id, function () use ($user, $details) {
            event(new UserNotifyEvent($user->id, $details));
        });

        $this->ejecutarPasoNotificacion('fcmNotification', $contexto, $folio, $user->id, function () use ($user, $details) {
            $user->fcmNotification($details);
        });

        Log::info(self::LOG_PREFIX . ' enviarAlerta completado', [
            'contexto' => $contexto,
            'folio' => $folio,
            'destinatario' => $codigoEmpDestinatario,
            'userId' => $user->id,
        ]);
    }

    private function notificarGestores($contexto, $folio, $empleadoId, callable $mensajesBuilder)
    {
        Log::info(self::LOG_PREFIX . ' inicio', [
            'contexto' => $contexto,
            'folio' => $folio,
            'empleadoId' => $empleadoId,
        ]);

        try {
            if (!$empleadoId) {
                Log::warning(self::LOG_PREFIX . ' empleadoId vacío', [
                    'contexto' => $contexto,
                    'folio' => $folio,
                ]);
                return;
            }

            $gestoresRh = $this->obtenerGestoresRh();
            $empleadoRemitente = Empleados::find($empleadoId);

            Log::info(self::LOG_PREFIX . ' destinatarios localizados', [
                'contexto' => $contexto,
                'folio' => $folio,
                'gestoresRh' => count($gestoresRh),
                'empleadoRemitente' => $empleadoRemitente ? $empleadoRemitente->EMP_EmpleadoId : null,
            ]);

            if (!$empleadoRemitente) {
                Log::warning(self::LOG_PREFIX . ' empleado remitente no encontrado', [
                    'contexto' => $contexto,
                    'folio' => $folio,
                    'empleadoId' => $empleadoId,
                ]);
                return;
            }

            if (count($gestoresRh) === 0) {
                Log::warning(self::LOG_PREFIX . ' sin gestores RH configurados', [
                    'contexto' => $contexto,
                    'folio' => $folio,
                ]);
                return;
            }

            $mensajes = $mensajesBuilder($empleadoRemitente);
            $fotoMensaje = is_null($empleadoRemitente->EMP_Fotografia) ? 'SIN FOTO.png' : $empleadoRemitente->EMP_Fotografia;
            $accionMensaje = url('#capital-humano/permisos');

            $enviados = 0;
            $fallidos = 0;

            foreach ($gestoresRh as $gestor) {
                try {
                    $this->enviarAlerta(
                        $gestor->USU_Nombre,
                        $mensajes['titulo'],
                        $mensajes['cuerpo'],
                        'alert',
                        $fotoMensaje,
                        $accionMensaje,
                        $contexto,
                        $folio
                    );
                    $enviados++;
                } catch (\Throwable $e) {
                    $fallidos++;
                    Log::error(self::LOG_PREFIX . ' fallo destinatario', [
                        'contexto' => $contexto,
                        'folio' => $folio,
                        'destinatario' => $gestor->USU_Nombre,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info(self::LOG_PREFIX . ' fin', [
                'contexto' => $contexto,
                'folio' => $folio,
                'enviados' => $enviados,
                'fallidos' => $fallidos,
            ]);
        } catch (\Throwable $e) {
            Log::error(self::LOG_PREFIX . ' error general', [
                'contexto' => $contexto,
                'folio' => $folio,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    private function ejecutarPasoNotificacion($paso, $contexto, $folio, $userId, callable $callback)
    {
        try {
            $callback();
            Log::info(self::LOG_PREFIX . ' paso OK', [
                'paso' => $paso,
                'contexto' => $contexto,
                'folio' => $folio,
                'userId' => $userId,
            ]);
        } catch (\Throwable $e) {
            Log::error(self::LOG_PREFIX . ' paso FALLO', [
                'paso' => $paso,
                'contexto' => $contexto,
                'folio' => $folio,
                'userId' => $userId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function obtenerGestoresRh()
    {
        return DB::select("
            SELECT u.USU_Nombre
            FROM RPT_EmpleadoCamposAdicionales eca
            INNER JOIN Empleados e ON e.EMP_EmpleadoId = eca.ECA_EmpleadoId
            INNER JOIN Usuarios u ON u.USU_EMP_EmpleadoId = e.EMP_EmpleadoId
            WHERE eca.ECA_Eliminado = 0
                AND eca.ECA_GestionaPermisosNotificacion = 1
                AND e.EMP_Activo = 1
        ");
    }
}
