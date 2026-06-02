<?php

namespace App\Http\Controllers\CapitalHumano;

use DB;
use App\Models\Empleados;
use App\Models\UsuariosRPT;
use App\Http\Controllers\Controller;
use App\Models\RPT\RPT_CHECADOR_INCIDENCIA as CHI;

class PermisosController extends Controller
{
    const CHE_VACACIONES = 6;
    const ESTATUS_ENVIADO = 'S';
    const ESTATUS_ARCHIVADO = 'D';

    const MENSAJE_SOLICITUD_ENVIADA = 'Tu solicitud fue enviada, no se considera como autorizada. Esta será resuelta en horario hábil, en un plazo de 24 horas.';

    public function permisosTipos()
    {
        try {
            $tipos = DB::select("
                SELECT CHE_Id AS permisoId, CHE_Estatus AS permiso
                FROM RPT_Checador_ConfigEstatus
                WHERE CHE_Tipo = 'INCIDENCIA' AND CHE_Activo = 1
                ORDER BY CHE_Estatus
            ");

            return response()->json([
                'Status' => 'Valido',
                'data' => $tipos,
                'permisos' => $tipos,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'Status' => 'Error',
                'Mensaje' => 'No fue posible cargar los tipos de permiso. ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getVacacionesDisponibles()
    {
        try {
            $empleado = $this->getCodigoEmpleado();
            if (!$empleado) {
                return response()->json(['Status' => 'Error', 'Mensaje' => 'No se encontró el empleado en sesión.'], 401);
            }

            $saldo = $this->obtenerSaldoVacaciones($empleado);

            return response()->json([
                'Status' => 'Valido',
                'diasDisponibles' => $saldo['diasDisponibles'],
                'diasDerecho' => $saldo['diasDerecho'],
                'diasTomados' => $saldo['diasTomados'],
                'puedeSolicitar' => $saldo['diasDisponibles'] > 0,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'Status' => 'Error',
                'Mensaje' => 'No fue posible consultar las vacaciones disponibles. ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getResumen()
    {
        try {
            $empleado = $this->getCodigoEmpleado();
            if (!$empleado) {
                return response()->json(['Status' => 'Error', 'Mensaje' => 'No se encontró el empleado en sesión.'], 401);
            }

            $permisosUltimoMes = DB::selectOne("
                SELECT COUNT(*) AS total
                FROM RPT_Checador_Incidencias CHI
                WHERE CHI.CHI_Eliminado = 0
                    AND CHI.CHI_EstatusPermiso <> ?
                    AND CHI.CHI_FechaInicio >= DATEADD(month, -1, CAST(GETDATE() AS date))
                    AND RIGHT('000' + CONVERT(varchar, CHI.CHI_EMP_Empleado), 4) = RIGHT('000' + CONVERT(varchar, ?), 4)
            ", [self::ESTATUS_ARCHIVADO, $empleado]);

            $saldo = $this->obtenerSaldoVacaciones($empleado);

            return response()->json([
                'Status' => 'Valido',
                'permisosUltimoMes' => (int) $permisosUltimoMes->total,
                'diasVacacionesDisponibles' => $saldo['diasDisponibles'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'Status' => 'Error',
                'Mensaje' => 'No fue posible cargar el resumen. ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getMisPermisos()
    {
        try {
            $empleado = $this->getCodigoEmpleado();
            if (!$empleado) {
                return response()->json(['Status' => 'Error', 'Mensaje' => 'No se encontró el empleado en sesión.'], 401);
            }

            $data = $this->consultarPermisosEmpleado($empleado);

            return response()->json(['data' => $data, 'Status' => 'Valido']);
        } catch (\Exception $e) {
            return response()->json([
                'Status' => 'Error',
                'Mensaje' => 'No fue posible cargar tus solicitudes. ' . $e->getMessage(),
            ], 500);
        }
    }

    /** @deprecated Usar getMisPermisos */
    public function index()
    {
        try {
            $empleado = $this->getCodigoEmpleado();
            if (!$empleado) {
                return response()->json(['Status' => 'Error', 'Mensaje' => 'No se encontró el empleado en sesión.'], 401);
            }

            $rows = $this->consultarPermisosEmpleado($empleado);
            $permisosUsuario = array_map(function ($row) {
                return (object) [
                    'Id' => (string) $row->Folio,
                    'Estatus' => $row->Estatus,
                    'CHI_FechaInicio' => $row->FechaInicio,
                    'CHI_FechaTermino' => $row->FechaFin,
                    'PermisoId' => '',
                    'PermisoDescripcion' => $row->TipoPermiso,
                    'Descripcion' => $row->Motivo,
                    'NotaRH' => $row->NotaRH,
                    'Duracion' => $row->Duracion,
                ];
            }, $rows);

            return response()->json([
                'Status' => 'Valido',
                'data' => $rows,
                'permisosUsuario' => $permisosUsuario,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'Status' => 'Error',
                'Mensaje' => 'No fue posible cargar tus solicitudes. ' . $e->getMessage(),
            ], 500);
        }
    }

    public function crear()
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        try {
            $empleado = $this->getCodigoEmpleado();
            if (!$empleado) {
                return response()->json(['Status' => 'Error', 'Mensaje' => 'No se encontró el empleado en sesión.'], 401);
            }

            $incidenciaId = (int) request()->input('incidenciaId');
            $fechaInicio = trim((string) request()->input('fechaHoraInicio'));
            $fechaTermino = trim((string) request()->input('fechaHoraTermino'));
            $descripcion = trim((string) request()->input('descripcion'));

            if (!$incidenciaId || !$fechaInicio || !$fechaTermino || $descripcion === '') {
                return response()->json([
                    'Status' => 'Error',
                    'Mensaje' => 'Tipo de permiso, fechas y motivo son obligatorios.',
                ]);
            }

            $validacionFechas = $this->validarFechasSolicitud($fechaInicio, $fechaTermino);
            if ($validacionFechas !== true) {
                return response()->json(['Status' => 'Error', 'Mensaje' => $validacionFechas]);
            }

            if ($incidenciaId === self::CHE_VACACIONES) {
                $saldo = $this->obtenerSaldoVacaciones($empleado);
                if ($saldo['diasDisponibles'] <= 0) {
                    return response()->json([
                        'Status' => 'Error',
                        'Mensaje' => 'No tienes días de vacaciones disponibles para solicitar este permiso.',
                    ]);
                }

                $diasSolicitados = $this->calcularDiasSolicitud($fechaInicio, $fechaTermino);
                if ($diasSolicitados > $saldo['diasDisponibles']) {
                    return response()->json([
                        'Status' => 'Error',
                        'Mensaje' => 'Los días solicitados (' . $diasSolicitados . ') superan tus vacaciones disponibles (' . $saldo['diasDisponibles'] . ').',
                    ]);
                }
            }

            DB::beginTransaction();

            $fila = new CHI();
            $fila->CHI_EMP_Empleado = $empleado;
            $fila->CHI_CHE_Id = $incidenciaId;
            $fila->CHI_FechaInicio = $fechaInicio;
            $fila->CHI_FechaTermino = $fechaTermino;
            $fila->CHI_Descripcion = strtoupper($descripcion);
            $fila->CHI_EstatusPermiso = self::ESTATUS_ENVIADO;
            $fila->CHI_CreadoPor = $empleado;
            $fila->CHI_FechaCreacion = date('d-m-Y H:i:s');
            $fila->CHI_Eliminado = 0;
            $fila->save();

            DB::commit();

            $this->notificarRhNuevoPermiso($fila, $empleado);

            return response()->json([
                'Status' => 'Valido',
                'Mensaje' => self::MENSAJE_SOLICITUD_ENVIADA,
                'folio' => $fila->CHI_Id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'Status' => 'Error',
                'Mensaje' => 'Ocurrió un error al guardar la solicitud. ' . $e->getMessage(),
            ], 500);
        }
    }

    /** @deprecated Usar crear */
    public function store()
    {
        return $this->crear();
    }

    public function eliminar()
    {
        try {
            $empleado = $this->getCodigoEmpleado();
            if (!$empleado) {
                return response()->json(['Status' => 'Error', 'Mensaje' => 'No se encontró el empleado en sesión.'], 401);
            }

            $id = (int) request()->input('id');
            $accion = strtolower(trim((string) request()->input('accion', 'archivar')));

            if (!$id) {
                return response()->json(['Status' => 'Error', 'Mensaje' => 'Folio de solicitud no válido.']);
            }

            $permiso = CHI::find($id);
            if (!$permiso || !$this->perteneceAlEmpleado($permiso, $empleado)) {
                return response()->json(['Status' => 'Error', 'Mensaje' => 'La solicitud no existe o no te pertenece.']);
            }

            if ((int) $permiso->CHI_Eliminado === 1) {
                return response()->json(['Status' => 'Error', 'Mensaje' => 'La solicitud ya fue cancelada.']);
            }

            if ($accion === 'cancelar') {
                if ($permiso->CHI_EstatusPermiso !== self::ESTATUS_ENVIADO) {
                    return response()->json([
                        'Status' => 'Error',
                        'Mensaje' => 'Solo puedes cancelar solicitudes en estatus ENVIADO.',
                    ]);
                }

                $permiso->CHI_Eliminado = 1;
                $permiso->save();

                return response()->json(['Status' => 'Valido', 'Mensaje' => 'Solicitud cancelada correctamente.']);
            }

            if ($permiso->CHI_EstatusPermiso === self::ESTATUS_ARCHIVADO) {
                return response()->json(['Status' => 'Error', 'Mensaje' => 'La solicitud ya está archivada.']);
            }

            if (!in_array($permiso->CHI_EstatusPermiso, ['A', 'R'], true)) {
                return response()->json([
                    'Status' => 'Error',
                    'Mensaje' => 'Solo puedes archivar solicitudes en estatus APROBADO o RECHAZADO.',
                ]);
            }

            $permiso->CHI_EstatusPermiso = self::ESTATUS_ARCHIVADO;
            $permiso->save();

            return response()->json(['Status' => 'Valido', 'Mensaje' => 'Solicitud archivada correctamente.']);
        } catch (\Exception $e) {
            return response()->json([
                'Status' => 'Error',
                'Mensaje' => 'Ocurrió un error al procesar la solicitud. ' . $e->getMessage(),
            ], 500);
        }
    }

    /** @deprecated Usar eliminar */
    public function destroy()
    {
        return $this->eliminar();
    }

    public function update()
    {
        return response()->json([
            'Status' => 'Error',
            'Mensaje' => 'No es posible editar solicitudes después de enviarlas.',
        ], 400);
    }

    public function show($id)
    {
        return response()->json(['Status' => 'Error', 'Mensaje' => 'Consulta no disponible.'], 404);
    }

    public function edit($id)
    {
    }

    public function sendNotification()
    {
        $details = [
            'title' => 'Esto viene de Invtek.',
            'body' => 'Pruebas desde Invtek',
            'foto' => 'SIN FOTO.png',
            'action' => url('#compras/Proveedor'),
            'type' => 'toast',
            'aplicativo' => "'web'",
        ];

        event(new \App\Events\UserNotifyEvent('34', $details));

        return response()->json([
            'status' => true,
            'message' => 'true',
        ]);
    }

    private function getCodigoEmpleado()
    {
        $userdata = auth()->user();

        return $userdata ? $userdata['USU_Nombre'] : null;
    }

    private function consultarPermisosEmpleado($empleado)
    {
        $query = "
            SELECT
                CHI.CHI_Id AS Folio,
                CHE.CHE_Estatus AS TipoPermiso,
                CONVERT(varchar, CHI.CHI_FechaInicio, 120) AS FechaInicio,
                CONVERT(varchar, CHI.CHI_FechaTermino, 120) AS FechaFin,
                CHI.CHI_Descripcion AS Motivo,
                CHI.CHI_EstatusPermiso,
                CASE
                    WHEN CHI.CHI_EstatusPermiso = 'A' THEN 'APROBADO'
                    WHEN CHI.CHI_EstatusPermiso = 'S' THEN 'ENVIADO'
                    WHEN CHI.CHI_EstatusPermiso = 'E' THEN 'EN REVISION'
                    WHEN CHI.CHI_EstatusPermiso = 'R' THEN 'RECHAZADO'
                    WHEN CHI.CHI_EstatusPermiso = 'D' THEN 'ARCHIVADO'
                    WHEN CHI.CHI_EstatusPermiso = 'N' THEN 'NO APLICA'
                    ELSE CHI.CHI_EstatusPermiso
                END AS Estatus,
                COALESCE(CHI.CHI_Nota, '') AS NotaRH,
                DATEDIFF(DAY, CHI.CHI_FechaInicio, CHI.CHI_FechaTermino) + 1 AS Duracion
            FROM RPT_Checador_Incidencias CHI
            INNER JOIN RPT_Checador_ConfigEstatus CHE ON CHE.CHE_Id = CHI.CHI_CHE_Id
            WHERE CHI.CHI_Eliminado = 0
                AND CHI.CHI_EstatusPermiso <> ?
                AND RIGHT('000' + CONVERT(varchar, CHI.CHI_EMP_Empleado), 4) = RIGHT('000' + CONVERT(varchar, ?), 4)
            ORDER BY CHI.CHI_FechaCreacion DESC, CHI.CHI_Id DESC
        ";

        return DB::select($query, [self::ESTATUS_ARCHIVADO, $empleado]);
    }

    private function perteneceAlEmpleado($permiso, $empleado)
    {
        $row = DB::select("
            SELECT TOP 1 CHI_Id
            FROM RPT_Checador_Incidencias
            WHERE CHI_Id = ?
                AND RIGHT('000' + CONVERT(varchar, CHI_EMP_Empleado), 4) = RIGHT('000' + CONVERT(varchar, ?), 4)
        ", [$permiso->CHI_Id, $empleado]);

        return count($row) > 0;
    }

    private function obtenerSaldoVacaciones($empleado)
    {
        $rows = DB::select("exec SP_RPT_VAC_VACACIONES_XEMPLEADO '" . $empleado . "'");

        if (count($rows) === 0) {
            return [
                'diasDerecho' => 0,
                'diasTomados' => 0,
                'diasDisponibles' => 0,
            ];
        }

        $resumen = $rows[0];
        $diasDerecho = isset($resumen->VAC_Derecho) ? (float) $resumen->VAC_Derecho : 0;
        $diasTomados = isset($resumen->VAC_tomadas) ? (float) $resumen->VAC_tomadas : 0;

        return [
            'diasDerecho' => $diasDerecho,
            'diasTomados' => $diasTomados,
            'diasDisponibles' => max(0, $diasDerecho - $diasTomados),
        ];
    }

    private function validarFechasSolicitud($fechaInicio, $fechaTermino)
    {
        $inicio = $this->parseFechaSolicitud($fechaInicio);
        $termino = $this->parseFechaSolicitud($fechaTermino);

        if (!$inicio || !$termino) {
            return 'Las fechas ingresadas no son válidas.';
        }

        $hoy = new \DateTime('today');
        $hoy->setTime(0, 0, 0);
        $inicioDia = clone $inicio;
        $inicioDia->setTime(0, 0, 0);

        if ($inicioDia < $hoy) {
            return 'La fecha de inicio no puede ser anterior a hoy.';
        }

        if ($termino <= $inicio) {
            return 'La fecha de término debe ser posterior a la fecha de inicio.';
        }

        return true;
    }

    private function calcularDiasSolicitud($fechaInicio, $fechaTermino)
    {
        $inicio = $this->parseFechaSolicitud($fechaInicio);
        $termino = $this->parseFechaSolicitud($fechaTermino);

        if (!$inicio || !$termino) {
            return 0;
        }

        return $inicio->diff($termino)->days + 1;
    }

    private function parseFechaSolicitud($fecha)
    {
        $formatos = [
            'd-m-Y H:i:s', 'd-m-Y H:i', 'd-m-Y',
            'd/m/Y H:i:s', 'd/m/Y H:i', 'd/m/Y',
            'Y-m-d H:i:s', 'Y-m-d',
        ];

        foreach ($formatos as $formato) {
            $dt = \DateTime::createFromFormat($formato, $fecha);
            if ($dt instanceof \DateTime) {
                return $dt;
            }
        }

        $timestamp = strtotime($fecha);

        return $timestamp ? (new \DateTime())->setTimestamp($timestamp) : null;
    }

    private function notificarRhNuevoPermiso($fila, $empleado)
    {
        try {
            $rh_empleados = DB::select("
                SELECT u.USU_Nombre
                FROM RPT_EmpleadoCamposAdicionales eca
                INNER JOIN Empleados e ON e.EMP_EmpleadoId = eca.ECA_EmpleadoId
                INNER JOIN Usuarios u ON u.USU_EMP_EmpleadoId = e.EMP_EmpleadoId
                WHERE eca.ECA_Eliminado = 0
                    AND eca.ECA_GestionaPermisosNotificacion = 1
                    AND e.EMP_Activo = 1
            ");

            $userdata = auth()->user();
            $empleadoId = $userdata['USU_EMP_EmpleadoId'] ?? null;
            if (!$empleadoId) {
                return;
            }

            $empleadoRemitente = Empleados::find($empleadoId);
            if (!$empleadoRemitente) {
                return;
            }

            $tituloMensaje = 'Nuevo Permiso Folio #' . $fila->CHI_Id . '. (' . $empleadoRemitente->EMP_Nombre . ' ' . $empleadoRemitente->EMP_PrimerApellido . ')';
            $cuerpoMensaje = '¿Desea revisar el permiso ahora?';
            $fotoMensaje = (is_null($empleadoRemitente->EMP_Fotografia)) ? 'SIN FOTO.png' : $empleadoRemitente->EMP_Fotografia;
            $accionMensaje = url('#capital-humano/permisos/' . $empleadoRemitente->EMP_EmpleadoId);

            foreach ($rh_empleados as $value) {
                $this->sendAlert($value->USU_Nombre, $tituloMensaje, $cuerpoMensaje, 'alert', $fotoMensaje, $accionMensaje);
            }
        } catch (\Exception $e) {
            // La notificación no debe impedir guardar la solicitud.
        }
    }

    public function sendAlert($codigoEmpDestinatario, $tituloMensaje, $cuerpoMensaje, $tipoMensaje, $fotoMensaje, $accionMensaje)
    {
        $user = UsuariosRPT::where('nomina', $codigoEmpDestinatario)->first();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Usuario no encontrado']);
        }

        $details = [
            'title' => $tituloMensaje,
            'body' => $cuerpoMensaje,
            'type' => $tipoMensaje,
            'foto' => $fotoMensaje,
            'action' => $accionMensaje,
            'aplicativo' => "'mi-nomina', 'web'",
        ];

        $user->storeNewNotification($details);
        $user->fcmNotification($details);

        return response()->json([
            'status' => true,
            'message' => 'Notificado',
        ]);
    }
}
