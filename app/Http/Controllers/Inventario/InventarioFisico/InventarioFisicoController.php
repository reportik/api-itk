<?php namespace App\Http\Controllers\Inventario\InventarioFisico;

use App\Http\Controllers\CFDI\EncabezadoPDF;
use App\Http\Controllers\CFDI\FEv32;
use App\Http\Controllers\Inventario\Articulos\ArticulosController;
use App\Http\Controllers\Sistema\DataBaseSession;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as NewRequest;
use App\Mapeos\Controles\ControlesMaestros;
use App\Mapeos\Controles\ControlesMaestrosMultiples;
use App\Models\AdmonSistema\ControlMaestro;
use App\Models\Inventario\InventarioFisico\Almacen;
use App\Models\Inventario\InventarioFisico\Articulo;
use App\Models\Inventario\InventarioFisico\DetallesMovimientoInventario;
use App\Models\Inventario\InventarioFisico\Empleado;
use App\Models\Inventario\InventarioFisico\InventarioFisico;
use App\Models\InventarioFisicoDetalle;
use App\Models\Inventario\InventarioFisico\LocalidadesArticulo;
use App\Models\Inventario\InventarioFisico\ProcesadorMovimientoInventarios;
use App\Models\Inventario\InventarioFisico\TraspasoMovto;
use App\Models\inventario\Localidad;
use App\Models\Lotes;
use App\Models\LotesLocalidades;
use Response;

