<?php namespace App\Http\Controllers\Inventario;

use Carbon\Carbon;
use Illuminate\Support\Facades\Facade;
use App\Http\Controllers\Embarques\EmbarquesController;
use App\Http\Controllers\Inventario\Articulos\ArticulosController;
use App\Http\Controllers\Inventario\Localidades\LocalidadesArticuloController;
use App\Http\Controllers\Sistema\AutonumericoController;
use App\Http\Controllers\RecursosHumanos\Departamentos\DepartamentosController;
use App\Http\Controllers\Sistema\DataBaseSession;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Request as NewRequest;
use Illuminate\Http\Request;
use App\Mapeos\Controles\ControlesMaestrosMultiples;
use App\Models\Articulos;
use App\Models\Inventario\Articulos\Almacen;
use App\Models\Inventario\Articulos\Articulo;
use App\Models\Inventario\InventarioFisico\DetallesMovimientoInventario;
use App\Models\Inventario\InventarioFisico\ProcesadorMovimientoInventarios;
use App\Models\Inventario\InventarioFisico\TraspasoMovto;
use App\Models\Inventario\Localidad;
use App\Models\Inventario\TraspasosLocalidades;
use App\Models\Localidades;
use App\Models\Lotes;
use App\Models\Traspasos;
use App\Models\TraspasosDetalle;
use App\Models\TraspasosSolicitudes;
use App\Models\TraspasosSolicitudesDetalle;
use App\Models\TraspasosSolicitudesTiempos;
use Response;

