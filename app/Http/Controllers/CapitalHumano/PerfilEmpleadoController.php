<?php

namespace App\Http\Controllers\CapitalHumano;

use DB;
use App\Models\UsuariosRPT;
use App\Events\UserNotifyEvent;
use App\Http\Controllers\Controller;
use App\Models\RPT\RPT_CHECADOR_INCIDENCIA as CHI;

class PerfilEmpleadoController extends Controller
{
    const ESTATUS_ENVIADO = 'S';
    const MARCA_CAMBIO_PERFIL = '[CAMBIO_PERFIL]';
    const JSON_DELIMITER = '||JSON||';

    public function getMiPerfil()
    {
        try {
            $empleadoId = $this->getEmpleadoId();
            if (!$empleadoId) {
                return response()->json(['Status' => 'Error', 'Mensaje' => 'No se encontró el empleado en sesión.'], 401);
            }

            return response()->json([
                'Status' => 'Valido',
                'data' => $this->obtenerDatosPerfil($empleadoId),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'Status' => 'Error',
                'Mensaje' => 'No fue posible cargar tu perfil. ' . $e->getMessage(),
            ], 500);
        }
    }

    public function solicitarCambios()
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        try {
            $empleadoId = $this->getEmpleadoId();
            $codigoEmpleado = $this->getCodigoEmpleado();
            if (!$empleadoId || !$codigoEmpleado) {
                return response()->json(['Status' => 'Error', 'Mensaje' => 'No se encontró el empleado en sesión.'], 401);
            }

            $cambiosRaw = request()->input('cambios');
            if (is_string($cambiosRaw)) {
                $cambios = json_decode($cambiosRaw, true);
            } else {
                $cambios = $cambiosRaw;
            }

            if (!is_array($cambios) || count($cambios) === 0) {
                return response()->json([
                    'Status' => 'Error',
                    'Mensaje' => 'No se detectaron cambios para enviar.',
                ]);
            }

            $cambiosNormalizados = $this->normalizarCambios($cambios);
            if (count($cambiosNormalizados) === 0) {
                return response()->json([
                    'Status' => 'Error',
                    'Mensaje' => 'No se detectaron cambios válidos para enviar.',
                ]);
            }

            $cheId = $this->obtenerCheIdSolicitoCambio();
            if (!$cheId) {
                return response()->json([
                    'Status' => 'Error',
                    'Mensaje' => 'El tipo de solicitud "SOLICITO CAMBIO" no está configurado. Contacta a Recursos Humanos.',
                ]);
            }

            $pendiente = DB::selectOne("
                SELECT TOP 1 CHI_Id
                FROM RPT_Checador_Incidencias
                WHERE CHI_Eliminado = 0
                    AND CHI_EstatusPermiso IN ('S', 'E')
                    AND CHI_CHE_Id = ?
                    AND RIGHT('000' + CONVERT(varchar, CHI_EMP_Empleado), 4) = RIGHT('000' + CONVERT(varchar, ?), 4)
            ", [$cheId, $codigoEmpleado]);

            if ($pendiente) {
                return response()->json([
                    'Status' => 'Error',
                    'Mensaje' => 'Ya tienes una solicitud de cambio de perfil pendiente (Folio #' . $pendiente->CHI_Id . ').',
                ]);
            }

            $hoy = date('d-m-Y');
            $fechaInicio = $hoy . ' 08:00:00';
            $fechaTermino = $hoy . ' 18:00:00';
            $descripcion = $this->construirDescripcionCambios($cambiosNormalizados);

            DB::beginTransaction();

            $fila = new CHI();
            $fila->CHI_EMP_Empleado = $codigoEmpleado;
            $fila->CHI_CHE_Id = $cheId;
            $fila->CHI_FechaInicio = $fechaInicio;
            $fila->CHI_FechaTermino = $fechaTermino;
            $fila->CHI_Descripcion = $descripcion;
            $fila->CHI_EstatusPermiso = self::ESTATUS_ENVIADO;
            $fila->CHI_CreadoPor = $codigoEmpleado;
            $fila->CHI_FechaCreacion = date('d-m-Y H:i:s');
            $fila->CHI_Eliminado = 0;
            $fila->save();

            DB::commit();

            $this->notificarRhSolicitudCambio($fila, $codigoEmpleado, count($cambiosNormalizados));

            return response()->json([
                'Status' => 'Valido',
                'Mensaje' => 'Tu solicitud fue enviada, no se considera como autorizada. Te notificaremos cuando sea resuelta.',
                'folio' => $fila->CHI_Id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'Status' => 'Error',
                'Mensaje' => 'Ocurrió un error al enviar la solicitud. ' . $e->getMessage(),
            ], 500);
        }
    }

