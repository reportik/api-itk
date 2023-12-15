<?php namespace App\Http\Controllers\Inventario;

use Carbon\Carbon;
use App\Http\Controllers\Embarques\EmbarquesController;
use App\Http\Controllers\Inventario\Articulos\ArticulosController;
use App\Http\Controllers\Sistema\DAOGeneralController;
use App\Http\Controllers\Sistema\DataBaseSession;
use App\Http\Requests;
use App\Http\Controllers\Controller;

//use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request;
use App\Mapeos\Controles\ControlesMaestrosMultiples;
use App\Models\Articulos;
use App\Models\Inventario\Articulos\Localidad;
use App\Models\Inventario\InventarioFisico\DetallesMovimientoInventario;
use App\Models\Inventario\InventarioFisico\ProcesadorMovimientoInventarios;
use App\Models\Inventario\InventarioFisico\TraspasoMovto;
use App\Models\Localidades;
use App\Models\Lotes;
use App\Models\TraspasosDetalle;
use App\Models\TraspasosRecibos;
use App\Models\TraspasosSolicitudes;
use App\Models\TraspasosSolicitudesDetalle;
use Response;
use Illuminate\Support\Facades\Request as NewRequest;

class RecibosTraspasosController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{

        $almacenes = array(''=>'Selecciona Alamacen') + Localidad::select('LOC_LocalidadId','ALM_Nombre')
                ->join('Almacenes', 'ALM_AlmacenId', '=', 'LOC_ALM_AlmacenId')
                ->where('LOC_Eliminado','=',0)
                ->orderby('ALM_Nombre','ASC')
                ->lists('ALM_Nombre','LOC_LocalidadId')->all();

        $almacenesLocalidades = array(''=>'Selecciones Almacen/Localidad') + Localidad::select('LOC_LocalidadId', \DB::raw("ALM_Nombre + (CASE WHEN LOC_LocalidadGeneral = 0 THEN ' - ' + LOC_Nombre ELSE '' END) AS FULL_NAME"))
                ->join('Almacenes','ALM_AlmacenId','=','LOC_ALM_AlmacenId')
                ->lists('FULL_NAME','LOC_LocalidadId')->all();

        //date_default_timezone_set('America/Mexico_City');
        $fecha=date('d/m/Y');

