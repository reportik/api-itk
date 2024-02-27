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
use App\Models\RPT\RPT_CHECADOR_ESTATUS as CHE;
use App\Models\RPT\RPT_CHECADOR_REPORTE as CHR;
use App\Http\Controllers\Sistema\DataBaseSession;
use App\Models\RPT\RPT_CHECADOR_INCIDENCIA AS CHI;

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

    public function index()
    {
        $userdata = auth()->user();
        // get all
        $permisos = DB::select("SELECT CHI_Id AS Id
            , CONVERT(varchar, CHI_FechaCreacion,120) CHI_FechaCreacion
            , CASE WHEN CHI.CHI_EstatusPermiso = 'A' THEN 'APROBADO'
            WHEN CHI.CHI_EstatusPermiso = 'S' THEN 'ENVIADO'
            WHEN CHI.CHI_EstatusPermiso = 'E' THEN 'EN REVISION'
            WHEN CHI.CHI_EstatusPermiso = 'R' THEN 'RECHAZADO'
            WHEN CHI.CHI_EstatusPermiso = 'D' THEN 'ARCHIVADO'
            WHEN CHI.CHI_EstatusPermiso = 'N' THEN 'NO APLICA' END
                AS Estatus
            , CHI_EstatusPermiso EstatusId
            , CHI_Descripcion AS Descripcion
            , CONVERT(varchar, CHI_FechaInicio,120) CHI_FechaInicio
            , CONVERT(varchar, CHI_FechaTermino,120) CHI_FechaTermino
            , CHI_CHE_Id PermisoId
            , CHE_Estatus PermisoDescripcion 
            , CHI_Eliminado
            FROM  RPT_Checador_Incidencias CHI 
            LEFT JOIN Empleados on 
                    RIGHT('000' + CONVERT(varchar, EMP_CodigoEmpleado), 4) = RIGHT('000' + CONVERT(varchar, CHI_EMP_Empleado), 4)
            INNER JOIN RPT_Checador_ConfigEstatus CHE on CHE.CHE_Id = CHI_CHE_Id
            where CHI.CHI_EstatusPermiso <> 'D' AND CHI.CHI_Eliminado = 0 and EMP_CodigoEmpleado = ?
            AND EMP_CMM_TipoEmpleadoId <> 'E646B375-C7AD-494E-8F6B-8BDF540DBEEB'
            order by CHI_FechaCreacion desc", [$userdata['USU_Nombre']]);
        return response()->json([
            "permisosUsuario" => $permisos
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
     
        try {

            $incidencia = $_POST['incidenciaId'];
            $fi = ($_POST['fechaHoraInicio']);
            $ft = ($_POST['fechaHoraTermino']);
            $descripcion = $_POST['descripcion'];

            $userdata = auth()->user();

            $fila = new CHI; //CHI_Id

            $fila->CHI_EMP_Empleado = $userdata['USU_Nombre'];
            $fila->CHI_CHE_Id = $incidencia;
            $fila->CHI_FechaInicio = $fi;
            $fila->CHI_FechaTermino = $ft;
            $fila->CHI_Descripcion = strtoupper($descripcion);
            $fila->CHI_EstatusPermiso = 'S'; //[APROBADO A, EN REVISION E, RECHAZADO R, NO APLICA N, ENVIADO S, ARCHIVADO D]
            $fila->CHI_CreadoPor = $userdata['USU_Nombre'];
            $fila->CHI_FechaCreacion = date('d-m-Y H:i:s');
            $fila->CHI_Eliminado = 0;
            $fila->save();

            return response()->json([
                "status" => true,
                "message" => "Permiso Guardado"
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Ocurrió un error al realizar el proceso. Error: ' . $e->getMessage() . ". Linea: " . $e->getLine()], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $userdata = auth()->user();
        // get all
        $permiso = DB::select("SELECT CHI_Id AS Id
            , CONVERT(varchar, CHI_FechaCreacion,120) CHI_FechaCreacion
            , CASE WHEN CHI.CHI_EstatusPermiso = 'A' THEN 'APROBADO'
            WHEN CHI.CHI_EstatusPermiso = 'S' THEN 'ENVIADO'
            WHEN CHI.CHI_EstatusPermiso = 'E' THEN 'EN REVISION'
            WHEN CHI.CHI_EstatusPermiso = 'R' THEN 'RECHAZADO'
            WHEN CHI.CHI_EstatusPermiso = 'D' THEN 'ARCHIVADO'
            WHEN CHI.CHI_EstatusPermiso = 'N' THEN 'NO APLICA' END
                AS Estatus
            , CHI_EstatusPermiso EstatusId
            , CHI_Descripcion AS Descripcion
            , CONVERT(varchar, CHI_FechaInicio,120) CHI_FechaInicio
            , CONVERT(varchar, CHI_FechaTermino,120) CHI_FechaTermino
            , CHI_CHE_Id PermisoId
            , CHE_Estatus PermisoDescripcion 
            , CHI_Eliminado
            FROM  RPT_Checador_Incidencias CHI 
            LEFT JOIN Empleados on 
                    RIGHT('000' + CONVERT(varchar, EMP_CodigoEmpleado), 4) = RIGHT('000' + CONVERT(varchar, CHI_EMP_Empleado), 4)
            INNER JOIN RPT_Checador_ConfigEstatus CHE on CHE.CHE_Id = CHI_CHE_Id
            where CHI.CHI_EstatusPermiso <> 'D' AND CHI.CHI_Eliminado = 0 and EMP_CodigoEmpleado = ?
            AND CHI_Id = ?
            AND EMP_CMM_TipoEmpleadoId <> 'E646B375-C7AD-494E-8F6B-8BDF540DBEEB'
            order by CHI_FechaCreacion desc", [$userdata['USU_Nombre'], $id]);
        return response()->json([
            "permiso" => $permiso
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update()
    {
        try {

            $id = $_POST['id'];
            $incidencia = $_POST['incidenciaId'];
            $fi = ($_POST['fechaHoraInicio']);
            $ft = ($_POST['fechaHoraTermino']);
            $descripcion = $_POST['descripcion'];

            //$userdata = auth()->user();

            $fila = CHI::find($id); //CHI_Id

            if ($fila->CHI_EstatusPermiso == 'S') {
                $fila->CHI_CHE_Id = $incidencia;
                $fila->CHI_FechaInicio = $fi;
                $fila->CHI_FechaTermino = $ft;
                $fila->CHI_Descripcion = strtoupper($descripcion);
                $fila->CHI_FechaCreacion = date('d-m-Y H:i:s');
                $fila->save();
                return response()->json([
                    "status" => true,
                    "message" => "Permiso Guardado"
                ]);
            } else {
                return response()->json(['error' => "Este Permiso ya no se puede modificar."], 400);
            }

            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Ocurrió un error al realizar el proceso. Error: ' . $e->getMessage() . ". Linea: " . $e->getLine()], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy()
    {
        $id = $_POST['id'];
        $fila = CHI::find($id); //CHI_Id

        if ($fila->CHI_EstatusPermiso == 'A' || $fila->CHI_EstatusPermiso == 'R') {
            //$fila->CHI_EstatusPermiso = 'D';
            $fila->CHI_Eliminado = 1;
            $fila->save();
            return response()->json([
                "status" => true,
                "message" => "exito"
            ]);
        } else {
            return response()->json(['error' => "Este Permiso no se puede archivar, por que no esta RECHAZADO o APROBADO."], 400);
        }
    }
   
   
    public function send()
    {
        //dd(\Carbon\Carbon::now());
        $user = UsuariosRPT::where('nomina', '913')->first();
        $userdata = auth()->user();
        $empleadoId = $userdata['USU_EMP_EmpleadoId'];
        //$empleadoId = DataBaseSession::getEmpleadoId();
        $empleado = Empleados::find($empleadoId);
        //dd($empleado, $empleadoId);
        $details = [

            'title' => 'Nuevo Permiso. ('. $empleado->EMP_Nombre.' '. $empleado->EMP_PrimerApellido.')',
            'body' => '¿Desea revisar el permiso ahora?',
            'type' => 'alert',
            'foto' => (is_null($empleado->EMP_Fotografia)) ? 'SIN FOTO.png' : $empleado->EMP_Fotografia,
            'action' => url('#capital-humano/permisos/'. $empleado->EMP_EmpleadoId)
        ];

        //Notification::send($user, new RPT_Notification($details));
        $user->storeNewNotification($details);
        $userId = $user->id;
        //falta pasarle el details para agregar la notifiacion en la vista
        event(new UserNotifyEvent($userId, $details));
        return response()->json([
            "status" => true,
            "message" => "Notificado"
        ]);
    }

    

}