class InventarioFisicoController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $encabezados =array(
            'Id',
            'Nombre'
        );

        $contenidos=array(
            'IF_InventarioFisicoId',
            'IF_FechaInventario'
        );

        $results=\DB::table('InventarioFisico')
            ->select($contenidos)
            ->whereRaw('IF_Bloqueado =0'.(DataBaseSession::isPermisoCorporativo() ? "" : " AND IF_InventarioFisicoId IN (
            SELECT IFD_IF_InventarioFisicoId FROM InventarioFisicoDetalle
            WHERE IFD_ALM_AlmacenId IN (".DataBaseSession::getAlmacenesPorCediId().")
            GROUP BY IFD_IF_InventarioFisicoId)"))
            ->get();

        $almacenes=array(''=>'') + Almacen::whereRaw("ALM_Eliminado = 0".(DataBaseSession::isPermisoCorporativo() ? "" : " AND ALM_AlmacenId IN (".DataBaseSession::getAlmacenesPorCediId().")"))
                ->orderby('ALM_Nombre','ASC')->lists('ALM_Nombre', 'ALM_AlmacenId')->all();

        return view('Inventario.InventarioFisico.create', compact('results', 'encabezados', 'contenidos', 'almacenes'));
    }

    public function consultarresponsable($id){
        $sub=Empleado::select('EMP_EmpleadoId','EMP_Nombre','EMP_PrimerApellido','EMP_SegundoApellido')
            ->join('Almacenes','EMP_EmpleadoId','=','ALM_EMP_ResponsableId')
            ->where('ALM_AlmacenId','=',$id)
            ->get();
        return Response::json($sub);
    }

    public function consultarinventarioarticulos($id){
        $sub = \DB::select(
            \DB::raw(
                "SELECT LOCA_ART_ArticuloId, ART_CodigoArticulo, ART_Nombre, ART_CantidadAMano, ART_SeguimientoLocMult, ART_SeguimientoLotMult, CMUM_Nombre,
                ALM_AlmacenId, ALM_Nombre, LOCA_LOC_LocalidadId, LOC_Nombre, LOCA_Cantidad, LOTL_LoteLocalidadId, LOT_LoteId, LOT_CodigoLote,
                LOTL_Cantidad, EMP_EmpleadoId
                FROM Articulos
                INNER JOIN ControlesMaestrosUM ON CMUM_UnidadMedidaId=ART_CMUM_UMInventarioId
                LEFT JOIN LocalidadesArticulo ON LOCA_ART_ArticuloId=ART_ArticuloId
                LEFT JOIN Localidades ON LOCA_LOC_LocalidadId=LOC_LocalidadId
                LEFT JOIN (LotesLocalidades
			              INNER JOIN Lotes ON LOTL_LOT_LoteId = LOT_LoteId)
			              ON LOT_ART_ArticuloId = ART_ArticuloId AND LOTL_LOC_LocalidadId = LOC_LocalidadId
                LEFT JOIN Almacenes ON LOC_ALM_AlmacenId=ALM_AlmacenId
                LEFT JOIN Empleados ON EMP_EmpleadoId=ALM_EMP_ResponsableId
                WHERE ALM_AlmacenId='".$id."' AND ART_Eliminado = 0 AND ART_Activo = 1 AND ISNULL(LOTL_Cantidad, 0) > 0
                AND ISNULL(LOCA_Cantidad, 0) > 0
                ORDER BY ART_CodigoArticulo ASC"
            )
        );

        /*$sub=LocalidadesArticulo::select('LOCA_ART_ArticuloId','ART_CodigoArticulo','ART_Nombre','ART_CantidadAMano','CMUM_Nombre','ALM_AlmacenId','ALM_Nombre','LOCA_LOC_LocalidadId','LOC_Nombre','LOCA_Cantidad','EMP_EmpleadoId')
            ->join('Localidades','LOCA_LOC_LocalidadId','=','LOC_LocalidadId')
            ->join('Almacenes','LOC_ALM_AlmacenId','=','ALM_AlmacenId')
            ->join('Articulos','ART_ArticuloId','=','LOCA_ART_ArticuloId')
            ->join('ControlesMaestrosUM','CMUM_UnidadMedidaId','=','ART_CMUM_UMInventarioId')
            ->join('Empleados','EMP_EmpleadoId','=','ALM_EMP_ResponsableId')
            ->where('ALM_AlmacenId','=',$id)
            ->where('LOC_LocalidadGeneral','=',0)
            ->orderBy('ART_CodigoArticulo','ASC')
            ->get();*/
        return Response::json($sub);
    }

    public static function ConsultarInventarioArticulosLocalidadesIF($idLocalidad){
        $sub = \DB::select(
            \DB::raw(
                "SELECT LOCA_ART_ArticuloId, ART_CodigoArticulo, ART_Nombre, ART_CantidadAMano, ART_SeguimientoLocMult, ART_SeguimientoLotMult, CMUM_Nombre,
                    ALM_AlmacenId, ALM_Nombre, LOCA_LOC_LocalidadId, LOC_Nombre, LOCA_Cantidad, LOTL_LoteLocalidadId, LOT_LoteId, LOT_CodigoLote,
                    LOTL_Cantidad, EMP_EmpleadoId
                FROM Articulos
                INNER JOIN ControlesMaestrosUM ON CMUM_UnidadMedidaId=ART_CMUM_UMInventarioId
                LEFT JOIN LocalidadesArticulo ON LOCA_ART_ArticuloId=ART_ArticuloId
                LEFT JOIN Localidades ON LOCA_LOC_LocalidadId=LOC_LocalidadId
                LEFT JOIN (LotesLocalidades
			              INNER JOIN Lotes ON LOTL_LOT_LoteId = LOT_LoteId)
			              ON LOT_ART_ArticuloId = ART_ArticuloId AND LOTL_LOC_LocalidadId = LOC_LocalidadId
                LEFT JOIN Almacenes ON LOC_ALM_AlmacenId=ALM_AlmacenId
                LEFT JOIN Empleados ON EMP_EmpleadoId=ALM_EMP_ResponsableId
                WHERE LOC_LocalidadId='".$idLocalidad."' AND LOC_LocalidadGeneral = 0 AND ART_Eliminado = 0 AND ART_Activo = 1 AND ISNULL(LOTL_Cantidad, 0) > 0
                AND ISNULL(LOCA_Cantidad, 0) > 0
                ORDER BY ART_CodigoArticulo ASC"
            )
        );
        /*$sub=LocalidadesArticulo::select('LOCA_ART_ArticuloId','ART_CodigoArticulo','ART_Nombre','ART_CantidadAMano','CMUM_Nombre','ALM_AlmacenId','ALM_Nombre','LOCA_LOC_LocalidadId','LOC_Nombre','LOCA_Cantidad','EMP_EmpleadoId')
            ->join('Localidades','LOCA_LOC_LocalidadId','=','LOC_LocalidadId')
            ->join('Almacenes','LOC_ALM_AlmacenId','=','ALM_AlmacenId')
            ->join('Articulos','ART_ArticuloId','=','LOCA_ART_ArticuloId')
            ->join('ControlesMaestrosUM','CMUM_UnidadMedidaId','=','ART_CMUM_UMInventarioId')
            ->join('Empleados','EMP_EmpleadoId','=','ALM_EMP_ResponsableId')
            ->where('LOC_LocalidadId','=',$idLocalidad)
            ->where('LOC_LocalidadGeneral','=',0)
            ->orderBy('ART_CodigoArticulo','ASC')
            ->get();*/

        return Response::json($sub);
    }

    public function consultarLocalidadesBloqueadas(){

        $arreglo = NewRequest::input('localidades');
        $fecha = NewRequest::input('fechaselec');
        $cuentaArreglo = count($arreglo);
        $localidadId = "";
        for($x = 0; $x < $cuentaArreglo; $x ++)
        {

            if($localidadId == "")
            {

                $localidadId = "'".$arreglo[$x][2]."'";

            }
            else
            {

                $localidadId = $localidadId.",'".$arreglo[$x][2]."'";

            }

        }

        $sub = \DB::select(
            \DB::raw(
                "SELECT
                    IFD_LOC_LocalidadId, IFD_Bloqueado
                FROM InventarioFisico
                INNER JOIN InventarioFisicoDetalle ON IFD_IF_InventarioFisicoId = IF_InventarioFisicoId
                WHERE IF_FechaInventario = '".$fecha."'
                AND IFD_LOC_LocalidadId IN (".$localidadId.")
                GROUP BY
                    IFD_LOC_LocalidadId, IFD_Bloqueado"
            )
        );

        $cuentasub = count($sub);
        $cadena = "";
        for($x = 0; $x < $cuentasub; $x ++)
        {

            for($i = 0; $i < $cuentaArreglo; $i ++)
            {

                if($sub[$x]->IFD_LOC_LocalidadId == $arreglo[$i][2] && $sub[$x]->IFD_Bloqueado == 1)
                {

                    if($cadena == "")
                    {

                        $cadena = $arreglo[$i][1];

                    }
                    else
                    {

                        $cadena = $cadena.", ".$arreglo[$i][1];

                    }

                }

            }

        }

        return Response::json($cadena);

    }

    public function ConsultarInventarioArticulosLocalidadesIF2($localidadesId, $fecha){

        $sub = \DB::select(
            \DB::raw(
                "SELECT *
                FROM
                (

                SELECT LOCA_ART_ArticuloId, ART_CodigoArticulo, ART_Nombre, ART_CantidadAMano, ART_SeguimientoLocMult, ART_SeguimientoLotMult, CMUM_Nombre,
                    ALM_AlmacenId, ALM_Nombre, LOCA_LOC_LocalidadId, LOC_Nombre, ISNULL(LOCA_Cantidad, 0) AS LOCA_Cantidad, LOTL_LoteLocalidadId, LOT_LoteId, LOT_CodigoLote,
                ISNULL(LOTL_Cantidad,0)AS LOTL_Cantidad, EMP_EmpleadoId
                    FROM Articulos
                    INNER JOIN ControlesMaestrosUM ON CMUM_UnidadMedidaId=ART_CMUM_UMInventarioId
                    LEFT JOIN LocalidadesArticulo ON LOCA_ART_ArticuloId=ART_ArticuloId
                    LEFT JOIN Localidades ON LOCA_LOC_LocalidadId=LOC_LocalidadId
                    LEFT JOIN (LotesLocalidades
                              INNER JOIN Lotes ON LOTL_LOT_LoteId = LOT_LoteId)
                              ON LOT_ART_ArticuloId = ART_ArticuloId AND LOTL_LOC_LocalidadId = LOC_LocalidadId
                    LEFT JOIN Almacenes ON LOC_ALM_AlmacenId=ALM_AlmacenId
                    LEFT JOIN Empleados ON EMP_EmpleadoId=ALM_EMP_ResponsableId
                    WHERE LOC_LocalidadId IN (".$localidadesId.")
                    AND LOC_LocalidadGeneral=0 AND ART_Eliminado = 0 AND ART_Activo = 1 AND ISNULL(LOTL_Cantidad, 0) > 0


                UNION ALL

                SELECT
                        ART_ArticuloId as LOCA_ART_ArticuloId, ART_CodigoArticulo, ART_Nombre, ART_CantidadAMano, ART_SeguimientoLocMult, ART_SeguimientoLotMult, CMUM_Nombre,
                        ALM_AlmacenId, ALM_Nombre, LOC_LocalidadId AS LOCA_LOC_LocalidadId, LOC_Nombre, ISNULL(LOCA_Cantidad, 0) AS LOCA_Cantidad, LOTL_LoteLocalidadId, LOT_LoteId, LOT_CodigoLote,
                ISNULL(LOTL_Cantidad,0)AS LOTL_Cantidad, EMP_EmpleadoId
                    FROM InventarioFisico
                    INNER JOIN InventarioFisicoDetalle ON IFD_IF_InventarioFisicoId=IF_InventarioFisicoId
                    INNER JOIN Articulos ON ART_ArticuloId=IFD_ART_ArticuloId
                    INNER JOIN ControlesMaestrosUM ON CMUM_UnidadMedidaId=ART_CMUM_UMInventarioId
                    LEFT JOIN Localidades ON IFD_LOC_LocalidadId=LOC_LocalidadId
                    LEFT JOIN LocalidadesArticulo ON LOCA_ART_ArticuloId=ART_ArticuloId AND LOCA_LOC_LocalidadId=LOC_LocalidadId
                    LEFT JOIN Lotes ON LOT_LoteId = IFD_LOT_LoteId
                    LEFT JOIN LotesLocalidades ON LOTL_LOT_LoteId = LOT_LoteId AND LOTL_LOC_LocalidadId = LOC_LocalidadId
                    LEFT JOIN Almacenes ON LOC_ALM_AlmacenId=ALM_AlmacenId
                    LEFT JOIN Empleados ON EMP_EmpleadoId=ALM_EMP_ResponsableId
                    WHERE LOC_LocalidadId IN (".$localidadesId.")
                    AND IF_FechaInventario='".$fecha."'
                   -- AND ISNULL(LOTL_Cantidad, 0) > 0
                --AND ISNULL(LOCA_Cantidad, 0) > 0

                ) AS CONSULTA

                GROUP BY  LOCA_ART_ArticuloId, ART_CodigoArticulo, ART_Nombre, ART_CantidadAMano, ART_SeguimientoLocMult, ART_SeguimientoLotMult
                ,CMUM_Nombre, ALM_AlmacenId, ALM_Nombre, LOCA_LOC_LocalidadId, LOC_Nombre, LOCA_Cantidad, LOTL_LoteLocalidadId, LOT_LoteId,
                LOT_CodigoLote,LOTL_Cantidad, EMP_EmpleadoId

                ORDER BY
                    ALM_Nombre,
                    LOC_Nombre,
                    ART_CodigoArticulo"
            )
        );

        return Response::json($sub);

    }

    public function consultarcantidadlocalidadesarticulos($idlocalidad,$idart){
        $sub=LocalidadesArticulo::select('LOCA_Cantidad')
            ->where('LOCA_LOC_LocalidadId','=',$idlocalidad)
            ->where('LOCA_ART_ArticuloId','=',$idart)
            ->get();
        return Response::json($sub);
    }

    public function buscaralmacenes(){
        $sub=Almacen::select('ALM_Nombre', 'ALM_AlmacenId')
            ->whereRaw(" ALM_Eliminado = 0".(DataBaseSession::isPermisoCorporativo() ? "" : " AND ALM_AlmacenId IN (".DataBaseSession::getAlmacenesPorCediId().")"))
            ->orderby('ALM_Nombre','=','ASC')
            ->get();
        return Response::json($sub);
    }

    public static function BuscarLocalidades($almacenId){
        $sub = Localidad::select( 'LOC_LocalidadId','LOC_CodigoLocalidad','LOC_Nombre','LOC_ALM_AlmacenId','LOC_CMM_CtaPredInvId','LOC_Planear','LOC_LocalidadGeneral','LOC_Eliminado','LOC_FechaUltimaModificacion','LOC_EMP_ModificadoPorId','LOC_DefinidoPorUsuario1','LOC_DefinidoPorUsuario2','LOC_DefinidoPorUsuario3','LOC_DefinidoPorUsuario4','LOC_DefinidoPorUsuario5','LOC_General')->where('LOC_ALM_AlmacenId','=',$almacenId)
            ->where('LOC_LocalidadGeneral','=','0')
            ->orderby('LOC_Nombre','ASC')
            ->get()->all();

          //  dd($sub);


        return Response::json($sub);
    }

    public function BuscarLocalidadesPorAlmacenes($almacenesId){

        $sub = \DB::select(
            \DB::raw(
                "SELECT *
                FROM Localidades
                WHERE LOC_ALM_AlmacenId IN (".$almacenesId.")
                ORDER BY LOC_ALM_AlmacenId, LOC_Nombre ASC"
            )
        );

        return Response::json($sub);

    }

    public function consultarcantidaddecimales(){
        $sub=ControlMaestro::select('CMA_Valor')
            ->where('CMA_Control','=',ControlesMaestros::$CMA_INV_DecimalesCantidades)
            ->get();
        return Response::json($sub);
    }

    public function buscarfecha($fecha){
        /* $sub = \DB::select(
            \DB::raw(
                "SELECT IFD_ART_ArticuloId as LOCA_ART_ArticuloId, ART_CodigoArticulo, ART_Nombre, ART_CantidadAMano, ART_SeguimientoLocMult, ART_SeguimientoLotMult, CMUM_Nombre,
                ALM_AlmacenId, ALM_Nombre, LOCA_LOC_LocalidadId, LOC_Nombre, ISNULL(LOCA_Cantidad, 0.0) AS LOCA_Cantidad , LOTL_LoteLocalidadId, LOT_LoteId, LOT_CodigoLote,
                ISNULL (LOTL_Cantidad, 0.0) AS LOTL_Cantidad, IFD_CantidadAnterior, IFD_CantidadContada, EMP_EmpleadoId, IF_InventarioFisicoId, IF_Bloqueado
                FROM InventarioFisico
                INNER JOIN InventarioFisicoDetalle ON IFD_IF_InventarioFisicoId=IF_InventarioFisicoId
                INNER JOIN Articulos ON ART_ArticuloId=IFD_ART_ArticuloId
                INNER JOIN ControlesMaestrosUM ON CMUM_UnidadMedidaId=ART_CMUM_UMInventarioId
                LEFT JOIN Localidades ON IFD_LOC_LocalidadId=LOC_LocalidadId
                LEFT JOIN LocalidadesArticulo ON LOCA_ART_ArticuloId=ART_ArticuloId AND LOCA_LOC_LocalidadId=LOC_LocalidadId
                LEFT JOIN Lotes ON LOT_LoteId = IFD_LOT_LoteId
                LEFT JOIN LotesLocalidades ON LOTL_LOT_LoteId = LOT_LoteId
                LEFT JOIN Almacenes ON LOC_ALM_AlmacenId=ALM_AlmacenId
                LEFT JOIN Empleados ON EMP_EmpleadoId=ALM_EMP_ResponsableId
                WHERE IF_FechaInventario='".$fecha."'
                ORDER BY ALM_Nombre,
                    LOC_Nombre,
                    ART_CodigoArticulo"
            )
        );
        */

        $sub = \DB::select(
            \DB::raw(
                "SELECT IF_Bloqueado, IF_InventarioFisicoId FROM InventarioFisico
                WHERE IF_FechaInventario='".$fecha."'
                "
            )
        );

        return Response::json($sub);
    }

    public function buscarfecha2($fecha,$localidadesId){
        $fecha = explode('-', $fecha);
        $fecha = $fecha[2].$fecha[1].$fecha[0];
        $sub = \DB::select(
            \DB::raw(
                "SELECT
                    ART_ArticuloId as LOCA_ART_ArticuloId, ART_CodigoArticulo, ART_Nombre, ART_CantidadAMano, ART_SeguimientoLocMult, ART_SeguimientoLotMult, CMUM_Nombre,
                    ALM_AlmacenId, ALM_Nombre, LOC_LocalidadId AS LOCA_LOC_LocalidadId, LOC_Nombre, ISNULL(LOCA_Cantidad, 0) AS LOCA_Cantidad, LOTL_LoteLocalidadId, LOT_LoteId, LOT_CodigoLote,
                    ISNULL(LOTL_Cantidad,0)AS LOTL_Cantidad, ISNULL(LOTL_Cantidad,0) AS IFD_CantidadAnterior, IFD_CantidadContada, EMP_EmpleadoId, IF_InventarioFisicoId, IF_Bloqueado, IFD_Bloqueado
                FROM InventarioFisico
                INNER JOIN InventarioFisicoDetalle ON IFD_IF_InventarioFisicoId=IF_InventarioFisicoId
                INNER JOIN Articulos ON ART_ArticuloId=IFD_ART_ArticuloId
                INNER JOIN ControlesMaestrosUM ON CMUM_UnidadMedidaId=ART_CMUM_UMInventarioId
                LEFT JOIN Localidades ON IFD_LOC_LocalidadId=LOC_LocalidadId
                LEFT JOIN LocalidadesArticulo ON LOCA_ART_ArticuloId=ART_ArticuloId AND LOCA_LOC_LocalidadId=LOC_LocalidadId
                LEFT JOIN Lotes ON LOT_LoteId = IFD_LOT_LoteId
                LEFT JOIN LotesLocalidades ON LOTL_LOT_LoteId = LOT_LoteId AND LOTL_LOC_LocalidadId = LOC_LocalidadId
                LEFT JOIN Almacenes ON LOC_ALM_AlmacenId=ALM_AlmacenId
                LEFT JOIN Empleados ON EMP_EmpleadoId=ALM_EMP_ResponsableId
                WHERE LOC_LocalidadId IN (".$localidadesId.")
                AND CAST(IF_FechaInventario AS DATE) = '".$fecha."'
                ORDER BY
                    ALM_Nombre,
                    LOC_Nombre,
                    ART_CodigoArticulo"
            )
        );

        return Response::json($sub);

    }

    //////////////////////////////////////////////////////////////FUNCIONES PROCESADOR//////////////////////////////////////////////////////
    //funcion general//
    public function inventarioFisicoGeneral($fecha,$localidadesId){
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        if($this->isDatosSinGuardar($localidadesId, $fecha)){

            return ['Status' => 'Error', 'Mensaje' => 'Hay artículos sin guardar, favor de ir a la sección Datos Generales y guardar los datos faltantes.'];

        }
        else {

            \DB::beginTransaction();

            try {

                //busca datos delinventario fisico por fecha
                $datosinventariofisico = InventarioFisicoController::buscaPorFecha($fecha);

                if ($datosinventariofisico->IF_Bloqueado == 0) {
                    //buscar detalles del inventario fisico(id inventario fisico)
                    $hmDetalles = InventarioFisicoController::getDetalles($datosinventariofisico->IF_InventarioFisicoId, $localidadesId);
                    //crear movimiento del inventario fisico(detalles del inventario, id inventario fisico)
                    InventarioFisicoController::CreaMovtoInventarioFisicoDetalle($hmDetalles, $datosinventariofisico->IF_InventarioFisicoId);
                    //mandar mensaje Proceso completado!!
                    //return Response::json(true);
                    $mensaje = 'El Inventario Fisico ha sido Bloqueado con éxito.';
                } else {
                    //enviar mensaje Este invetario ya fue reemplazado
                    //return Response::json(false);
                    $mensaje = 'El Inventario Fisico No ha sido Bloqueado con éxito.';
                }

                \DB::commit();

                return ['Status' => 'Valido', 'Mensaje' => $mensaje];

            } catch (\Exception $e) {
                \DB::rollback();

                return ['Status' => 'Error', 'Mensaje' => 'No se registró el Bloqueado de Inventario Fisico. Ocurrió un error al realizar el proceso. <br><br><br>Error: <br>' . $e->getMessage()];

            }
        }

    }

    //buscar datos del inventario fisico por fecha
    public function buscaPorFecha($fecha){
        /*$fec = explode("-",$fecha);
        $fecha2 = $fec[2]."/".$fec[1]."/".$fec[0];*/
        $nuevaFecha= explode('-', $fecha);
        $nuevaFecha = $nuevaFecha[2].$nuevaFecha[1].$nuevaFecha[0];
        $buscainventariofisicoid=InventarioFisico::whereRaw("CAST(IF_FechaInventario AS DATE) ='".$nuevaFecha."'")->take(1)->get();
        return $buscainventariofisicoid[0];
    }

    //hacer la buqueda del inventario con campos establecidos
    public function getDetalles($idinventariofisico,$localidadesId){

        try{

            $buscainventariofisicodetalle=\DB::select(\DB::raw(
                "SELECT IFD_InventarioFisicoDetId AS COL_DETALLE_ID,
                IFD_IFE_InventarioFisicoEtId AS COL_ETIQUETA_ID,
                IFD_Leyenda AS COL_LEYENDA,
                IFD_EMP_ResponsableId AS COL_RESPONSABLE,
                IFD_ART_ArticuloId AS COL_ARTICULO_ID,
                IFD_CantidadContada AS COL_CANTIDAD_CONTADA,
                IFD_CantidadAnterior AS COL_CANTIDAD_ANTERIOR_SISTEMA,
                IFD_ALM_AlmacenId AS COL_ALMACEN_ID,
                IFD_LOC_LocalidadId AS COL_LOCALIDAD_ID,
                IFD_LOT_LoteId AS COL_LOTE_ID,
                IFD_FechaConteo AS COL_FECHA_CONTEO,
                IFD_CMM_MetodoConteoId AS COL_METODO_CONTEO_ID,
                IFD_EMP_ModificadoPorId AS COL_MODIFICADO_POR,
                IFD_FechaUltimaModificacion AS COL_FECHA_MODIFICACION,
                IFD_OT_OrdenTrabajoId AS COL_ORDEN_TRABAJO_ID,
                IFD_Bloqueado AS COL_BLOQUEADO,
                ART_CodigoArticulo AS CODIGO_ARTICULO,
                ALM_CodigoAlmacen AS CODIGO_ALMACEN,
                ART_SeguimientoLocMult AS COL_LOCALIDAD,
                CMUM_Nombre AS COL_NOMBRE_UM,
                ISNULL( ( CASE
                WHEN ( ART_SeguimientoLocMult = 1 AND ART_SeguimientoLotMult = 0 )
                THEN LOCA_Cantidad
                WHEN ( ART_SeguimientoLocMult = 1 AND ART_SeguimientoLotMult = 1 )
                THEN LOTL_Cantidad
                ELSE LOCA_Cantidad
                END ),0.0) AS COL_CANTIDAD_SISTEMA,
                (SELECT LOC_CodigoLocalidad
                FROM Localidades
                WHERE LOC_LocalidadId=LOCA_LOC_LocalidadId) AS CODIGO_LOCALIDADES,
                LOCA_LocalidadArticuloId,
                LOTL_LoteLocalidadId
                FROM InventarioFisico
                INNER JOIN InventarioFisicoDetalle ON IFD_IF_InventarioFisicoId = IF_InventarioFisicoId
                INNER JOIN Articulos ON ART_ArticuloId = IFD_ART_ArticuloId
                INNER JOIN ControlesMaestrosUM ON ART_CMUM_UMInventarioId=CMUM_UnidadMedidaId
                LEFT JOIN Localidades ON LOC_LocalidadId = IFD_LOC_LocalidadId
                LEFT JOIN Lotes ON LOT_LoteId = IFD_LOT_LoteId
                LEFT JOIN LocalidadesArticulo ON LOCA_ART_ArticuloId = IFD_ART_ArticuloId AND LOCA_LOC_LocalidadId = IFD_LOC_LocalidadId
                LEFT JOIN LotesLocalidades ON LOTL_LOT_LoteId = LOT_LoteId AND LOTL_LOC_LocalidadId = IFD_LOC_LocalidadId
                LEFT JOIN Almacenes ON ALM_AlmacenId = LOC_ALM_AlmacenId
                WHERE IF_InventarioFisicoId = '".$idinventariofisico."'
                AND LOC_LocalidadId IN (".$localidadesId.")
          ORDER BY COL_ARTICULO_ID"
            ));

            return $buscainventariofisicodetalle;

        }
        catch(\Exception $e){

            throw $e;

        }

    }

    //crear el movimiento de inventario fisico detalle (consulta de los detalles, id del inventario)
    public function CreaMovtoInventarioFisicoDetalle($datosconsulta,$idinventariofisico){

        try{

            $cuenta=count($datosconsulta);
            //dd($datosconsulta);

            for($i=0;$i<$cuenta;$i++){
                $idart= $datosconsulta[$i]->COL_ARTICULO_ID;//guarda id del articulo que trae la consulta por renglon
                $idlote= $datosconsulta[$i]->COL_LOTE_ID;//guarda id del lote que trae la consulta por renglon
                // $alDetalles=$datosconsulta;
                //crear el movimiento (id delarticulo, datos de la consulta por renglon)
                $idMovimiento=InventarioFisicoController::creaMovimiento($idart,$datosconsulta[$i]);
                //actualizar el id de transferencia (id del inventariofisico, id articulo, id del movimiento)
                InventarioFisicoController::actualizarIdTransferencia($datosconsulta[$i]->COL_DETALLE_ID,$idart,$idMovimiento,$idlote);
            }
            InventarioFisicoController::desbloqueo($idinventariofisico);

        }
        catch(\Exception $e){

            throw $e;

        }

        //return true;
    }

    //funcion para crear el movimiento (id del articulo, datos de la consulta de detalles por renglon)
    public function creaMovimiento($idart,$alDetalles){

        try{

            //buscar articulo por id(id del articulo)
            $articulo=ArticulosController::buscaPorId($idart);
            //guardar la cantidad de referencia (datos de la consulta de detalles)
            $cantidadTransferencia=InventarioFisicoController::getCantidadReferencia($alDetalles);
            //obtener el movimiento  de la transferencia(id del articulo, cantidad de transferencia, nombre de la unidad de medida)
            $movimiento=InventarioFisicoController::obtenerTransferenciaMvoto($idart, $cantidadTransferencia,$alDetalles->COL_NOMBRE_UM);
            //registrar elmovimineto en inventario(tranferencia del movimiento creado,funcion para obtener el detalle del movimiento(id articulo,cantidad detransferencia, datos de la consulta de detalles),null)

            $idTransferenciaMovimiento=ProcesadorMovimientoInventarios::registraMovimientoEnInventario($movimiento,InventarioFisicoController::obtenerDetallesDelMovimientoEntrada($articulo,$cantidadTransferencia,$alDetalles),null);

            return $idTransferenciaMovimiento;

        }
        catch(\Exception $e){

            throw $e;

        }

    }

    //funcion para calcular la cantidad de referencia(datos deconsulta de detalles)
    public function getCantidadReferencia($alDetalles){
        $cantidad=0;
        //for($i=0; $i<$conta; $i++){
        if ($alDetalles->LOTL_LoteLocalidadId != '' && $alDetalles->LOTL_LoteLocalidadId != null) {
            $existencia = floatval(LotesLocalidades::find($alDetalles->LOTL_LoteLocalidadId)->LOTL_Cantidad);
        } elseif ($alDetalles->LOCA_LocalidadArticuloId != '' && $alDetalles->LOCA_LocalidadArticuloId != null) {

            if($alDetalles->COL_LOTE_ID != '' && $alDetalles->COL_LOTE_ID != null){
                $existencia = 0;
            }
            else {
                $existencia = floatval(LocalidadesArticulo::find($alDetalles[0]->LOCA_LocalidadArticuloId)->LOCA_Cantidad);
            }
        }
        else{
            $existencia = 0;
        }

        if ($alDetalles->COL_CANTIDAD_CONTADA == 0) {
            $alDetalles->COL_CANTIDAD_CONTADA = ($existencia) * (-1);//existencia actual localidad - cantidadajuste * -1
        } elseif ($alDetalles->COL_CANTIDAD_CONTADA > $existencia) {
            $alDetalles->COL_CANTIDAD_CONTADA = $alDetalles->COL_CANTIDAD_CONTADA - $existencia;//cantidadajuste - existencia actual localidad
        } elseif ($alDetalles->COL_CANTIDAD_CONTADA < $existencia) {
            $alDetalles->COL_CANTIDAD_CONTADA = (($existencia - $alDetalles->COL_CANTIDAD_CONTADA) * (-1));//existencia actual localidad - cantidadajuste  * -1
        } elseif ($alDetalles->COL_CANTIDAD_CONTADA == $existencia) {
            $alDetalles->COL_CANTIDAD_CONTADA = 0;
        }

        //$cantidad=$cantidad + ;//-$alDetalles->COL_CANTIDAD_SISTEMA);
        //}
        return $alDetalles->COL_CANTIDAD_CONTADA;
    }

    //funcion para obtener la tranferencia del movimiento(id del articulo, cantidad de la transferencia, nombre de la unidad demedida)
    public function obtenerTransferenciaMvoto($idart,$cantidadTransferencia,$nombreUM){

        try{

            $TraspasosMovtos=new TraspasoMovto();//nuevo medelo
            $TraspasosMovtos->TRAM_ART_ArticuloId=$idart;//id articulo
            $TraspasosMovtos->TRAM_CantidadATraspasar=$cantidadTransferencia;//cantidad de transferencia
            $TraspasosMovtos->TRAM_Razon="Inventario Fisico";//razon social
            $TraspasosMovtos->TRAM_Referencia=null;//referencia
            $TraspasosMovtos->TRAM_UnidadMedidadArt=$nombreUM;//nombre de la unidad de medida
            $TraspasosMovtos->TRAM_EstatusContable=true;//estatus contable
            $TraspasosMovtos->TRAM_FechaRequerida=null;//fecha requerida
            $TraspasosMovtos->TRAM_FechaRecibo=null;//fecha recibo
            $TraspasosMovtos->TRAM_FechaLibroMayor=null;//fecha libro mayor
            $TraspasosMovtos->TRAM_CMM_TipoTransferenciaId=ControlesMaestrosMultiples::$CMM_CDA_MovimientoEnInventario;//tipo de transferencia

            return $TraspasosMovtos;

        }
        catch(\Exception $e){

            throw $e;

        }

    }

    //funcion para obtener detalles del movimiento de entrada(id articulo, cantidad de transferencia, datos deconsulta de detalles)
    public function obtenerDetallesDelMovimientoEntrada($articulo,$cantidadTransferencia,$alDetalles){
        $data[]=array();//crear arreglo para guardar
        $alDetalle_fila=$alDetalles;
        $detalle_movto=new DetallesMovimientoInventario();//nuevo modelo detalles movto. inv.
        $detalle_movto->setIdAlmacen($alDetalle_fila->COL_ALMACEN_ID);//id del almacen
        $localidades=new Localidad();//nuevo modelo de localidad
        $localidades->COL_LOCALIDAD_ID=$alDetalle_fila->COL_LOCALIDAD_ID;//id de localidad
        $localidades->COL_ALMACEN_ID=$alDetalle_fila->COL_ALMACEN_ID;//id almacen
        $lotes = new Lotes();//nuevo modelo de lotes
        $lotes->COL_LOTE_ID = $alDetalle_fila->COL_LOTE_ID;//id lote
        $detalle_movto->setLocalidad($localidades);//asignar id de la localidad
        $detalle_movto->setLote($lotes);//asignar id del lote
        $detalle_movto->setCantidadTransferir($cantidadTransferencia);//asignar cantidad a transferir (cant.contada - cant. sistema)
        $data[0]=$detalle_movto;//guarda modelo en arreglo

        return $data;
    }

    public function actualizarIdTransferencia($idInventario,$idArticulo,$idMovimiento,$idLote){

        try{

            //date_default_timezone_set('America/Mexico_City');
            $hoy=date('d/m/Y H:i:s');

            $InventarioFisicoDetalle=InventarioFisicoDetalle::where('IFD_InventarioFisicoDetId','=',$idInventario)
                ->get();



            $IFD = InventarioFisicoDetalle::find($InventarioFisicoDetalle[0]->IFD_InventarioFisicoDetId);



            $IFD->IFD_TRAM_TraspasoMovtoId = $idMovimiento;
            $IFD->IFD_Bloqueado = 1;
            $IFD->IFD_FechaUltimaModificacion = $hoy;
            $IFD->save();

            /*\DB::table('InventarioFisicoDetalle')->where('IFD_InventarioFisicoDetId', '=', $InventarioFisicoDetalle[0]->IFD_InventarioFisicoDetId)
                
                ->update(
                    array(
                        'IFD_TRAM_TraspasoMovtoId' => $idMovimiento,
                        'IFD_Bloqueado' => 1,
                        'IFD_FechaUltimaModificacion' => $hoy
                        //'IFD_EMP_ModificadoPor' => ''
                    )
                );
                */

        }
        catch(\Exception $e){

            throw $e;

        }

    }

    public function desbloqueo($id){

        try{

            //date_default_timezone_set('America/Mexico_City');
            $hoy=date('d/m/Y H:i:s');

            $InventarioFisicoDetalle = InventarioFisicoDetalle::where('IFD_IF_InventarioFisicoId','=',$id)->get();

            $cuentaIFD = count($InventarioFisicoDetalle);
            $ban = false;
            for($x = 0; $x < $cuentaIFD; $x++)
            {

                if($InventarioFisicoDetalle[$x]->IFD_Bloqueado == 1)
                {

                    $ban = true;
                    break;

                }

            }

            if($ban == false)
            {

                $InventarioFisico=InventarioFisico::find($id);
                $InventarioFisico->IF_Bloqueado=true;
                $InventarioFisico->IF_FechaUltimaModificacion=$hoy;
                $InventarioFisico->save();

            }

        }
        catch(\Exception $e){

            throw $e;

        }

    }

    public function verificaCantidades($hmDetalles){
        $ban=true;

    }
    //////////////////////////////////////////////////////////FIN FUNCIONES PROCESADOR//////////////////////////////////////////////////////

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {

        \DB::beginTransaction();

        try {
            //RECUPERAR EL ARREGLOCON DATOS
            $arreglo = NewRequest::input('arreglo');
            $objeto = json_decode($arreglo);

            $IF_FechaInventario = NewRequest::input('fechaIF');

            $sub = \DB::table('InventarioFisico')->insert(
                array(
                    'IF_FechaInventario' => $IF_FechaInventario
                )
            );

            $InventarioFisicoId = InventarioFisico::orderby('IF_FechaUltimaModificacion', 'DESC')->first()->IF_InventarioFisicoId;

            $cuentaArreglo = count($objeto);

            for($val = 0; $val < $cuentaArreglo; $val ++)
            {

                if($objeto[$val][6] == '')
                {

                    $objeto[$val][6] = NULL;

                }

                \DB::table('InventarioFisicoDetalle')->insert(
                    array(
                        'IFD_IF_InventarioFisicoId' => $InventarioFisicoId,
                        'IFD_ART_ArticuloId' => $objeto[$val][2],//ART_ArticuloId
                        'IFD_CantidadContada' => $objeto[$val][1],//IFD_CantidadContada
                        'IFD_CantidadAnterior' => $objeto[$val][0],//LOTL_Cantidad
                        'IFD_ALM_AlmacenId' => $objeto[$val][3],//'ALM_AlmacenId
                        'IFD_LOC_LocalidadId' => $objeto[$val][4],//LOC_LocalidadId
                        'IFD_LOT_LoteId' => $objeto[$val][5],//LOT_LoteId
                        'IFD_EMP_ResponsableId' => $objeto[$val][6],//EMP_EmpleadoId
                        'IFD_FechaConteo' => $IF_FechaInventario
                    )
                );

            }
            //return json_encode(array());

            $mensaje = 'Se registró el Inventario Fisico con éxito.';

            \DB::commit();

            return ['Status' => 'Valido', 'Mensaje' => $mensaje];

        }
        catch (\Exception $e) {

            \DB::rollback();

            return ['Status' => 'Error', 'Mensaje' => 'No se registró el Inventario Fisico. Ocurrió un error al realizar el proceso. <br><br><br>Error: <br>'.$e->getMessage()];

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
        \DB::beginTransaction();

        try {

            //date_default_timezone_set('America/Mexico_City');
            $hoy=date('d/m/Y H:i:s');

            //RECUPERAR EL ARREGLOCON DATOS
            $arreglo = NewRequest::input('arreglo');
            $objeto = json_decode($arreglo);

            $IF_FechaInventario = NewRequest::input('fechaIF');

            $users = \DB::table('InventarioFisicoDetalle')->where('IFD_IF_InventarioFisicoId','=',$id)->get();

            $cuentaArreglo = count($objeto);

            for($val = 0; $val < $cuentaArreglo; $val++)
            {

                $ban=0;$posi=0;$suma=0;
                foreach ($users as $user)
                {

                    if($user->IFD_ART_ArticuloId == $objeto[$val][2] && $user->IFD_LOC_LocalidadId == $objeto[$val][4] && $user->IFD_LOT_LoteId == $objeto[$val][5])
                    {

                        $ban=1;
                        $posi=$suma;

                    }
                    $suma++;

                }

                if($ban==1)
                {

                    if(intval($users[$posi]->IFD_CantidadContada) != intval($objeto[$val][1]))
                    {

                        \DB::table('InventarioFisicoDetalle')->where('IFD_IF_InventarioFisicoId', '=', $id)
                            ->where('IFD_ART_ArticuloId','=',$users[$posi]->IFD_ART_ArticuloId)
                            ->where('IFD_LOC_LocalidadId','=',$users[$posi]->IFD_LOC_LocalidadId)
                            ->where('IFD_LOT_LoteId','=',$users[$posi]->IFD_LOT_LoteId)
                            ->update(
                                array(
                                    'IFD_CantidadContada' => $objeto[$val][1],
                                    'IFD_FechaConteo' => $hoy,
                                    'IFD_FechaUltimaModificacion' => $hoy
                                )
                            );

                    }

                }
                else
                {

                    \DB::table('InventarioFisicoDetalle')->insert(
                        array(
                            'IFD_IF_InventarioFisicoId' => $id,
                            'IFD_ART_ArticuloId' => $objeto[$val][2],//ART_ArticuloId
                            'IFD_CantidadContada' => $objeto[$val][1],//IFD_CantidadContada
                            'IFD_CantidadAnterior' => $objeto[$val][0],//LOTL_Cantidad
                            'IFD_ALM_AlmacenId' => $objeto[$val][3],//'ALM_AlmacenId
                            'IFD_LOC_LocalidadId' => $objeto[$val][4],//LOC_LocalidadId
                            'IFD_LOT_LoteId' => $objeto[$val][5],//LOT_LoteId
                            'IFD_EMP_ResponsableId' => $objeto[$val][6] == '' ? null :  $objeto[$val][6],//EMP_EmpleadoId
                            'IFD_FechaConteo' => $IF_FechaInventario
                        )
                    );

                }

            }
            //return json_encode(array());
            $mensaje = 'Se Actualizo el Inventario Fisico con éxito.';

            \DB::commit();

            return ['Status' => 'Valido', 'Mensaje' => $mensaje];

        }
        catch (\Exception $e) {

            \DB::rollback();

            return ['Status' => 'Error', 'Mensaje' => 'No se Actualizo el Inventario Fisico. Ocurrió un error al realizar el proceso. <br><br><br>Error: <br>'.$e->getMessage()];

        }

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

    private function isDatosSinGuardar($localidadesId, $fecha){

        $datosSinGuardar = \DB::select(
            \DB::raw(
                    "SELECT ART_ArticuloId as LOCA_ART_ArticuloId,
                        ART_CodigoArticulo, ART_Nombre,
                        ART_CantidadAMano, ART_SeguimientoLocMult,
                        ART_SeguimientoLotMult, CMUM_Nombre,
                        ALM_AlmacenId, ALM_Nombre,
                        LOC_LocalidadId AS LOCA_LOC_LocalidadId,
                        LOC_Nombre, ISNULL(LOCA_Cantidad, 0) AS LOCA_Cantidad,
                        LOTL_LoteLocalidadId, LOT_LoteId, LOT_CodigoLote,
                        ISNULL(LOTL_Cantidad,0)AS LOTL_Cantidad,
                        ISNULL(LOTL_Cantidad,0) AS IFD_CantidadAnterior
                    FROM Articulos
                    INNER JOIN ControlesMaestrosUM ON CMUM_UnidadMedidaId=ART_CMUM_UMInventarioId
                    LEFT JOIN LocalidadesArticulo ON LOCA_ART_ArticuloId=ART_ArticuloId
                    LEFT JOIN Localidades ON LOCA_LOC_LocalidadId=LOC_LocalidadId
                    LEFT JOIN (LotesLocalidades
                              INNER JOIN Lotes ON LOTL_LOT_LoteId = LOT_LoteId)
                              ON LOT_ART_ArticuloId = ART_ArticuloId AND LOTL_LOC_LocalidadId = LOC_LocalidadId
                    LEFT JOIN Almacenes ON LOC_ALM_AlmacenId=ALM_AlmacenId
                    LEFT JOIN Empleados ON EMP_EmpleadoId=ALM_EMP_ResponsableId
                    WHERE LOC_LocalidadId IN (".$localidadesId.")
                    AND LOTL_LoteLocalidadId NOT IN (
                                    SELECT
                                    LOTL_LoteLocalidadId
                                FROM InventarioFisico
                                INNER JOIN InventarioFisicoDetalle ON IFD_IF_InventarioFisicoId=IF_InventarioFisicoId
                                LEFT JOIN Localidades ON IFD_LOC_LocalidadId=LOC_LocalidadId
                                LEFT JOIN LocalidadesArticulo ON LOCA_ART_ArticuloId=IFD_ART_ArticuloId AND LOCA_LOC_LocalidadId=LOC_LocalidadId
                                LEFT JOIN Lotes ON LOT_LoteId = IFD_LOT_LoteId
                                LEFT JOIN LotesLocalidades ON LOTL_LOT_LoteId = LOT_LoteId AND LOTL_LOC_LocalidadId = LOC_LocalidadId
                                WHERE LOC_LocalidadId IN (".$localidadesId.")
                                AND IF_FechaInventario='".$fecha."'

                                GROUP BY LOTL_LoteLocalidadId
                    )
                     AND LOC_LocalidadGeneral = 0 AND ART_Eliminado = 0 AND ART_Activo = 1 AND LOTL_Cantidad > 0"
            )
        );

        return count($datosSinGuardar) > 0 ? true : false;

    }

    public function consultaYGeneraPDF(){

        try {

            $fecha = NewRequest::input('Fecha');
            $localidades = NewRequest::input('Localidades');

            $datos = InventarioFisicoController::consultaDatosPDF($fecha, $localidades);
            $ruta = $this->generaPDF($datos,$fecha);

            $response = array("action" => "success");

            return ['Status' => 'Valido', 'respuesta' => $response, 'ruta' => $ruta];

        }
        catch (\Exception $e){

            return ['Status' => 'Error', 'Mensaje' => 'Ocurrió un error al realizar el proceso. Error: ' .$e->getMessage()];

        }

    }

    public function consultaDatosPDF($fecha, $localidades){

        $consulta = \DB::select(

            \DB::raw(

                "SELECT
                        ART_CodigoArticulo AS CODIGO,
                        ART_Nombre AS NOMBRE,
                        CMUM_Nombre AS UNIDAD_MEDIDA,
                        ISNULL(LOCA_Cantidad, 0) AS CANTIDAD_ANTERIOR,
                        SUM(IFD_CantidadContada) AS CANTIDAD_CONTADA
                    FROM InventarioFisico
                    INNER JOIN InventarioFisicoDetalle ON IFD_IF_InventarioFisicoId=IF_InventarioFisicoId
                    INNER JOIN Articulos ON ART_ArticuloId=IFD_ART_ArticuloId
                    INNER JOIN ControlesMaestrosUM ON CMUM_UnidadMedidaId=ART_CMUM_UMInventarioId
                    LEFT JOIN Localidades ON IFD_LOC_LocalidadId=LOC_LocalidadId
                    LEFT JOIN LocalidadesArticulo ON LOCA_ART_ArticuloId=ART_ArticuloId AND LOCA_LOC_LocalidadId=LOC_LocalidadId
                    LEFT JOIN Almacenes ON LOC_ALM_AlmacenId=ALM_AlmacenId
                    WHERE LOC_LocalidadId IN (".$localidades.")
                    AND IF_FechaInventario = '".$fecha."'
                    GROUP BY
                        ART_CodigoArticulo,
                        ART_Nombre,
                        ART_CantidadAMano,
                        CMUM_Nombre,
                        ALM_Nombre,
                        LOC_Nombre,
                        LOCA_Cantidad
                    ORDER BY
                        ALM_Nombre,
                        LOC_Nombre,
                        ART_CodigoArticulo"

            )

        );

        return $consulta;

    }

    public function generaPDF ($datos,$fecha) {
        try{

            //date_default_timezone_set('America/Mexico_City');
            $hoy=date('d-m-Y ');

            require_once '../public/plugins/tcpdf/tcpdf.php';
            require_once public_path().'/plugins/X509/File/X509.php';

            $nombre_archivo = 'Inventario'.trim($fecha);
            $fev32 = new FEv32();
            $this->ruta_xml = $fev32->getPathSave().$nombre_archivo;
            $this->ruta = $fev32->getPathSaveSimple().$nombre_archivo;

            $cantidad_decimales = \DB::select(\DB::raw("SELECT CMA_Valor FROM ControlesMaestros WHERE CMA_Control = 'CMA_VEN_DecimalesCantidades'"))[0]->CMA_Valor;
            $precios_decimales = \DB::select(\DB::raw("SELECT CMA_Valor FROM ControlesMaestros WHERE CMA_Control = 'CMA_VEN_DecimalesPrecios'"))[0]->CMA_Valor;
            //$poliza_decimales = \DB::select(\DB::raw("SELECT CMA_Valor FROM ControlesMaestros WHERE CMA_Control = 'CMA_CCNF_DecimalesPolizas'"))[0]->CMA_Valor;

            $pdf = new EncabezadoPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $pdf->setCodigo($fecha);
            $pdf->setFecha($hoy);
            $pdf->setTipoDocumento('FECHA INVENTARIO');
            $pdf->SetTopMargin(38);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
            $pdf->AddPage('P', 'A4');

            /*// Datos Cliente
            $pdf->SetFont('times', '', 8, '', 'false');
            $tbl =
                '<table cellpadding="1" cellspacing="0" border="0">
                    <hr size="2"/>
                    <tr>
                    <td width="267" style="font-size: 5px"><b>CLIENTE</b></td>
                    <td width="86" style="font-size: 5px"><b></b></td>
                    </tr>
                    <tr>
                    <td width="267" style="font-style: oblique; font-size: 9px"><b>('.$factura[0]->CLI_CodigoCliente.') '.$factura[0]->FTD_RazonSocial.'</b></td>
					</tr>
					<tr>
					<td width="267">'.$factura[0]->FTD_DIRECCION.'</td>
					</tr>
					<tr>
					<td width="267">'.$factura[0]->CLI_LOCALIZACION.'</td>
					</tr>
					<tr>
					<td width="267">'.$factura[0]->CLI_TELEFONICOS.'</td>
					</tr>
					<tr>
					<td width="267"> RFC: '.$factura[0]->FTD_RFC.'</td>
					<td width="266"></td>
					</tr>
					</table>';

            $pdf->writeHTML($tbl, true, false, true, false, '');

            // Datos Cliente
            $pdf->SetFont('times', '', 8, '', 'false');
            $tbl =
                '<table cellpadding="1" cellspacing="0" border="0">
                    <hr size="2"/>
                    <tr>
                    <td width="267" style="font-size: 5px"><b>ELABORÓ</b></td>
                    </tr>
                    <tr>
                    <td width="267"><b>'.$factura[0]->EMP_NombreCompleto.'</b></td>
					</tr>
					</table>';

            $pdf->writeHTML($tbl, true, false, true, false, '');*/

            // Detalle de la factura (Artículos) y totales.
            $pdf->SetFont('times', '', 8, '', 'false');
            $tbl = '<table cellpadding="1" cellspacing="1" border="0">
					<hr size="2"/>
					<tr>
					<td width="40" align="left"><b>CÓDIGO</b></td>
					<td width="254" align="left"><b>DESCRIPCIÓN</b></td>
					<td width="60" align="left"><b>UM</b></td>
					<td width="59.5" align="right"><b>CANTIDAD ANTERIOR</b></td>
					<td width="59.5" align="right"><b>CANTIDAD CONTADA</b></td>
					</tr>
					<hr size="2"/>
					</table>';
            $tbl .= '<table cellpadding="1" cellspacing="1" border="0">';
            $total = count($datos);
            for($i = 0; $i < $total; $i++) {
                $tbl .= '<tr>
					<td width="40" align="left">'.$datos[$i]->CODIGO.'</td>
					<td width="254" align="left">'.$datos[$i]->NOMBRE.'</td>
					<td width="60" align="left">'.$datos[$i]->UNIDAD_MEDIDA.'</td>
					<td width="59.5" align="right">'.number_format($datos[$i]->CANTIDAD_ANTERIOR, $cantidad_decimales, '.', ',').'</td>
					<td width="59.5" align="right">'.number_format($datos[$i]->CANTIDAD_CONTADA, $precios_decimales, '.', ',').'</td>
					</tr>';

            }
            $tbl .= '</table>
					 <hr size="2"/>';
            $pdf->writeHTML($tbl, true, false, true, false, '');
            /*$tbl_izq =	'<table cellpadding="1" cellspacing="1" border="0" width="410">
					 <tr>
					 	<td border="1" align="left" colspan="4">'.$factura[0]->TOTAL_LETRAS.'</td>
					 </tr>
					 </table>';
            $tbl_der =	'<table cellpadding="1" cellspacing="0" border="0.5" width="100">
					 <tr>
					 	<td width="59.5" style="background-color:#d4d4d4" align="left"><b>Subtotal:</b></td>
					 	<td width="59.5" align="right">'.number_format($factura[0]->IMPORTE_TOTAL, $precios_decimales, '.', ',').'</td>
					 </tr>
					 <tr>
					 	<td width="59.5" style="background-color:#d4d4d4" align="left"><b>IVA: </b>'.$factura[0]->CMIVA_Descripcion.'</td>
					 	<td width="59.5" align="right">'.number_format($factura[0]->IVA_TOTAL, $precios_decimales, '.', ',').'</td>
					 </tr>
					 <tr>
					 	<td width="59.5" style="background-color:#d4d4d4" align="left"><b>Total:</b></td>
					 	<td width="59.5" align="right">'.number_format($factura[0]->TOTAL, $precios_decimales, '.', ',').'</td>
					 </tr>
					 </table>';

            $y = $pdf->getY();*/

            $pdf->SetFillColor(0, 0, 0, 100);
            $pdf->SetTextColor(0, 0, 0, 100);
            //$pdf->writeHTMLCell(80, '', '', $y, $tbl_izq, 0, 0, 0, true, 'J', true);
            //$pdf->writeHTMLCell(80, '', 156.5, $y, $tbl_der, 0, 0, 0, true, 'J', true);
            $pdf->Output($this->ruta_xml.'.pdf', 'F');

            return $this->ruta;

        }catch (\FileException $e) {

            throw $e;

        }

    }

}