    private function obtenerDatosPerfil($empleadoId)
    {
        $row = DB::selectOne("
            SELECT
                e.EMP_CodigoEmpleado,
                e.EMP_Nombre,
                e.EMP_PrimerApellido,
                e.EMP_SegundoApellido,
                e.EMP_FechaNacimiento,
                e.EMP_FechaIngreso,
                e.EMP_NSS,
                e.EMP_RFC,
                e.EMP_CURP,
                e.EMP_Calle,
                e.EMP_NoExterior,
                e.EMP_NoInterior,
                e.EMP_CodigoPostal,
                e.EMP_TelefonoCasa,
                e.EMP_TelefonoOficina,
                e.EMP_ExtensionOficina,
                e.EMP_TelefonoCelular,
                e.EMP_CorreoElectronico,
                e.EMP_ContactoEmergencias,
                e.EMP_TelefonoContacto,
                e.EMP_RelacionContacto,
                e.EMP_Fotografia,
                e.EMP_Comentarios,
                sexo.CMM_Valor AS Sexo,
                ec.CMM_Valor AS EstadoCivil,
                ts.CMM_Valor AS TipoSanguineo,
                te.CMM_Valor AS TipoEmpleado,
                p.PUE_NombrePuesto,
                d.DEP_Nombre AS Departamento,
                cedi.CMM_Valor AS Cedi,
                turno.TUR_Nombre AS Turno,
                pai.PAI_Nombre AS Pais,
                est.EST_Nombre AS Estado,
                ciu.CIU_Nombre AS Ciudad,
                col.CIUC_Nombre AS Colonia
            FROM Empleados e
            LEFT JOIN ControlesMaestrosMultiples sexo ON sexo.CMM_ControlId = e.EMP_CMM_SexoId
            LEFT JOIN ControlesMaestrosMultiples ec ON ec.CMM_ControlId = e.EMP_CMM_EstadoCivilId
            LEFT JOIN ControlesMaestrosMultiples ts ON ts.CMM_ControlId = e.EMP_CMM_TipoSanguineoId
            LEFT JOIN ControlesMaestrosMultiples te ON te.CMM_ControlId = e.EMP_CMM_TipoEmpleadoId
            LEFT JOIN Puestos p ON p.PUE_PuestoId = e.EMP_PUE_PuestoId
            LEFT JOIN Departamentos d ON d.DEP_DeptoId = e.EMP_DEP_DeptoId
            LEFT JOIN ControlesMaestrosMultiples cedi ON cedi.CMM_ControlId = e.EMP_DEP_SucursalId
            LEFT JOIN turnos turno ON turno.TUR_TurnoId = e.EMP_TUR_TurnoId
            LEFT JOIN Paises pai ON pai.PAI_PaisId = e.EMP_PAI_PaisId
            LEFT JOIN Estados est ON est.EST_EstadoId = e.EMP_EST_EstadoId
            LEFT JOIN Ciudades ciu ON ciu.CIU_CiudadId = e.EMP_CIU_CiudadId
            LEFT JOIN CiudadesColonias col ON col.CIUC_ColoniaId = e.EMP_Colonia
            WHERE e.EMP_EmpleadoId = ?
        ", [$empleadoId]);

        if (!$row) {
            throw new \Exception('Empleado no encontrado.');
        }

        $nombreCompleto = trim(
            $row->EMP_Nombre . ' ' . $row->EMP_PrimerApellido . ' ' . ($row->EMP_SegundoApellido ?: '')
        );

        return [
            'nombreCompleto' => $nombreCompleto,
            'codigoEmpleado' => $row->EMP_CodigoEmpleado,
            'nombre' => $row->EMP_Nombre,
            'primerApellido' => $row->EMP_PrimerApellido,
            'segundoApellido' => $row->EMP_SegundoApellido,
            'sexo' => $row->Sexo,
            'fechaNacimiento' => $this->formatearFecha($row->EMP_FechaNacimiento),
            'nss' => $row->EMP_NSS,
            'tipoSanguineo' => $row->TipoSanguineo,
            'estadoCivil' => $row->EstadoCivil,
            'rfc' => $row->EMP_RFC,
            'curp' => $row->EMP_CURP,
            'calle' => $row->EMP_Calle,
            'noExterior' => $row->EMP_NoExterior,
            'noInterior' => $row->EMP_NoInterior,
            'codigoPostal' => $row->EMP_CodigoPostal,
            'colonia' => $row->Colonia,
            'ciudad' => $row->Ciudad,
            'estado' => $row->Estado,
            'pais' => $row->Pais,
            'telefonoCasa' => $row->EMP_TelefonoCasa,
            'telefonoOficina' => $row->EMP_TelefonoOficina,
            'extensionOficina' => $row->EMP_ExtensionOficina,
            'telefonoCelular' => $row->EMP_TelefonoCelular,
            'correoElectronico' => $row->EMP_CorreoElectronico,
            'contactoEmergencia' => $row->EMP_ContactoEmergencias,
            'telefonoContacto' => $row->EMP_TelefonoContacto,
            'relacionContacto' => $row->EMP_RelacionContacto,
            'fechaIngreso' => $this->formatearFecha($row->EMP_FechaIngreso),
            'puesto' => $row->PUE_NombrePuesto,
            'departamento' => $row->Departamento,
            'cedi' => $row->Cedi,
            'tipoEmpleado' => $row->TipoEmpleado,
            'turno' => $row->Turno,
            'comentarios' => $row->EMP_Comentarios,
        ];
    }

    private function normalizarCambios(array $cambios)
    {
        $resultado = [];

        foreach ($cambios as $cambio) {
            if (!is_array($cambio)) {
                continue;
            }

            $campo = trim((string) (isset($cambio['campo']) ? $cambio['campo'] : ''));
            $label = trim((string) (isset($cambio['label']) ? $cambio['label'] : $campo));
            $anterior = trim((string) (isset($cambio['anterior']) ? $cambio['anterior'] : ''));
            $nuevo = trim((string) (isset($cambio['nuevo']) ? $cambio['nuevo'] : ''));

            if ($campo === '' || $nuevo === $anterior) {
                continue;
            }

            $resultado[] = [
                'campo' => $campo,
                'label' => $label,
                'anterior' => $anterior,
                'nuevo' => $nuevo,
            ];
        }

        return $resultado;
    }

    private function construirDescripcionCambios(array $cambios)
    {
        $lineas = ['Solicitud de cambio de perfil (' . count($cambios) . ' campo(s))'];

        foreach ($cambios as $cambio) {
            $anterior = $cambio['anterior'] !== '' ? $cambio['anterior'] : '(vacío)';
            $lineas[] = '• ' . $cambio['label'] . ': ' . $anterior . ' → ' . $cambio['nuevo'];
        }

        $resumen = implode("\n", $lineas);
        $json = json_encode(['tipo' => 'CAMBIO_PERFIL', 'cambios' => $cambios], JSON_UNESCAPED_UNICODE);

        return self::MARCA_CAMBIO_PERFIL . $resumen . self::JSON_DELIMITER . $json;
    }

    private function obtenerCheIdSolicitoCambio()
    {
        $row = DB::selectOne("
            SELECT TOP 1 CHE_Id
            FROM RPT_Checador_ConfigEstatus
            WHERE CHE_Tipo = 'MODIFICACION'
                AND CHE_Activo = 1
                AND UPPER(CHE_Estatus) = 'SOLICITO CAMBIO'
        ");

        return $row ? (int) $row->CHE_Id : null;
    }

    private function getEmpleadoId()
    {
        $userdata = auth()->user();

        return $userdata ? $userdata['USU_EMP_EmpleadoId'] : null;
    }

    private function getCodigoEmpleado()
    {
        $userdata = auth()->user();

        return $userdata ? $userdata['USU_Nombre'] : null;
    }

    private function formatearFecha($fecha)
    {
        if (empty($fecha)) {
            return '';
        }

        $timestamp = strtotime($fecha);

        return $timestamp ? date('d/m/Y', $timestamp) : $fecha;
    }

    private function notificarRhSolicitudCambio($fila, $codigoEmpleado, $totalCambios)
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

            $empleadoRemitente = DB::selectOne("
                SELECT EMP_Nombre, EMP_PrimerApellido, EMP_Fotografia
                FROM Empleados
                WHERE RIGHT('000' + CONVERT(varchar, EMP_CodigoEmpleado), 4) = RIGHT('000' + CONVERT(varchar, ?), 4)
            ", [$codigoEmpleado]);

            if (!$empleadoRemitente || count($rh_empleados) === 0) {
                return;
            }

            $tituloMensaje = 'Solicitud cambio de perfil Folio #' . $fila->CHI_Id . '. ('
                . $empleadoRemitente->EMP_Nombre . ' ' . $empleadoRemitente->EMP_PrimerApellido . ')';
            $cuerpoMensaje = 'El empleado solicitó modificar ' . $totalCambios . ' dato(s). ¿Desea revisar la solicitud?';
            $fotoMensaje = empty($empleadoRemitente->EMP_Fotografia) ? 'SIN FOTO.png' : $empleadoRemitente->EMP_Fotografia;
            $accionMensaje = url('#capital-humano/permisos');

            foreach ($rh_empleados as $rh) {
                $this->enviarAlertaRh(
                    $rh->USU_Nombre,
                    $tituloMensaje,
                    $cuerpoMensaje,
                    $fotoMensaje,
                    $accionMensaje
                );
            }
        } catch (\Exception $e) {
            // La notificación no debe impedir guardar la solicitud.
        }
    }

    private function enviarAlertaRh($codigoEmpDestinatario, $tituloMensaje, $cuerpoMensaje, $fotoMensaje, $accionMensaje)
    {
        $user = UsuariosRPT::where('nomina', $codigoEmpDestinatario)->first();
        if (!$user) {
            return;
        }

        $details = [
            'title' => $tituloMensaje,
            'body' => $cuerpoMensaje,
            'type' => 'alert',
            'foto' => $fotoMensaje,
            'action' => $accionMensaje,
            'aplicativo' => "'mi-nomina', 'web'",
        ];

        $user->storeNewNotification($details);
        event(new UserNotifyEvent($user->id, $details));
        $user->fcmNotification($details);
    }
}
