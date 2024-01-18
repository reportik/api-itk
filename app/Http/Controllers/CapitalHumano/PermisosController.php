<?php

namespace App\Http\Controllers\CapitalHumano;

use DB;
use App\Models\User;
use App\Models\RPT\RPT_CHECADOR_INCIDENCIA AS CHI;
use App\Models\RPT\RPT_CHECADOR_REPORTE as CHR;
use App\Models\RPT\RPT_CHECADOR_ESTATUS as CHE;

use App\Models\Empleados;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;

use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

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
            WHEN CHI.CHI_EstatusPermiso = 'E' THEN 'EN REVISION'
            WHEN CHI.CHI_EstatusPermiso = 'R' THEN 'RECHAZADO'
            WHEN CHI.CHI_EstatusPermiso = 'N' THEN 'NO APLICA' END
                AS Estatus
            , CHI_Descripcion AS Descripcion
            , CONVERT(varchar, CHI_FechaInicio,120) CHI_FechaInicio
            , CONVERT(varchar, CHI_FechaTermino,120) CHI_FechaTermino
            , CHI_Eliminado
            FROM  RPT_Checador_Incidencias CHI 
            LEFT JOIN Empleados on 
                    RIGHT('000' + CONVERT(varchar, EMP_CodigoEmpleado), 4) = RIGHT('000' + CONVERT(varchar, CHI_EMP_Empleado), 4)
            where CHI.CHI_Eliminado = 0 and EMP_CodigoEmpleado = ?
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
    public function store(Request $request)
    {
     
        try {
            // data validation
            // $request->validate([
            //     "name" => "required",
            //     "email" => "required|email|unique:users",
            //     "password" => "required|confirmed"
            // ]);

            // 
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
            $fila->CHI_EstatusPermiso = 'E'; //[APROBADO, EN REVISION, RECHAZADO, NO APLICA]
            $fila->CHI_CreadoPor = $userdata['USU_Nombre'];
            $fila->CHI_FechaCreacion = date('d-m-Y H:i:s');
            $fila->CHI_Eliminado = 0;
            $fila->save();

            return response()->json([
                "status" => true,
                "message" => "Registro Guardado"
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

}