        return view('Inventario.RecibosTraspasos.create', compact('almacenesLocalidades', 'almacenes', 'fecha'));

	}

    public function ajaxResponseListado(){

        $FechaInicio = NewRequest::input('fechaDesde');
        $FechaFinal = NewRequest::input('fechaHasta');

        $consulta = \DB::select(

            \DB::raw(

                "SELECT DISTINCT
                    TRS_TraspasoSolicitudId as DT_RowId,
                    TRS_CodigoSolicitud,
                    CAST(TRS_FechaSolicitud AS DATE) AS TRS_FechaSolicitud,
                    RUT_Nombre,
                    (EMP_Nombre+' '+EMP_PrimerApellido+' '+EMP_SegundoApellido) AS VENDEDOR,
                    DESTINO.LOC_Nombre AS ORIGEN,
                    ORIGEN.LOC_Nombre AS DESTINO,
                    CMM_Valor
                FROM TraspasosSolicitudes
                INNER JOIN Localidades ORIGEN ON ORIGEN.LOC_LocalidadId = TRS_LOC_LocalidadOrigenId
                INNER JOIN Localidades DESTINO ON DESTINO.LOC_LocalidadId = TRS_LOC_LocalidadDestinoId
                LEFT JOIN TransportesUnidades ON TUN_LOC_LocalidadId = TRS_LOC_LocalidadOrigenId
                LEFT JOIN Rutas ON RUT_TUN_TransporteUnidadId = TUN_TransporteUnidadId
                LEFT JOIN Empleados ON EMP_EmpleadoId = RUT_EMP_VendedorId
                INNER JOIN ControlesMaestrosMultiples ON TRS_CMM_EstatusSolicitudId = CMM_ControlId
                AND CMM_ControlId IN('9367F403-C6E8-4952-8276-F71B8E92B641', '58B06E0A-7D6F-482A-AB9D-287FA7872E7E')
                AND CAST(TRS_FechaSolicitud AS DATE) BETWEEN '".$FechaInicio."' AND '".$FechaFinal."'
                WHERE ISNULL(TUN_Eliminado,0) = 0
                ".(DataBaseSession::isPermisoCorporativo() ? "" : " AND ORIGEN.LOC_ALM_AlmacenId IN ( SELECT ALM_AlmacenId FROM Almacenes WHERE ALM_AlmacenId IN (".DataBaseSession::getAlmacenesPorCediId()."))")."
                 AND  TRS_Eliminado = 0
                 ORDER BY TRS_FechaSolicitud desc, TRS_CodigoSolicitud ,VENDEDOR"

            )

        );

        $ajaxData = array();
        $ajaxData['data'] = $consulta;
        $ajaxData['options'] = array();

        return (json_encode($ajaxData));

    }

    public function consultararticulosNoTraspasados($TRSD_TRS_TraspasoSolicitudId){

        $consultaDetallesSolicitudTraspaso = \DB::select(
            \DB::raw(
                "select
                    ART_CodigoArticulo + ' - ' + ART_Nombre AS ART_CodigoYNombre,
                    TRAD_CantidadATraspasar
                from TraspasosSolicitudesDetalle
                left join TraspasosDetalle on TRSD_DetalleId = TRAD_TRSD_DetalleId
                INNER JOIN Articulos ON ART_ArticuloId = TRSD_ART_ArticuloId
                where TRSD_TRS_TraspasoSolicitudId = '".$TRSD_TRS_TraspasoSolicitudId."'"
            )
        );

        return Response::json($consultaDetallesSolicitudTraspaso);

    }

    public function consultarDetalleTraspasoRT2($TRSD_TRS_TraspasoSolicitudId){

        $consultaDetallesSolicitudTraspaso = \DB::select(
            \DB::raw(
                "SELECT
                    ART_CodigoArticulo, ART_Nombre, CMUM_Nombre, LOT_CodigoLote,
                    CAST(ISNULL(LOCA_Cantidad,0)AS INT) AS Existencia_Localidad,
                    CAST(ISNULL(EXISTENCIA.LOTL_Cantidad,0)AS INT) AS Existencia_Lote,
                    TRSD_Cantidad AS CANTIDAD_SOLICITADA, ABS(SUM(TRLOT_CantidadTraspaso)) AS CANTIDAD_TRASPASADA,
                    SUM(ISNULL(TRAR_CantidadRecibo,0.0)) AS CANTIDAD_RECIBO,
                    ABS(SUM(TRLOT_CantidadTraspaso)) - SUM(ISNULL(TRAR_CantidadRecibo,0.0)) AS CANTIDAD_POR_RECIBIR,
                    CMM_Valor, TRAD_TraspasoDetalleId, LOT_LoteId, TRSD_DetalleId,
                    TRS_LOC_LocalidadOrigenId,TRS_LOC_LocalidadDestinoId
                FROM TraspasosDetalle
                INNER JOIN TraspasosSolicitudesDetalle ON TRAD_TRSD_DetalleId = TRSD_DetalleId
                INNER JOIN TraspasosLotes ON TRAD_TRAM_TraspasoMovtoId = TRLOT_TRAM_TraspasoMovtoId
                INNER JOIN LotesLocalidades PREVIO ON TRLOT_LOTL_LoteLocalidadId = PREVIO.LOTL_LoteLocalidadId
                INNER JOIN Lotes ON LOT_LoteId = LOTL_LOT_LoteId
                INNER JOIN Articulos ON ART_ArticuloId=TRSD_ART_ArticuloId
                INNER JOIN ControlesMaestrosUM ON CMUM_UnidadMedidaId = TRSD_CMUM_UnidadMedidaId
                INNER JOIN TraspasosSolicitudes ON TRS_TraspasoSolicitudId = TRSD_TRS_TraspasoSolicitudId
                INNER JOIN ControlesMaestrosMultiples ON CMM_ControlId=TRS_CMM_EstatusSolicitudId
                LEFT JOIN LotesLocalidades EXISTENCIA  ON LOT_LoteId = EXISTENCIA.LOTL_LOT_LoteId AND TRS_LOC_LocalidadOrigenId = EXISTENCIA.LOTL_LOC_LocalidadId
                LEFT JOIN Localidades ON LOC_LocalidadId = TRS_LOC_LocalidadOrigenId
                LEFT JOIN LocalidadesArticulo ON ART_ArticuloId = LOCA_ART_ArticuloId AND LOCA_LOC_LocalidadId = LOC_LocalidadId
                LEFT JOIN TraspasosRecibos ON TRAR_TRAD_TraspasoDetalleId= TRAD_TraspasoDetalleId
                WHERE
                    TRSD_TRS_TraspasoSolicitudId = '".$TRSD_TRS_TraspasoSolicitudId."'
                GROUP BY
                    TRSD_DetalleId,ART_CodigoArticulo, ART_Nombre, CMUM_Nombre, LOT_CodigoLote,
                    LOCA_Cantidad,EXISTENCIA.LOTL_Cantidad, TRSD_Cantidad, CMM_Valor, TRAD_TraspasoDetalleId,
                    LOT_LoteId,TRS_LOC_LocalidadOrigenId,TRS_LOC_LocalidadDestinoId
                ORDER BY
                    ART_CodigoArticulo ASC"
            )
        );

        return Response::json($consultaDetallesSolicitudTraspaso);

    }

    public function consultarDetalleTraspasoRT($TRSD_TRS_TraspasoSolicitudId,$LOCA_LOC_LocalidadId){
        $consultaDetallesSolicitudTraspaso = \DB::select(
            \DB::raw(
                "SELECT ART_CodigoArticulo, ART_Nombre, CMUM_Nombre, LOT_CodigoLote,
                CAST(ISNULL(LOCA_Cantidad,0)AS INT) AS Existencia_Localidad,CAST(ISNULL(LOTL_Cantidad,0)AS INT) AS Existencia_Lote,
                TRSD_Cantidad AS CANTIDAD_SOLICITADA, TRAD_CantidadATraspasar AS CANTIDAD_TRASPASADA ,SUM(ISNULL(TRAR_CantidadRecibo,0.0)) AS CANTIDAD_RECIBO,
                TRAD_CantidadATraspasar - SUM(ISNULL(TRAR_CantidadRecibo,0.0)) AS CANTIDAD_POR_RECIBIR
                , CMM_Valor, TRAD_TraspasoDetalleId, LOT_LoteId,
                TRSD_DetalleId
                FROM TraspasosDetalle
                INNER JOIN TraspasosSolicitudesDetalle ON TRAD_TRSD_DetalleId = TRSD_DetalleId
                INNER JOIN TraspasosLotes ON TRAD_TRAM_TraspasoMovtoId = TRLOT_TRAM_TraspasoMovtoId
                INNER JOIN LotesLocalidades ON TRLOT_LOTL_LoteLocalidadId = LOTL_LoteLocalidadId
                INNER JOIN Lotes ON LOT_LoteId = LOTL_LOT_LoteId
                INNER JOIN Articulos ON ART_ArticuloId=TRSD_ART_ArticuloId
                INNER JOIN ControlesMaestrosUM ON CMUM_UnidadMedidaId = TRSD_CMUM_UnidadMedidaId
                INNER JOIN TraspasosSolicitudes ON TRS_TraspasoSolicitudId = TRSD_TRS_TraspasoSolicitudId
                INNER JOIN ControlesMaestrosMultiples ON CMM_ControlId=TRS_CMM_EstatusSolicitudId
                LEFT JOIN Localidades ON LOC_LocalidadId = '".$LOCA_LOC_LocalidadId."'
                LEFT JOIN LocalidadesArticulo ON ART_ArticuloId = LOCA_ART_ArticuloId AND LOCA_LOC_LocalidadId = LOC_LocalidadId
                LEFT JOIN TraspasosRecibos ON TRAR_TRAD_TraspasoDetalleId= TRAD_TraspasoDetalleId
                WHERE TRSD_TRS_TraspasoSolicitudId = '".$TRSD_TRS_TraspasoSolicitudId."'
                GROUP BY
                TRSD_DetalleId,ART_CodigoArticulo, ART_Nombre, CMUM_Nombre, LOT_CodigoLote,LOCA_Cantidad,LOTL_Cantidad, TRSD_Cantidad,
                CMM_Valor, TRAD_TraspasoDetalleId, LOT_LoteId,TRAD_CantidadATraspasar
                ORDER BY ART_CodigoArticulo ASC"
            )
        );

        return Response::json($consultaDetallesSolicitudTraspaso);
    }

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
        //date_default_timezone_set('America/Mexico_City');
        $fecha=date('d/m/Y');

        return view('Inventario.CancelarReciboTraspaso.cancelarrecibotraspaso', compact('fecha'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{

        if (\Illuminate\Support\Facades\Request::isMethod('get')) {

            $FechaInicio = NewRequest::input('fechaDesde');
            $FechaFinal = NewRequest::input('fechaHasta');

            $consulta = \DB::select(
                \DB::raw(
                    "select
                        TRS_TraspasoSolicitudId as DT_RowId,
                        TRS_CodigoSolicitud
                    from TraspasosSolicitudes
                    inner join ControlesMaestrosMultiples on CMM_ControlId = TRS_CMM_EstatusSolicitudId
                    where TRS_Eliminado = 0
                    and (TRS_CMM_EstatusSolicitudId = 'F39BE884-DDCD-4A1C-B8CB-38322917583B'
                    or TRS_CMM_EstatusSolicitudId = 'B8749E06-0936-4171-85C1-90615BCCE41E')
                    AND CAST(TRS_FechaSolicitud AS DATE) BETWEEN '".$FechaInicio."' AND '".$FechaFinal."'
                    ".(DataBaseSession::isPermisoCorporativo() ? "" : " AND TRS_LOC_LocalidadOrigenId IN ( SELECT LOC_LocalidadId FROM Localidades WHERE LOC_ALM_AlmacenId IN (".DataBaseSession::getAlmacenesPorCediId()."))")."
                    order by
                        TRS_CodigoSolicitud
                    desc"
                )
            );

            $ajaxData = array();
            $ajaxData['data'] = $consulta;
            $ajaxData['options'] = array();
            return (json_encode($ajaxData));

        }
        elseif (\Illuminate\Support\Facades\Request::isMethod('post')) {

            if(\Illuminate\Support\Facades\Request::input('action') == 'remove'){

                $operacion = "restaC";
                //ACTUALIZA EL ESTADO DE LA SOLICITUD
                $TRS_TraspasoSolicitud = TraspasosSolicitudes::find(\Illuminate\Support\Facades\Request::input('id')[0]);
                $TRS_TraspasoSolicitud->TRS_CMM_EstatusSolicitudId = ControlesMaestrosMultiples::$CMM_EstatusSolicitudTraspaso_Solicitado;
                $LocalidadId = $TRS_TraspasoSolicitud->TRS_LOC_LocalidadOrigenId;
                \DB::table('TraspasosSolicitudes')->where('TRS_TraspasoSolicitudId', '=', $TRS_TraspasoSolicitud->TRS_TraspasoSolicitudId)
                    ->update(
                        array(
                            'TRS_CMM_EstatusSolicitudId' => $TRS_TraspasoSolicitud->TRS_CMM_EstatusSolicitudId
                        )
                    );

                //BUSCA LOS DETALLES DE LA SOLICITUD
                $TRSD_Detalle = TraspasosSolicitudesDetalle::select('TRSD_DetalleId')
                    ->where('TRSD_TRS_TraspasoSolicitudId','=',$TRS_TraspasoSolicitud->TRS_TraspasoSolicitudId)
                    ->get();

                //BUSCA LOS TRASPAOS REALIZADOS DE ESA SOLICITUD
                $Cuenta_TRSD_Detalle = count($TRSD_Detalle);
                for($x = 0; $x < $Cuenta_TRSD_Detalle; $x++){

                    $TraspasosDetalle = TraspasosDetalle::select('TRAD_TraspasoDetalleId')
                        ->where('TRAD_TRSD_DetalleId','=',$TRSD_Detalle[$x]->TRSD_DetalleId)
                        ->get();

                    $Cuenta_TraspasosDetalle = count($TraspasosDetalle);

                    if($Cuenta_TraspasosDetalle > 0){

                        $TraspasosRecibo = TraspasosRecibos::select('TRAR_TRAM_TraspasoMovtoId','TRAR_TRAD_TraspasoDetalleId','TRAR_CantidadRecibo','TRAR_Eliminado')
                            ->where('TRAR_TRAD_TraspasoDetalleId','=',$TraspasosDetalle[0]->TRAD_TraspasoDetalleId)
                            ->get();

                        //ACTUALIZA EL CAMPO DE ELIMINADO
                        $TraspasosRecibo[0]->TRAR_Eliminado = 1;
                        \DB::table('TraspasosRecibos')->where('TRAR_TRAD_TraspasoDetalleId', '=', $TraspasosRecibo[0]->TRAR_TRAD_TraspasoDetalleId)
                            ->update(
                                array(
                                    'TRAR_Eliminado' => $TraspasosRecibo[0]->TRAR_Eliminado
                                )
                            );

                        $Cuenta_TraspasosRecibo = count($TraspasosRecibo);

                        if($Cuenta_TraspasosRecibo > 0){

                            $TraspasoMovto = TraspasoMovto::where('TRAM_TraspasoMovtoId','=',$TraspasosRecibo[0]->TRAR_TRAM_TraspasoMovtoId)->get();
                            //busca articulo
                            $articulo = ArticulosController::buscaPorId($TraspasoMovto[0]->TRAM_ART_ArticuloId);
                            //inserta y obtiene el ID del traspaso Movto
                            $idTrapasosMovtos = TraspasosController::obtenerTransferenciaMvoto($articulo,$TraspasoMovto,$TraspasosRecibo[0]->TRAR_TRAD_TraspasoDetalleId,$operacion);
                            //OBTIENE CANTIDAD ANTERIOR Y LOCALIDAD
                            $CantidadAnteriorYLocalidadId = TraspasosController::restarPiezasEnLocalidad($TraspasoMovto[0]->TRAM_CantidadATraspasar,$articulo->ART_ArticuloId,$LocalidadId,$operacion);
                            //INSERTA EN TRASPASOS LOCALIDADES
                            TraspasosController::guardaTransferencia($idTrapasosMovtos,$CantidadAnteriorYLocalidadId[0],$CantidadAnteriorYLocalidadId[1],$TraspasoMovto[0]->TRAM_CantidadATraspasar,$articulo->ART_CantidadAMano,$operacion);
                            //ACTUALIZA CANTIDAD A MANO
                            ArticulosController::actualizaCantidadAManoPorId($articulo->ART_ArticuloId,$TraspasoMovto[0]->TRAM_CantidadATraspasar);

                        }

                    }

                }

                //REGRESA LA SOLICITUD ELIMINADA PARA QUE EL PUGLIN HAGA SU CHAMBA DE ELIMINAR
                $ajaxData = array();

                $ajaxData['data'] = $TRS_TraspasoSolicitud->toArray();
                $ajaxData['options'] = array();

                return (json_encode($ajaxData));

            }

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
        $resultados=TraspasosSolicitudes::find($id);

        $resultados->TRS_FechaSolicitud = substr($resultados->TRS_FechaSolicitud, 0, 10);

        $CodigoSolicitud = $resultados->TRS_CodigoSolicitud;

        //$almacenes=array(''=>'Selecciona Alamacen') + Almacenes::orderby('ALM_Nombre','ASC')->lists('ALM_Nombre','ALM_AlmacenId');

        $almacenes =  Localidad::select('LOC_LocalidadId','ALM_Nombre')
                ->join('Almacenes', 'ALM_AlmacenId', '=', 'LOC_ALM_AlmacenId')
                //->where('LOC_LocalidadGeneral','=',1)
                ->where('LOC_LocalidadId','=',DataBaseSession::getLocalidadGeneralId())
                ->orderby('ALM_Nombre','ASC')
                ->lists('ALM_Nombre','LOC_LocalidadId');

        $almacenesLocalidades = array(''=>'Selecciones Almacen/Localidad') + Localidad::select('LOC_LocalidadId', \DB::raw("ALM_Nombre + (CASE WHEN LOC_LocalidadGeneral = 0 THEN ' - ' + LOC_Nombre ELSE '' END) AS FULL_NAME"))
                ->join('Almacenes','ALM_AlmacenId','=','LOC_ALM_AlmacenId')
                ->lists('FULL_NAME','LOC_LocalidadId');
        $articulos=array(''=>'Selecciona Código del Artículo') + Articulos::orderby('ART_CodigoArticulo','ASC')->lists('ART_CodigoArticulo','ART_ArticuloId');

        return view('Inventario.RecibosTraspasos.editar', compact('id','CodigoSolicitud','resultados', 'almacenesLocalidades', 'articulos', 'almacenes'));
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

            $jsonTraspasos = Request::input('Traspasos');
            if(count($jsonTraspasos) > 0){

                $status = \DB::select(\DB::raw("
                                                select TRS_CMM_EstatusSolicitudId from TraspasosSolicitudes
                                                inner join TraspasosSolicitudesDetalle on TRSD_TRS_TraspasoSolicitudId = TRS_TraspasoSolicitudId
                                                where TRSD_DetalleId = '".$jsonTraspasos[0]['SolicitudDetalleId']."'
                                                "
                ))[0]->TRS_CMM_EstatusSolicitudId;

                if($status != ControlesMaestrosMultiples::$CMM_EstatusSolicitudTraspaso_Recibido
                    && $status != ControlesMaestrosMultiples::$CMM_EstatusSolicitudTraspaso_RecibidoParcial
                    && $status != ControlesMaestrosMultiples::$CMM_EstatusSolicitudTraspaso_Recibo_Parcial){

                    //$fecharecibo = Request::input('fecha');
                    $comentarios = Request::input('comentarios');
                    RecibosTraspasosController::guardaProcesoTraspaso($jsonTraspasos,$comentarios,$id);

                }
                else{

                    echo json_encode(
                        [
                            "Respuesta" => [
                                [
                                    'InformacionError' => [],
                                    'Estatus' => 'Procesado',
                                    'Mensaje'=> "La solicitud de traspaso ya fue recibida."
                                ]
                            ]
                        ]
                    );

                }

            }
            //$form = Request::input('formulario');



            //$mensaje = 'El Recibo de Traspaso se registró con éxito.';

            \DB::commit();

            echo json_encode(
                [
                    "Respuesta" => [
                        [
                            'InformacionError' => [],
                            'Estatus' => 'Procesado',
                            'Mensaje'=> "La transacción fue realizada exitosamente."
                        ]
                    ]
                ]
            );

            //return ['Status' => 'Valido', 'Mensaje' => $mensaje];

        }
        catch (\Exception $e) {

            \DB::rollback();

            echo json_encode(
                [
                    "Respuesta" => [
                        [
                            'InformacionError' => $e->getMessage(),
                            'Estatus' => 'Error',
                            'Mensaje'=> "Ocurrió un error en la transacción, no fue posible realizar el traspaso."
                        ]
                    ]
                ]
            );

            //return ['Status' => 'Error', 'Mensaje' => 'No se registró el Recibo de Traspaso. Ocurrió un error al realizar el proceso. <br><br><br>Error: <br>'.$e->getMessage()];

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

    private function guardaProcesoTraspaso($arrayTraspasos,$comentarios,$SolicitudTraspasoId){

        try{

            $longitud = count($arrayTraspasos);

            for($i=0; $i<$longitud; $i++){

                if(!isset($arrayTraspasos[$i]['TraspasoReciboId']))
                    $arrayTraspasos[$i]['TraspasoReciboId']=EmbarquesController::getNuevoId();

                RecibosTraspasosController::registraTraspasoRecibo($arrayTraspasos[$i]['SolicitudDetalleId']
                    , $arrayTraspasos[$i]['TraspasoDetalleId']
                    , $arrayTraspasos[$i]['Lotes']
                    , $arrayTraspasos[$i]['Cantidad']
                    //, $fechaRecibo//$form[1]['value']//fecha recibo
                    , $comentarios//$form[7]['value']//comentarios
                    , $arrayTraspasos[$i]['TraspasoReciboId']
                );

                $datos = RecibosTraspasosController::getCodigoSolicitudArticulo($arrayTraspasos[$i]['SolicitudDetalleId']);

                $Articulo = ArticulosController::buscaPorId($datos->ART_ArticuloId);
                $Articulo->ART_CantidadAMano = $Articulo->ART_CantidadAMano + $arrayTraspasos[$i]['Cantidad'];
                $Articulo->ART_CantidadUltimoAjuste = $arrayTraspasos[$i]['Cantidad'];
                ArticulosController::actualizaCamposDeAjustePorId($Articulo);

            }

            $this->actualizaEstadoSolicitud($SolicitudTraspasoId);

        }
        catch(\Exception $e){

            throw $e;

        }

    }

    private function registraTraspasoRecibo($SolicitudDetalleId,$TraspasoDetalleId, $arrayLotes, $cantidadTraspasar,$comentarios,$TraspasoReciboId){

        try{

            $traspasoRecibo = new TraspasosRecibos();
            $traspasoRecibo->TRAR_TraspasoReciboId = $TraspasoReciboId;//EmbarquesController::getNuevoId();
            $traspasoRecibo->TRAR_TRAD_TraspasoDetalleId = $TraspasoDetalleId;
            $traspasoRecibo->TRAR_CantidadRecibo = $cantidadTraspasar;
            // $traspasoRecibo->TRAR_FechaRecibo = Carbon::now('America/Mexico_City');
            $traspasoRecibo->TRAR_Comentarios = $comentarios;
            $traspasoRecibo->TRAR_TRAM_TraspasoMovtoId = RecibosTraspasosController::guardaTraspasoMovto($cantidadTraspasar,
                $traspasoRecibo->TRAR_TraspasoReciboId,
                $arrayLotes,
                $SolicitudDetalleId);
            $traspasoRecibo->save();

        }
        catch(\Exception $e){

            throw $e;

        }

    }

    private function guardaTraspasoMovto($cantidadTraspasar, $TraspasoReciboId, $arrayLotes, $SolicitudDetalleId){

        try{

            $datos = RecibosTraspasosController::getCodigoSolicitudArticulo($SolicitudDetalleId);

            $traspasoMovto = new TraspasoMovto();
            $traspasoMovto->TRAM_ART_ArticuloId = $datos->ART_ArticuloId;
            $traspasoMovto->TRAM_CantidadATraspasar = $cantidadTraspasar;
            $traspasoMovto->TRAM_CMM_TipoTransferenciaId = ControlesMaestrosMultiples::$RECIBO_TRASPASO;
            $traspasoMovto->TRAM_Razon = 'Recibo de Traspaso de la Solicitud: '.$datos->TRS_CodigoSolicitud;
            $traspasoMovto->TRAM_Referencia =
                "Traspaso de Articulo : " . $datos->ART_CodigoArticulo . ", "
                . "Cantidad: " . $cantidadTraspasar;
            $traspasoMovto->TRAM_ReferenciaMovtoId = $TraspasoReciboId;

            $cantidadLotes = count($arrayLotes);
//dd($cantidadLotes);
            $arrayDetallesMovimiento = array();

            for ($i=0; $i<$cantidadLotes; $i++) {

                $dmi = new DetallesMovimientoInventario();

                $dmi->setCantidadTransferir($arrayLotes[$i]['Cantidad']);
                $dmi->setIdAlmacen(Localidades::where('LOC_LocalidadId', '=', $arrayLotes[$i]['LocalidadId'])->get()[0]->LOC_ALM_AlmacenId);

                //if (EmbarquesController::tieneSeguimientoLocalidades($afectaRegistros->ARTICULO_ID)) {
                $localidad = new Localidades();
                $localidad->COL_LOCALIDAD_ID = $arrayLotes[$i]['LocalidadId'];
                $dmi->setLocalidad($localidad);
                //}

                //if (EmbarquesController::tieneSeguimientoLotes($afectaRegistros->ARTICULO_ID)) {
                $lote = new Lotes();
                $lote->COL_LOTE_ID = LotesController::getIdLotePorCodigo($arrayLotes[$i]['Lote']);
                $dmi->setLote($lote);
                //}

                array_push($arrayDetallesMovimiento, $dmi);

            }

            return ProcesadorMovimientoInventarios::registraMovimientoEnInventario($traspasoMovto, $arrayDetallesMovimiento, null);

        }
        catch(\Exception $e){

            throw $e;

        }

    }

    private function getCodigoSolicitudArticulo($SolicitudDetalleId){

        $datos = TraspasosSolicitudes::select('TRS_CodigoSolicitud', 'TRS_TraspasoSolicitudId', 'ART_ArticuloId', 'ART_CodigoArticulo')
            ->join('TraspasosSolicitudesDetalle', 'TRS_TraspasoSolicitudId', '=', 'TRSD_TRS_TraspasoSolicitudId')
            ->join('Articulos', 'ART_ArticuloId', '=', 'TRSD_ART_ArticuloId')
            ->where('TRSD_DetalleId', '=', $SolicitudDetalleId)
            ->get(1)[0];

        return $datos;

    }

    private function actualizaEstadoSolicitud($SolicitudTraspasoId){

        try{

            //$idSolicitud = $this->getCodigoSolicitudArticulo($TraspasoDetalleId)->TRS_TraspasoSolicitudId;

            $resultadoConsulta =
                \DB::select(\DB::raw("
                SELECT TRSD_Cantidad - SUM(ISNULL(TRAR_CantidadRecibo,0.0)) as TRASPASO
                FROM
                TraspasosSolicitudes
                INNER JOIN TraspasosSolicitudesDetalle on TRSD_TRS_TraspasoSolicitudId = TRS_TraspasoSolicitudId
                LEFT JOIN TraspasosDetalle on TRAD_TRSD_DetalleId = TRSD_DetalleId
                LEFT JOIN TraspasosRecibos on TRAR_TRAD_TraspasoDetalleId = TRAD_TraspasoDetalleId
                WHERE TRS_TraspasoSolicitudId = '".$SolicitudTraspasoId."'
                GROUP BY TRSD_DetalleId, TRSD_Cantidad
                "
                ));

            $longitud = count($resultadoConsulta);

            $status = ControlesMaestrosMultiples::$CMM_EstatusSolicitudTraspaso_Recibido;

            for($i=0; $i<$longitud; $i++){

                if($resultadoConsulta[$i]->TRASPASO > 0){

                    $status = ControlesMaestrosMultiples::$CMM_EstatusSolicitudTraspaso_RecibidoParcial;
                    break;

                }

            }

            $solicitud = TraspasosSolicitudes::find($SolicitudTraspasoId);

            $solicitud->TRS_CMM_EstatusSolicitudId = $status;
            $solicitud->save();

        }
        catch(\Exception $e){

            throw $e;

        }

    }

    public function buscaSupervisorPorLocalidad($LOC_LocalidadId){


        $resultadoConsulta = \DB::select(
                \DB::raw("select EMP_Nombre + ' ' + EMP_PrimerApellido + ' ' + EMP_SegundoApellido as NombreCompleto, USU_Nombre, USU_Contrasenia from Localidades
                        inner join TransportesUnidades on tun_loc_localidadID = LOC_LocalidadId
                        inner join Rutas on RUT_TUN_TransporteUnidadId = TUN_TransporteUnidadId
                        inner join Empleados on emp_empleadoid = RUT_EMP_VendedorId
                        inner join Usuarios on USU_EMP_EmpleadoId = emp_empleadoid
                        where LOC_LocalidadId = '".$LOC_LocalidadId."'")
            );

        if(count($resultadoConsulta) <= 0) {

            $resultadoConsulta = \DB::select(
                \DB::raw("select EMP_Nombre + ' ' + EMP_PrimerApellido + ' ' + EMP_SegundoApellido as NombreCompleto, USU_Nombre, USU_Contrasenia
                    from Localidades
                    INNER JOIN Almacenes ON ALM_AlmacenId = LOC_ALM_AlmacenId
                        inner join Empleados on emp_empleadoid = ALM_EMP_ResponsableId
                        INNER JOIN Usuarios ON USU_EMP_EmpleadoId = EMP_EmpleadoId
                        WHERE LOC_LocalidadId ='".$LOC_LocalidadId."'")
            );
        }

        return Response::json($resultadoConsulta);

    }

    public function isCedi($idSolicitud){

        $resultadoConsulta = \DB::select(
            \DB::raw("select TRS_CodigoSolicitud from TraspasosSolicitudes
                    inner join Localidades on LOC_LocalidadId = TRS_LOC_LocalidadOrigenId

                    where TRS_TraspasoSolicitudId = '".$idSolicitud."' and
                    LOC_LocalidadId in (
                    select LOC_LocalidadId from Almacenes
                    inner join Localidades on LOC_ALM_AlmacenId = ALM_AlmacenId
                    where ALM_AlmacenPredeterminado = 1
)")
        );



        return count($resultadoConsulta) > 0 ? "true" : "false";

    }

    public function getLocalidadGeneral(){

        $dao = new DAOGeneralController();
        $idLocalidaGral = $dao->getArrayAsociativo("SELECT TOP 1 LOC_LocalidadId FROM Localidades WHERE LOC_LocalidadId = '".DataBaseSession::getLocalidadGeneralId()."' AND LOC_Eliminado = 0");

        return $idLocalidaGral[0]['LOC_LocalidadId'];


    }

}