class TraspasosController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        /*$encabezados =array(
            'Id',
            'Codigo',
            'Status'
        );

        $contenidos=array(
            'TRS_TraspasoSolicitudId',
            'TRS_CodigoSolicitud',
            'CMM_Valor'
        );

        $results=\DB::table('TraspasosSolicitudes')
            ->join('ControlesMaestrosMultiples','CMM_ControlId','=','TRS_CMM_EstatusSolicitudId')
            ->where('TRS_Eliminado','=',0)
            ->select($contenidos)
            ->get();
        */
        $almacenes=array(''=>'Almacen') + Almacen::whereRaw(" ALM_Eliminado = 0".(DataBaseSession::isPermisoCorporativo() ? "" : " AND ALM_AlmacenId IN (".DataBaseSession::getAlmacenesPorCediId().")"))
                ->orderby('ALM_Nombre','ASC')->lists('ALM_Nombre','ALM_AlmacenId')->all();

        $articulos=array(''=>'Productos') + Articulos::orderby('ART_CodigoArticulo','ASC')->lists('ART_Nombre','ART_ArticuloId')->all();

        //date_default_timezone_set('America/Mexico_City');
        $fecha=date('d/m/Y');

        return view('Inventario.Traspasos.create', compact('articulos', 'almacenes', 'fecha'));
    }

    public function buscaTraspasos(){

        $FechaInicio = NewRequest::input('fechaDesde');
        $FechaFinal = NewRequest::input('fechaHasta');

        $consultaTraspasos = \DB::select(
            \DB::raw(
                "SELECT
                    TRA_CodigoTraspaso
                    ,CAST(TRA_FechaTraspaso AS DATE) AS TRA_FechaTraspaso
                FROM Traspasos
                WHERE CAST(TRA_FechaTraspaso AS DATE) BETWEEN '".$FechaInicio."' AND '".$FechaFinal."'"
            )
        );

        $ajaxData = array();
        $ajaxData['data'] = $consultaTraspasos;
        $ajaxData['options'] = array();

        return (json_encode($ajaxData));

    }

    public function consultardetallesolicitudtraspaso($TRSD_TRS_TraspasoSolicitudId,$LOCA_LOC_LocalidadId){

        $consultaDetallesSolicitudTraspaso = \DB::select(
            \DB::raw(
                "SELECT TRSD_NumeroPartida, ART_CodigoArticulo, ART_Nombre, CMUM_Nombre, CAST(ISNULL(LOCA_Cantidad,0)AS INT) AS Existencia, TRSD_Cantidad, TRSD_DetalleId, ISNULL(x.cantidadTraspasada,0.0) as CantidadTraspasada, CMM_Valor
                FROM TraspasosSolicitudesDetalle
                INNER JOIN Articulos ON ART_ArticuloId=TRSD_ART_ArticuloId
                INNER JOIN ControlesMaestrosUM ON CMUM_UnidadMedidaId = TRSD_CMUM_UnidadMedidaId
                INNER JOIN TraspasosSolicitudes ON TRS_TraspasoSolicitudId = TRSD_TRS_TraspasoSolicitudId
                INNER JOIN ControlesMaestrosMultiples ON CMM_ControlId = TRS_CMM_EstatusSolicitudId
                LEFT JOIN Localidades ON LOC_LocalidadId = '".$LOCA_LOC_LocalidadId."'
                LEFT JOIN (
                            SELECT TRAD_TRSD_DetalleId, SUM(ISNULL(TRAD_CantidadATraspasar,0.0))as CantidadTraspasada FROM TraspasosDetalle
                            WHERE TRAD_Eliminado = 0
						    GROUP BY TRAD_TRSD_DetalleId
                        )AS x ON x.TRAD_TRSD_DetalleId = TRSD_DetalleId
                LEFT JOIN LocalidadesArticulo ON ART_ArticuloId = LOCA_ART_ArticuloId AND LOCA_LOC_LocalidadId = LOC_LocalidadId
                WHERE TRSD_TRS_TraspasoSolicitudId = '".$TRSD_TRS_TraspasoSolicitudId."'
                ORDER BY TRSD_NumeroPartida ASC"
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
        return view('Inventario.CancelarTraspaso.cancelartraspaso');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {

        /*if (\Illuminate\Support\Facades\Request::isMethod('get')) {

            $results=\DB::table('TraspasosSolicitudes')
                ->join('ControlesMaestrosMultiples','CMM_ControlId','=','TRS_CMM_EstatusSolicitudId')
                ->where('TRS_Eliminado','=',0)
                ->whereRaw("TRS_CMM_EstatusSolicitudId = '".ControlesMaestrosMultiples::$CMM_EstatusSolicitudTraspaso_Traspasado."'
                     OR TRS_CMM_EstatusSolicitudId = '".ControlesMaestrosMultiples::$CMM_EstatusSolicitudTraspaso_TraspasoParcial."'")
                ->select('TRS_TraspasoSolicitudId as DT_RowId','TRS_CodigoSolicitud')
                ->get();

            $ajaxData = array();
            $ajaxData['data'] = $results;
            $ajaxData['options'] = array();

            return (json_encode($ajaxData));

        }
        elseif (\Illuminate\Support\Facades\Request::isMethod('post')) {

            if(\Illuminate\Support\Facades\Request::input('action') == 'remove'){

                $operacion = "sumaC";
                //ACTUALIZA EL ESTADO DE LA SOLICITUD
                $TRS_TraspasoSolicitud = TraspasosSolicitudes::find(\Illuminate\Support\Facades\Request::input('id')[0]);
                $TRS_TraspasoSolicitud->TRS_CMM_EstatusSolicitudId = ControlesMaestrosMultiples::$CMM_EstatusSolicitudTraspaso_Solicitado;
                $LocalidadId = $TRS_TraspasoSolicitud->TRS_LOC_LocalidadDestinoId;
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

                    $TraspasosDetalle = TraspasosDetalle::select('TRAD_TRAM_TraspasoMovtoId','TRAD_TRSD_DetalleId','TRAD_CantidadATraspasar','TRAD_Eliminado')
                        ->where('TRAD_TRSD_DetalleId','=',$TRSD_Detalle[$x]->TRSD_DetalleId)
                        ->get();

                    //ACTUALIZA EL CAMPO DE ELIMINADO
                    $TraspasosDetalle[0]->TRAD_Eliminado = 1;
                    \DB::table('TraspasosDetalle')->where('TRAD_TRSD_DetalleId', '=', $TraspasosDetalle[0]->TRAD_TRSD_DetalleId)
                        ->update(
                            array(
                                'TRAD_Eliminado' => $TraspasosDetalle[0]->TRAD_Eliminado
                            )
                        );

                    $Cuenta_TraspasosDetalle = count($TraspasosDetalle);

                    if($Cuenta_TraspasosDetalle > 0){

                        $TraspasoMovto = TraspasoMovto::where('TRAM_TraspasoMovtoId','=',$TraspasosDetalle[0]->TRAD_TRAM_TraspasoMovtoId)->get();
                        //busca articulo
                        $articulo = ArticulosController::buscaPorId($TraspasoMovto[0]->TRAM_ART_ArticuloId);
                        //inserta y obtiene el ID del traspaso Movto
                        $idTrapasosMovtos = TraspasosController::obtenerTransferenciaMvoto($articulo,$TraspasoMovto,$TraspasosDetalle[0]->TRAD_TRSD_DetalleId,$operacion);
                        //OBTIENE CANTIDAD ANTERIOR Y LOCALIDAD
                        $CantidadAnteriorYLocalidadId = TraspasosController::restarPiezasEnLocalidad($TraspasoMovto[0]->TRAM_CantidadATraspasar,$articulo->ART_ArticuloId,$LocalidadId,$operacion);
                        //INSERTA EN TRASPASOS LOCALIDADES
                        TraspasosController::guardaTransferencia($idTrapasosMovtos,$CantidadAnteriorYLocalidadId[0],$CantidadAnteriorYLocalidadId[1],$TraspasoMovto[0]->TRAM_CantidadATraspasar,$articulo->ART_CantidadAMano,$operacion);
                        //ACTUALIZA CANTIDAD A MANO
                        ArticulosController::actualizaCantidadAManoPorId($articulo->ART_ArticuloId,$TraspasoMovto[0]->TRAM_CantidadATraspasar * -1);

                    }

                }

                //REGRESA LA SOLICITUD ELIMINADA PARA QUE EL PUGLIN HAGA SU CHAMBA DE ELIMINAR
                $ajaxData = array();

                $ajaxData['data'] = $TRS_TraspasoSolicitud->toArray();
                $ajaxData['options'] = array();

                return (json_encode($ajaxData));

            }

        }*/
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

        $almacenes = array(''=>'Selecciona Alamacen') + Localidad::select('LOC_LocalidadId','ALM_Nombre')
                ->join('Almacenes', 'ALM_AlmacenId', '=', 'LOC_ALM_AlmacenId')
                //->where('LOC_LocalidadGeneral','=',1)
                ->where('LOC_LocalidadId','=',DataBaseSession::getLocalidadGeneralId())
                ->orderby('ALM_Nombre','ASC')
                ->lists('ALM_Nombre','LOC_LocalidadId')->all();

        $almacenesLocalidades = array(''=>'Selecciones Almacen/Localidad') + Localidad::select('LOC_LocalidadId', \DB::raw("ALM_Nombre + (CASE WHEN LOC_LocalidadGeneral = 0 THEN ' - ' + LOC_Nombre ELSE '' END) AS FULL_NAME"))
                ->join('Almacenes','ALM_AlmacenId','=','LOC_ALM_AlmacenId')
                ->whereRaw(" ALM_AlmacenId IN (".DataBaseSession::getAlmacenesPorCediId().")")
                ->lists('FULL_NAME','LOC_LocalidadId')->all();
        $articulos=array(''=>'Selecciona Código del Artículo') + Articulos::orderby('ART_CodigoArticulo','ASC')->lists('ART_CodigoArticulo','ART_ArticuloId')->all();

        return view('Inventario.Traspasos.editar', compact('id','CodigoSolicitud','resultados', 'almacenesLocalidades', 'articulos', 'almacenes'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        /*$sub = \DB::table('Traspasos')->insert(
            array(
                'TRA_FechaTraspaso' => $_POST['TRA_FechaTraspaso'],
                'TRA_TRS_TraspasoSolicitudId' => $id,
                'TRA_Comentarios' => $_POST['TRS_Comentarios']
            )
        );*/

        //$UltimoRegistrado = Traspasos::orderby('TRA_FechaCreacion', 'DESC')->first()->TRA_TraspasoId;

        $operacion = "resta";
        for($val=1; $val<=$_POST['CCON_contador']; $val++){
            if($_POST['TRSD_Cantidad'.$val] != ""){
                \DB::table('TraspasosDetalle')->insert(
                    array(
                        //'TRAD_TRA_TraspasoId' => $UltimoRegistrado,
                        'TRAD_TRSD_DetalleId' => $_POST['TRAD_TRSD_DetalleId'.$val],
                        'TRAD_CantidadATraspasar' => $_POST['TRAD_CantidadATraspasar'.$val],
                        'TRAD_FechaTraspaso' => $_POST['TRA_FechaTraspaso'],
                        'TRAD_FechaLote' => $_POST['TRAD_FechaLote'.$val]
                    )
                );

                //busca articulo
                $articulo = ArticulosController::BuscarCodigoArt2($_POST['ART_CodigoArticulo'.$val]);
                //inserta y obtiene el ID del traspaso Movto
                $idTrapasosMovtos = TraspasosController::obtenerTransferenciaMvoto($articulo,$val,$_POST['CCON_contador3'],$operacion);

                //obtiene el ultimo traspasoDetalle registrado
                $UltimoTraspasoDetalle = TraspasosDetalle::orderby('TRAD_FechaRegistro', 'DESC')->first()->TRAD_TraspasoDetalleId;
                //actualiza el id de traspasoMovto
                \DB::table('TraspasosDetalle')->where('TRAD_TraspasoDetalleId', '=', $UltimoTraspasoDetalle)
                    ->update(
                        array(
                            'TRAD_TRAM_TraspasoMovtoId' => $idTrapasosMovtos
                        )
                    );

                //OBTIENE CANTIDAD ANTERIOR Y LOCALIDAD
                $CantidadAnteriorYLocalidadId = TraspasosController::restarPiezasEnLocalidad($_POST['TRAD_CantidadATraspasar'.$val],$articulo[0]->ART_ArticuloId,null,$operacion);
                //INSERTA EN TRASPASOS LOCALIDADES
                TraspasosController::guardaTransferencia($idTrapasosMovtos,$CantidadAnteriorYLocalidadId[0],$CantidadAnteriorYLocalidadId[1],$_POST['TRAD_CantidadATraspasar'.$val],$articulo[0]->ART_CantidadAMano,$operacion);
                //ACTUALIZA CANTIDAD A MANO
                ArticulosController::actualizaCantidadAManoPorId($articulo[0]->ART_ArticuloId,$_POST['TRAD_CantidadATraspasar'.$val] * -1);
            }
        }

        if($_POST['CCON_contador2'] == 'true'){
            $status = ControlesMaestrosMultiples::$CMM_EstatusSolicitudTraspaso_Traspasado;
        }else{
            $status = ControlesMaestrosMultiples::$CMM_EstatusSolicitudTraspaso_TraspasoParcial;
        }

        \DB::table('TraspasosSolicitudes')->where('TRS_TraspasoSolicitudId', '=', $id)
            ->update(
                array(
                    'TRS_CMM_EstatusSolicitudId' => $status
                )
            );

        return json_encode(array());
    }

    public static function obtenerTransferenciaMvoto($articulo,$val,$CodigoSolicitud,$operacion){
        //date_default_timezone_set('America/Mexico_City');
        $hoy=date('d/m/Y H:i:s');
        $TraspasosMovtos = new TraspasoMovto();
        if($operacion == "suma"){
            $TraspasosMovtos->TRAM_CantidadATraspasar = $_POST['CantidadRecibida'.$val];
            $TraspasosMovtos->TRAM_CantidadAManoConTraspaso = $articulo[0]->ART_CantidadAMano + $_POST['CantidadRecibida'.$val];
            $TraspasosMovtos->TRAM_Razon = "Cancelacion de Traspaso de la Solicitud ".$CodigoSolicitud;
            $TraspasosMovtos->TRAM_CMM_TipoTransferenciaId=ControlesMaestrosMultiples::$RECIBO_TRASPASO;
            $TraspasosMovtos->TRAM_ReferenciaMovtoId = $_POST['TRAD_TraspasoDetalleId'.$val];
            $TraspasosMovtos->TRAM_Referencia = $CodigoSolicitud;
            $TraspasosMovtos->TRAM_UnidadMedidadArt = $_POST['CMUM_Nombre'.$val];
            $TraspasosMovtos->TRAM_ART_ArticuloId = $articulo[0]->ART_ArticuloId;
            $TraspasosMovtos->TRAM_CantidadAMano = $articulo[0]->ART_CantidadAMano;
        }elseif($operacion == "sumaC"){
            $TraspasosMovtos->TRAM_CantidadATraspasar = $val[0]->TRAM_CantidadATraspasar * -1;
            $TraspasosMovtos->TRAM_CantidadAManoConTraspaso = $articulo->ART_CantidadAMano + ($val[0]->TRAM_CantidadATraspasar * -1);
            $TraspasosMovtos->TRAM_Razon = "Recibo de la Solicitud ".$val[0]->TRAM_Referencia;
            $TraspasosMovtos->TRAM_CMM_TipoTransferenciaId=ControlesMaestrosMultiples::$CANCELA_TRASPASO;
            $TraspasosMovtos->TRAM_ReferenciaMovtoId = $CodigoSolicitud;//en este caso es el ID de TRAD_TraspasoDetalleId
            $TraspasosMovtos->TRAM_Referencia = $val[0]->TRAM_Referencia;
            $TraspasosMovtos->TRAM_UnidadMedidadArt = $val[0]->TRAM_UnidadMedidadArt;
            $TraspasosMovtos->TRAM_ART_ArticuloId = $articulo->ART_ArticuloId;
            $TraspasosMovtos->TRAM_CantidadAMano = $articulo->ART_CantidadAMano;
        }elseif($operacion == "resta"){
            $TraspasosMovtos->TRAM_NumeroPartida = $_POST['TRSD_NumeroPartida'.$val];
            $TraspasosMovtos->TRAM_CantidadATraspasar = $_POST['TRAD_CantidadATraspasar'.$val] * -1;
            $TraspasosMovtos->TRAM_CantidadAManoConTraspaso = $articulo[0]->ART_CantidadAMano - $_POST['TRAD_CantidadATraspasar'.$val];
            $TraspasosMovtos->TRAM_Razon = "Traspaso de la Solicitud ".$CodigoSolicitud;
            $TraspasosMovtos->TRAM_CMM_TipoTransferenciaId=ControlesMaestrosMultiples::$TRASPASO;
            $TraspasosMovtos->TRAM_ReferenciaMovtoId = $_POST['TRAD_TRSD_DetalleId'.$val];
            $TraspasosMovtos->TRAM_Referencia = $CodigoSolicitud;
            $TraspasosMovtos->TRAM_UnidadMedidadArt = $_POST['CMUM_Nombre'.$val];
            $TraspasosMovtos->TRAM_ART_ArticuloId = $articulo[0]->ART_ArticuloId;
            $TraspasosMovtos->TRAM_CantidadAMano = $articulo[0]->ART_CantidadAMano;
        }elseif($operacion == "restaC"){
            $TraspasosMovtos->TRAM_CantidadATraspasar = $val[0]->TRAM_CantidadATraspasar * -1;
            $TraspasosMovtos->TRAM_CantidadAManoConTraspaso = $articulo->ART_CantidadAMano - ($val[0]->TRAM_CantidadATraspasar * -1);
            $TraspasosMovtos->TRAM_Razon = "Cancelacion de Recibo de la Solicitud ".$val[0]->TRAM_Referencia;
            $TraspasosMovtos->TRAM_CMM_TipoTransferenciaId=ControlesMaestrosMultiples::$CANCELA_RECIBO_TRASPASO;
            $TraspasosMovtos->TRAM_ReferenciaMovtoId = $CodigoSolicitud;//en este caso es el ID de TRAD_TraspasoDetalleId
            $TraspasosMovtos->TRAM_Referencia = $val[0]->TRAM_Referencia;
            $TraspasosMovtos->TRAM_UnidadMedidadArt = $val[0]->TRAM_UnidadMedidadArt;
            $TraspasosMovtos->TRAM_ART_ArticuloId = $articulo->ART_ArticuloId;
            $TraspasosMovtos->TRAM_CantidadAMano = $articulo->ART_CantidadAMano;
        }
        $TraspasosMovtos->TRAM_FechaTraspaso = $hoy;
        $TraspasosMovtos->TRAM_EstatusContable = true;

        $TraspasosMovtos->save();
        $TRAM_TraspasoMovtoId = TraspasoMovto::orderby('TRAM_FechaTraspaso', 'DESC')->first()->TRAM_TraspasoMovtoId;

        return $TRAM_TraspasoMovtoId;
    }

    public static function restarPiezasEnLocalidad($cantidadTransfir,$ART_ArticuloId,$LocalidadId,$operacion){
        if($operacion == "suma"){
            $localidadArticulo = LocalidadesArticuloController::buscaPorArticuloIdYLocalidadId($ART_ArticuloId,$_POST['TRS_LOC_LocalidadDestinoId']);
            $cantidadTotal = $localidadArticulo[0]->LOCA_Cantidad + $cantidadTransfir;
        }elseif($operacion == "sumaC"){
            $localidadArticulo = LocalidadesArticuloController::buscaPorArticuloIdYLocalidadId($ART_ArticuloId,$LocalidadId);
            $cantidadTotal = $localidadArticulo[0]->LOCA_Cantidad + ($cantidadTransfir * -1);
        }elseif($operacion == "resta"){
            $localidadArticulo = LocalidadesArticuloController::buscaPorArticuloIdYLocalidadId($ART_ArticuloId,$_POST['TRS_LOC_LocalidadDestinoId']);
            $cantidadTotal = $localidadArticulo[0]->LOCA_Cantidad - $cantidadTransfir;
        }elseif($operacion == "restaC"){
            $localidadArticulo = LocalidadesArticuloController::buscaPorArticuloIdYLocalidadId($ART_ArticuloId,$LocalidadId);
            $cantidadTotal = $localidadArticulo[0]->LOCA_Cantidad - ($cantidadTransfir * -1);
        }
        $cantidadAnteriorEnLocalidadArticulo = $localidadArticulo[0]->LOCA_Cantidad;
        LocalidadesArticuloController::actualizaPorId($localidadArticulo,$cantidadTotal);

        return array($cantidadAnteriorEnLocalidadArticulo,$localidadArticulo[0]->LOCA_LocalidadArticuloId);
    }

    public static function guardaTransferencia($idTrapasosMovtos,$cantidadAnteriorEnLocalidadArticulo,$LocalidadArticuloId,$CantidadATraspasar,$CantidadAMano,$operacion){
        //date_default_timezone_set('America/Mexico_City');
        $hoy=date('d/m/Y H:i:s');
        //Guardar la transferencia negativa a nivel que se afecta en las localidades
        $TraspasosLocalidades = new TraspasosLocalidades();
        $TraspasosLocalidades->TRLOC_TRAM_TraspasoMovtoId = $idTrapasosMovtos;
        $TraspasosLocalidades->TRLOC_LOCA_LocalidadArticuloId = $LocalidadArticuloId;
        $TraspasosLocalidades->TRLOC_FechaTransferencia = $hoy;
        if($operacion == "suma"){
            $TraspasosLocalidades->TRLOC_CantidadTransferida = $CantidadATraspasar;
        }elseif($operacion == "sumaC"){
            $TraspasosLocalidades->TRLOC_CantidadTransferida = $CantidadATraspasar * -1;
        }elseif($operacion == "resta"){
            $TraspasosLocalidades->TRLOC_CantidadTransferida = $CantidadATraspasar * -1;
        }elseif($operacion == "restaC"){
            $TraspasosLocalidades->TRLOC_CantidadTransferida = $CantidadATraspasar;
        }
        $TraspasosLocalidades->TRLOC_CantidadAMano = $CantidadAMano;
        $TraspasosLocalidades->TRLOC_CantidadAnteriorLocalidad = $cantidadAnteriorEnLocalidadArticulo;

        $TraspasosLocalidades->save();
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

//======================================================================================================================
//==========================================CARLOS OMAR ANAYA BARAJAS===================================================
//======================================================================================================================

    public function Get_Localidades(){

        $AlmacenId = NewRequest::input('idAlmacen');

        $Localidades = \DB::select(\DB::raw("
            SELECT LOC_Nombre, LOC_LocalidadId
            FROM Localidades
            WHERE LOC_ALM_AlmacenId = '$AlmacenId'
            AND LOC_LocalidadGeneral = 0
            AND LOC_Eliminado = 0
            ORDER BY LOC_Nombre"));

        /*$ajaxData = array();
        $ajaxData['data'] = $Localidades;
        $ajaxData['options'] = array();*/

        return $Localidades;
    }

    public function Get_Productos(){

        $LocalidadId = NewRequest::input('idProducto');
        $AlmacenId = NewRequest::input('idAlmacen');

        if($LocalidadId != 'All')
            $where = "WHERE LOC_LocalidadId = '$LocalidadId' AND ART_Eliminado = 0";

        else
            $where = "WHERE ALM_AlmacenId = '$AlmacenId' AND ART_Eliminado = 0";

        $Articulos = \DB::select(\DB::raw("
            SELECT DISTINCT (ART_CodigoArticulo + ' - ' + ART_Nombre) AS ART_Nombre, ART_ArticuloId
            FROM LocalidadesArticulo
            INNER JOIN Articulos ON LOCA_ART_ArticuloId = ART_ArticuloId
            INNER JOIN Localidades ON LOCA_LOC_LocalidadId = LOC_LocalidadId
            INNER JOIN Almacenes ON LOC_ALM_AlmacenId = ALM_AlmacenId
            $where
            ORDER BY ART_Nombre"));

        /*$ajaxData = array();
        $ajaxData['data'] = $Articulos;
        $ajaxData['options'] = array();*/

        return $Articulos;
    }

    public function Get_DatosTabla(){

        $where = '';

        $IdLocalidad = NewRequest::input('idLocalidad');
        $IdProductos = NewRequest::input('idProductos');

        if($IdLocalidad != 'All'){

            $where = "WHERE LOTL_LOC_LocalidadId = '". $IdLocalidad ."'";
        }

        $CadProductos = '';
        $Cont = count($IdProductos);

        for($x=0; $x<$Cont; $x++){

            if($x <= ($Cont-2))
                $CadProductos .= "'".$IdProductos[$x]."'".', ';

            else
                $CadProductos .= "'".$IdProductos[$x]."'";
        }

        $where .= $where == '' ?
            " WHERE LOT_ART_ArticuloId IN(" . $CadProductos. ")"
            :
            " AND LOT_ART_ArticuloId IN(" . $CadProductos. ")";

        $DatosTabla = \DB::select(\DB::raw(
            "SELECT
                ART_ArticuloId, ART_CodigoArticulo, ART_Nombre, CMUM_Nombre, LOC_Nombre, LOT_LoteId, LOT_CodigoLote,
                CAST(LOTL_Cantidad AS Decimal(28,2)) AS LOTL_Cantidad

            FROM LotesLocalidades
            INNER JOIN Localidades ON LOTL_LOC_LocalidadId = LOC_LocalidadId
            INNER JOIN Almacenes ON LOC_ALM_AlmacenId = ALM_AlmacenId
            INNER JOIN Lotes ON LOTL_LOT_LoteId = LOT_LoteId AND LOT_Eliminado = 0
            INNER JOIN Articulos ON LOT_ART_ArticuloId = ART_ArticuloId
            INNER JOIN ControlesMaestrosUM ON ART_CMUM_UMInventarioId = CMUM_UnidadMedidaId
            $where AND CAST(LOTL_Cantidad AS Decimal(28,2)) > 0
            ORDER BY ART_Nombre"
        ));

        return $DatosTabla;
    }

    public function guardaTraspasos(){

        \DB::beginTransaction();

        try {

            //RECUPERAR VARIABLES
            $Arreglo = NewRequest::input('array');
            $objeto = json_decode($Arreglo);
            $AlmacenOrigenId = NewRequest::input('almOrigen');
            $LocalidadOrigenId = NewRequest::input('locOrigen');
            $AlmacenDestinoId = NewRequest::input('almDestino');
            $LocalidadDestinoId = NewRequest::input('locDestino');
            $Comentarios = NewRequest::input('comentarios');

            //GENERAR AUTONUMERICO
            $autonumerico_dao = new AutonumericoController();
            if($autonumerico_dao->isAutonumericoActivoPorReferenciaId('CM_INV_SiguienteTraspaso', null)){
                $Autonumerico_id = $this->EstablecerAutonumerico(DataBaseSession::getCediId(), null);
                $Codigo_Traspaso = $autonumerico_dao->getSiguienteAutonumericoPorId($Autonumerico_id);
            }

            //INERTAR EL TRASPASO
            \DB::table('Traspasos')->insert(
                array(
                    'TRA_CodigoTraspaso' => $Codigo_Traspaso,
                    'TRA_EMP_ModificadoPor' => DataBaseSession::getEmpleadoId(),
                    'TRA_CMM_TipoTraspasoId' => ControlesMaestrosMultiples::$CMM_TIPO_TRASPASO,
                    'TRA_Comentario' => $Comentarios
                )
            );

            //CONSULTAR ULTIMO TRASPASO INSERTADO
            $TraspasoId = Traspasos::orderby('TRA_FechaTraspaso', 'DESC')->first()->TRA_TraspasoId;

            //CUENTA OBJETO
            $cuentaObjeto = count($objeto);

            //SUMAR CANTIDADES POR ARTICULO PARA GUARDAR EL TRASPASOMOVTO
            if($cuentaObjeto > 0)
            {

                $ArrayArticulo = array(array());
                $Articulo_Anterior = "";
                $posicion = 0;
                for($x = 0; $x < $cuentaObjeto; $x ++)
                {

                    if($Articulo_Anterior != $objeto[$x][1])
                    {

                        $Articulo_Anterior = $objeto[$x][1];
                        if($x > 0)
                        {

                            $posicion++;

                        }
                        $ArrayArticulo[$posicion] = array('cantidad'=>$objeto[$x][0],'articuloId'=>$objeto[$x][1]);

                    }
                    else
                    {

                        $ArrayArticulo[$posicion]['cantidad'] = $ArrayArticulo[$posicion]['cantidad'] + $objeto[$x][0];

                    }

                }

                //CONTAR ARREGLO CON CANTIDADES
                $cuentaArrayArticulo = count($ArrayArticulo);

                //INICIA PROCESADOR DE INVENTARIO
                if($cuentaArrayArticulo > 0)
                {

                    for($x = 0; $x < $cuentaArrayArticulo; $x ++)
                    {

                        $CantidadPorTraspasar = 0;
                        $AlmacenId = "";
                        $LocalidadId = "";
                        $TipoOperacion = "";
                        $CantidadPositiva = $ArrayArticulo[$x]['cantidad'];
                        $CantidadNegativa = $ArrayArticulo[$x]['cantidad'] * -1;
                        $ArticuloId = $ArrayArticulo[$x]['articuloId'];
                        $UnidadMedida = ArticulosController::buscaNombreUMInventarioPorArticuloId($ArticuloId);
                        $Articulo = ArticulosController::buscaPorId($ArticuloId);
                        //REGISTRA 2 TRASPASOS MOVTOS POR ARTICULO (NEGATIVO Y POSITIVO)
                        for($y = 0; $y < 2; $y ++)
                        {

                            if($y == 0)
                            {

                                $CantidadPorTraspasar = $CantidadNegativa;
                                $AlmacenId = $AlmacenOrigenId;
                                $LocalidadId = $LocalidadOrigenId;
                                $TipoOperacion = "Negativo";

                            }
                            else
                            {

                                $CantidadPorTraspasar = $CantidadPositiva;
                                $AlmacenId = $AlmacenDestinoId;
                                $LocalidadId = $LocalidadDestinoId;
                                $TipoOperacion = "Positivo";

                            }
                            TraspasosController::procesaTransferirArticulo($TipoOperacion,$TraspasoId,$objeto,$AlmacenId,$LocalidadId,$Codigo_Traspaso,$CantidadPorTraspasar,$ArticuloId,$UnidadMedida[0]->CMUM_Nombre,$Articulo->ART_CodigoArticulo,$Articulo->ART_CantidadAMano);

                        }

                    }

                }

            }

            $mensaje = 'Se registró el Traspaso con éxito.';

            \DB::commit();

            return ['Status' => 'Valido', 'Mensaje' => $mensaje];

        }
        catch (\Exception $e) {

            \DB::rollback();

            return ['Status' => 'Error', 'Mensaje' => 'No se registró el Traspaso. Ocurrió un error al realizar el proceso. Error: '.$e->getMessage()];

        }

    }

    public function establecerAutonumerico($cediId, $empleadoId)
    {
        try {
            $autonumerico_dao = new AutonumericoController();
            $autonumericoFicha = $autonumerico_dao->getAutonumericoN("CM_INV_SiguienteTraspaso", $cediId);
            return $autonumericoFicha->AUT_AutonumericoId;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public function procesaTransferirArticulo($TipoOperacion,$TraspasoId,$objeto,$AlmacenId,$LocalidadId,$Codigo_Traspaso,$CantidadPorTraspasar,$idArt,$UnidadMedida,$CodigoArticulo,$CantidadAMano){

        try{

            $TraspasosMovtos=new TraspasoMovto();
            $TraspasosMovtos->TRAM_TRA_TraspasoId=$TraspasoId;
            $TraspasosMovtos->TRAM_ART_ArticuloId=$idArt;
            $TraspasosMovtos->TRAM_CantidadATraspasar=$CantidadPorTraspasar;
            $TraspasosMovtos->TRAM_Razon="Traspaso del codigo: ".$Codigo_Traspaso;
            $TraspasosMovtos->TRAM_Referencia="Traspaso de Articulo: ".$CodigoArticulo." Cantidad: ".$CantidadPorTraspasar;
            $TraspasosMovtos->TRAM_UnidadMedidadArt=$UnidadMedida;
            $TraspasosMovtos->TRAM_EstatusContable=false;
            $TraspasosMovtos->TRAM_CantidadAMano=$CantidadAMano;
            $TraspasosMovtos->TRAM_CantidadAManoConTraspaso=$CantidadAMano+$CantidadPorTraspasar;
            $TraspasosMovtos->TRAM_CMM_TipoTransferenciaId=ControlesMaestrosMultiples::$TRASPASO;

            $cuentaObjeto = count($objeto);
            $arrayDetallesMovimiento = array();

            for ($i = 0; $i < $cuentaObjeto; $i++)
            {

                if($objeto[$i][1] == $idArt)
                {

                    $dmi = new DetallesMovimientoInventario();

                    if($TipoOperacion == "Negativo")
                    {

                        $objeto[$i][0] = $objeto[$i][0] * -1;

                    }

                    $dmi->setCantidadTransferir($objeto[$i][0]);
                    $dmi->setIdAlmacen($AlmacenId);

                    $localidad = new Localidades();
                    $localidad->COL_LOCALIDAD_ID = $LocalidadId;
                    $dmi->setLocalidad($localidad);

                    $lote = new Lotes();
                    $lote->COL_LOTE_ID = $objeto[$i][2];
                    $dmi->setLote($lote);

                    array_push($arrayDetallesMovimiento, $dmi);

                }

            }

            ProcesadorMovimientoInventarios::registraMovimientoEnInventario($TraspasosMovtos, $arrayDetallesMovimiento, null);

        }
        catch(\Exception $e){

            throw $e;

        }

    }

    public function procesaTraspaso(){

//        $json =
//            [
//                "EmpleadoId" => '4AB7E31F-E573-492C-98A1-6BE4A0741717',
//                "HoraInicio" => '20171011 16:44:09.990',
//                "HoraTermino" => '20171011 16:50:09.990',
//                "Traspasos" => [
//
//                    [
//                        'Lotes' => [
//                            ['Lote' => '212131115',
//                            'LocalidadId' => 'DE47EF57-ADAC-4DFB-9E67-372B96C8E943',
//                            'Cantidad'=> 3
//                            ],
//                            ['Lote' => '212131115',
//                            'LocalidadId' => 'DE47EF57-ADAC-4DFB-9E67-372B96C8E943',
//                            'Cantidad'=> 6
//                            ]
//                        ],
//                        'SolicitudDetalleId' => '5D91C29E-73B6-4FED-A8DE-000024217B58',
//                        'CantidadTraspasar'=> 9
//                    ],
//                    [
//                        'Lotes' => [
//                            ['Lote' => '104101115',
//                            'LocalidadId' => 'DE47EF57-ADAC-4DFB-9E67-372B96C8E943',
//                            'Cantidad'=> 4
//                            ]
//                        ],
//                        'SolicitudDetalleId' => '94AE8F73-AE18-41A0-8A43-288561428ECF',
//                        'CantidadTraspasar'=> 4
//                    ]
//                ]
//		    ]
//        ;

//
//        dd($json['Traspasos']['EmpleadoId']);

//        $jsonResponse = json_encode(
//            [
//                "Respuesta" => [
//                    [
//                        'InformacionError' => [
//                            ['Lote' => $lote->LOT_CodigoLote,
//                                'Localidad' => $codigoLocalidad ." - ".$localidad->LOC_Nombre,
//                                'Articulo'=> $lote->ART_CodigoArticulo
//                            ]
//                        ],
//                        'Estatus' => 'Error',
//                        'Mensaje'=> "No es posible sacar la cantidad de ". abs($cantidadTraspasar) .", ya que su existencia es de " . $loteLocalidad[0]->LOTL_Cantidad . "."
//                    ]
//                ]
//            ]
//        );

        $jsonTraspasos = json_decode(\Illuminate\Support\Facades\Request::input('traspasos'), true);
        //$jsonTraspasos = $json;
        $horaInicio = null;
        $horaTermino = null;
        if(isset($jsonTraspasos['HoraInicio'])){
            $horaInicio = $jsonTraspasos['HoraInicio'];
        }
        if(isset($jsonTraspasos['HoraTermino'])){
            $horaTermino = $jsonTraspasos['HoraTermino'];
        }

        TraspasosController::guardaProcesoTraspaso($jsonTraspasos['Traspasos'], $jsonTraspasos['EmpleadoId'], $horaInicio, $horaTermino);

        //return $jsonTraspasos;

    }

    private function guardaProcesoTraspaso($arrayTraspasos, $empleadoId, $horaInicio, $horaTermino){

        $longitud = count($arrayTraspasos);
        \DB::beginTransaction();
        try {

            for($i=0; $i<$longitud; $i++){

                TraspasosController::registraTraspasoDetalle($arrayTraspasos[$i]['SolicitudDetalleId']
                    , $arrayTraspasos[$i]['Lotes']
                    , $arrayTraspasos[$i]['CantidadTraspasar']
                    , $empleadoId
                    , array_key_exists('TraspasoId', $arrayTraspasos[$i]) ? $arrayTraspasos[$i]['TraspasoId'] : EmbarquesController::getNuevoId()
                );

            }

            $this->actualizaEstadoSolicitud($arrayTraspasos[0]['SolicitudDetalleId']);

            if($horaInicio != null && $horaTermino != null) {
                $this->guardaTiempoSolicitud($arrayTraspasos[0]['SolicitudDetalleId'], $horaInicio, $horaTermino, $empleadoId);
            }

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

        } catch (\Exception $e) {
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
        }

    }

    private function registraTraspasoDetalle($idSolicitudDetalleId, $arrayLotes, $cantidadTraspasar, $empleadoId, $traspasoId){

        try{

            $cantidadPorTraspasar =
                \DB::select(\DB::raw("
                select TRSD_Cantidad - SUM(ISNULL(TRAD_CantidadATraspasar,0)) as CANT, CMUM_Nombre
                , TRS_CMM_EstatusSolicitudId
                from TraspasosSolicitudes
                inner join TraspasosSolicitudesDetalle on TRSD_TRS_TraspasoSolicitudId = TRS_TraspasoSolicitudId
                inner join Articulos on ART_ArticuloId = TRSD_ART_ArticuloId
                inner join ControlesMaestrosUM on CMUM_UnidadMedidaId = ART_CMUM_UMInventarioId
                LEFT join TraspasosDetalle on TRAD_TRSD_DetalleId = TRSD_DetalleId
                WHERE TRSD_DetalleId = '" . $idSolicitudDetalleId . "' GROUP BY TRS_TraspasoSolicitudId, TRS_CMM_EstatusSolicitudId, TRSD_Cantidad, CMUM_Nombre"
                ));

            if($cantidadPorTraspasar[0]->TRS_CMM_EstatusSolicitudId == ControlesMaestrosMultiples::$CMM_EstatusSolicitudTraspaso_Recibido
                || $cantidadPorTraspasar[0]->TRS_CMM_EstatusSolicitudId == ControlesMaestrosMultiples::$CMM_EstatusSolicitudTraspaso_RecibidoParcial){

                throw new \Exception("Traspaso Ya Fue Recibido",300);

            }
            else {

                if ($cantidadPorTraspasar[0]->CANT > 0 && $cantidadPorTraspasar[0]->CANT >= $cantidadTraspasar) {

                    $traspasoDetalle = new TraspasosDetalle();
                    $traspasoDetalle->TRAD_TraspasoDetalleId = $traspasoId;
                    $traspasoDetalle->TRAD_TRSD_DetalleId = $idSolicitudDetalleId;
                    $traspasoDetalle->TRAD_CantidadATraspasar = $cantidadTraspasar;
                    $traspasoDetalle->TRAD_FechaTraspaso = date('Ymd H:i:s'); //Carbon::now('America/Mexico_City')->format('d/m/Y');
                    $traspasoDetalle->TRAD_FechaLote = date('Ymd H:i:s'); //Carbon::now('America/Mexico_City')->format('d/m/Y');
                    $traspasoDetalle->TRAD_EMP_CreadoPorId = $empleadoId;
                    $traspasoDetalle->TRAD_TRAM_TraspasoMovtoId = TraspasosController::guardaTraspasoMovto($cantidadTraspasar,
                        $idSolicitudDetalleId,
                        $traspasoDetalle->TRAD_TraspasoDetalleId,
                        $arrayLotes,
                        $empleadoId
                    );


                    try {
                        $traspasoDetalle->save();
                    } catch (\Illuminate\Database\QueryException $ex) {
                        $results = \DB::select(\DB::raw("select * from TraspasosDetalle where TRAD_TraspasoDetalleId  = '" . $traspasoDetalle->TRAD_TraspasoDetalleId . "'"));

                        if (sizeof($results) > 0)
                            throw new \Exception(" Informacion Enviada ", 301);
                        throw $ex;
                        // throw new \Exception(" Guardar Traspaso ", 304);

                    }

                } else {

                    if ($cantidadPorTraspasar[0]->CMUM_Nombre == 'kilogramos') {

                        $traspasoDetalle = new TraspasosDetalle();
                        $traspasoDetalle->TRAD_TraspasoDetalleId = $traspasoId;
                        $traspasoDetalle->TRAD_TRSD_DetalleId = $idSolicitudDetalleId;
                        $traspasoDetalle->TRAD_CantidadATraspasar = $cantidadTraspasar;
                        $traspasoDetalle->TRAD_FechaTraspaso = date('Ymd H:i:s'); //Carbon::now('America/Mexico_City')->format('d/m/Y');
                        $traspasoDetalle->TRAD_FechaLote = date('Ymd H:i:s'); //Carbon::now('America/Mexico_City')->format('d/m/Y');
                        $traspasoDetalle->TRAD_EMP_CreadoPorId = $empleadoId;
                        $traspasoDetalle->TRAD_TRAM_TraspasoMovtoId = TraspasosController::guardaTraspasoMovto($cantidadTraspasar,
                            $idSolicitudDetalleId,
                            $traspasoDetalle->TRAD_TraspasoDetalleId,
                            $arrayLotes,
                            $empleadoId
                        );


                        try {
                            $traspasoDetalle->save();
                        } catch (\Illuminate\Database\QueryException $ex) {
                            $results = \DB::select(\DB::raw("select * from TraspasosDetalle where TRAD_TraspasoDetalleId  = '" . $traspasoDetalle->TRAD_TraspasoDetalleId . "'"));

                            if (sizeof($results) > 0)
                                throw new \Exception(" Informacion Enviada ", 301);

                            throw new \Exception(" Guardar Traspaso ", 304);

                        }

                    } else {

                        throw new \Exception("No es posible realizar la transacción. La cantidad proporcionada es mayor a la cantidad pendiente por traspasar.");

                    }
                }

            }

        } catch (\Exception $ex) {
            throw $ex;
        }




    }

    private function guardaTraspasoMovto($cantidadTraspasar, $idSolicitudDetalleId, $idTraspasoDetalle, $arrayLotes, $empleadoId){

        try {
            $datos = TraspasosController::getCodigoSolicitudArticulo($idSolicitudDetalleId);

            $traspasoMovto = new TraspasoMovto();
            $traspasoMovto->TRAM_ART_ArticuloId = $datos->ART_ArticuloId;
            $traspasoMovto->TRAM_CantidadATraspasar = -$cantidadTraspasar;
            $traspasoMovto->TRAM_CMM_TipoTransferenciaId = 'D7D22076-0400-4C59-B88E-1AA98C910B9F';
            $traspasoMovto->TRAM_Razon = 'Traspaso de la Solicitud: ' . $datos->TRS_CodigoSolicitud;
            $traspasoMovto->TRAM_EMP_ModificadoPorId = $empleadoId;
            $traspasoMovto->TRAM_Referencia =
                "Traspaso de Articulo : " . $datos->ART_CodigoArticulo . ", "
                . "Cantidad: " . $cantidadTraspasar;
            $traspasoMovto->TRAM_ReferenciaMovtoId = $idTraspasoDetalle;

            $cantidadLotes = count($arrayLotes);
            $arrayDetallesMovimiento = array();

            $localidadGeneral = Localidades::where('LOC_LocalidadId', '=', DataBaseSession::getLocalidadGeneralPorCediId(DepartamentosController::getCedisPorEmpleadoId($empleadoId)))->get();

            if (count($localidadGeneral) <= 0) {
                throw new \Exception('No existe una localidad general de donde realizar el traspaso.');
            }

            for ($i = 0; $i < $cantidadLotes; $i++) {

                $dmi = new DetallesMovimientoInventario();

                $dmi->setCantidadTransferir(-$arrayLotes[$i]['Cantidad']);
                $dmi->setIdAlmacen($localidadGeneral[0]->LOC_ALM_AlmacenId);

                //if (EmbarquesController::tieneSeguimientoLocalidades($afectaRegistros->ARTICULO_ID)) {
                $localidad = new Localidades();
                $localidad->COL_LOCALIDAD_ID = $localidadGeneral[0]->LOC_LocalidadId;
                $dmi->setLocalidad($localidad);
                //}

                //if (EmbarquesController::tieneSeguimientoLotes($afectaRegistros->ARTICULO_ID)) {
                $lote = new Lotes();
                $loteId = LotesController::getIdLotePorCodigo($arrayLotes[$i]['Lote']);

                if($loteId == null){
                    throw new \Exception('El lote '.$arrayLotes[$i]['Lote'].' no existe.');
                }

                $lote->COL_LOTE_ID = $loteId;
                $dmi->setLote($lote);
                //}

                array_push($arrayDetallesMovimiento, $dmi);
            }

            return ProcesadorMovimientoInventarios::registraMovimientoEnInventario($traspasoMovto, $arrayDetallesMovimiento, null);
        }catch (\Exception $ex) {
            throw $ex;
        }


    }

    private function getCodigoSolicitudArticulo($idSolicitudDetalle){

        try {
            $datos = TraspasosSolicitudes::select('TRS_CodigoSolicitud', 'TRS_TraspasoSolicitudId', 'ART_ArticuloId', 'ART_CodigoArticulo')
                ->join('TraspasosSolicitudesDetalle', 'TRS_TraspasoSolicitudId', '=', 'TRSD_TRS_TraspasoSolicitudId')
                ->join('Articulos', 'ART_ArticuloId', '=', 'TRSD_ART_ArticuloId')
                ->where('TRSD_DetalleId', '=', $idSolicitudDetalle)
                ->get(1)[0];

            return $datos;
        }catch (\Exception $ex) {
            throw $ex;
        }


    }

    private function actualizaEstadoSolicitud($idSolcitudDetalle){

        try {
            $idSolicitud = $this->getCodigoSolicitudArticulo($idSolcitudDetalle)->TRS_TraspasoSolicitudId;

            $resultadoConsulta =
                \DB::select(\DB::raw("
                SELECT TRSD_Cantidad - SUM(ISNULL(TRAD_CantidadATraspasar,0.0)) as TRASPASO
                FROM
                TraspasosSolicitudes
                INNER JOIN TraspasosSolicitudesDetalle on TRSD_TRS_TraspasoSolicitudId = TRS_TraspasoSolicitudId
                LEFT JOIN TraspasosDetalle on TRAD_TRSD_DetalleId = TRSD_DetalleId
                WHERE TRS_TraspasoSolicitudId = '" . $idSolicitud . "'
                GROUP BY TRSD_DetalleId, TRSD_Cantidad
                "
                ));

            $longitud = count($resultadoConsulta);

            $status = '9367F403-C6E8-4952-8276-F71B8E92B641';

            for ($i = 0; $i < $longitud; $i++) {

                if ($resultadoConsulta[$i]->TRASPASO > 0) {

                    $status = '58B06E0A-7D6F-482A-AB9D-287FA7872E7E';
                    break;

                }

            }

            $solicitud = TraspasosSolicitudes::find($idSolicitud);

            $solicitud->TRS_CMM_EstatusSolicitudId = $status;
            $solicitud->save();

        }catch (\Exception $ex) {
            throw $ex;
        }

    }

    private function guardaTiempoSolicitud($idSolcitudDetalle, $horaInicio, $horaTermino, $idEmpleado){

        try {
            $idSolicitud = $this->getCodigoSolicitudArticulo($idSolcitudDetalle)->TRS_TraspasoSolicitudId;

            $traspasoSolicitudTiempo = new TraspasosSolicitudesTiempos();
            $traspasoSolicitudTiempo->TST_TRS_TraspasoSolicitudId = $idSolicitud;
            $traspasoSolicitudTiempo->TST_HoraInicio = $horaInicio;
            $traspasoSolicitudTiempo->TST_HoraTermino = $horaTermino;
            $traspasoSolicitudTiempo->TST_EMP_CreadoPorId = $idEmpleado;
            $traspasoSolicitudTiempo->save();

        }catch (\Exception $ex) {
            throw $ex;
        }

    }

}

