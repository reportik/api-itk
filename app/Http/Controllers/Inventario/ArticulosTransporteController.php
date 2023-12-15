<?php namespace App\Http\Controllers\Inventario;

use Illuminate\Support\Facades\Facade;
use App\Http\Controllers\Inventario\Articulos\ArticulosController;
use App\Http\Controllers\inventario\Localidades\LocalidadesArticuloController;
use App\Http\Controllers\Sistema\AutonumericoController;
use App\Http\Controllers\Sistema\DataBaseSession;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as NewRequest;
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
use Response;

class ArticulosTransporteController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{

        $encabezados =array(
            'Id',
            'Código',
            'Fecha',
            'Ruta',
            'Origen',
            'Destino',
            'Status'
        );

        $contenidos=array(
            'TRS_TraspasoSolicitudId',
            'TRS_CodigoSolicitud',
            'TRS_FechaSolicitud',
            'RUT_Nombre',
            'Origen',
            'Destino',
            'CMM_Valor'
        );

        /*$results=\DB::table('TraspasosSolicitudes')
            ->join('ControlesMaestrosMultiples','CMM_ControlId','=','TRS_CMM_EstatusSolicitudId')
            ->where('TRS_Eliminado','=',0)
            ->select($contenidos)
            ->get();*/

        $results = \DB::select(\DB::raw(
            "select TRS_TraspasoSolicitudId,TRS_CodigoSolicitud,
                CAST(TRS_FechaSolicitud AS DATE) AS TRS_FechaSolicitud,
                RUT_Nombre, ALM_OR.ALM_Nombre + ' - '+ Origen.LOC_Nombre as Origen,ALM_DES.ALM_Nombre + ' - '+  Destino.LOC_Nombre as Destino, CMM_Valor
                from TraspasosSolicitudes
                inner join Localidades Destino on Destino.LOC_LocalidadId = TRS_LOC_LocalidadDestinoId
                inner join Localidades Origen on Origen.LOC_LocalidadId = TRS_LOC_LocalidadOrigenId
                inner join Almacenes ALM_OR on ALM_OR.ALM_AlmacenId = Origen.LOC_ALM_AlmacenId
                inner join Almacenes ALM_DES on ALM_DES.ALM_AlmacenId = Destino.LOC_ALM_AlmacenId
                inner join ControlesMaestrosMultiples on CMM_ControlId = TRS_CMM_EstatusSolicitudId
                left join TransportesUnidades on TUN_LOC_LocalidadId = TRS_LOC_LocalidadOrigenId AND TUN_Eliminado = 0
                left join Rutas on RUT_TUN_TransporteUnidadId = TUN_TransporteUnidadId
                where TRS_Eliminado = 0
                AND TRS_FechaSolicitud >= convert(char(6), dateadd(month, -1, getdate()), 112) + '01'
                order by CAST(TRS_FechaSolicitud AS DATE) desc, TRS_CodigoSolicitud DESC"
        ));

        $almacenes=array(''=>'Almacen') + Almacen::orderby('ALM_Nombre','ASC')->lists('ALM_Nombre','ALM_AlmacenId')->all();

        /*$almacenes = array(''=>'Seleccione Almacen') + Localidad::select('LOC_LocalidadId','ALM_Nombre')
                ->join('Almacenes', 'ALM_AlmacenId', '=', 'LOC_ALM_AlmacenId')
                //->where('LOC_LocalidadGeneral','=',1)
                ->where('LOC_General','=',1)
                ->orderby('ALM_Nombre','ASC')
                ->lists('ALM_Nombre','LOC_LocalidadId');

        $localidades = array(''=>'Seleccione Localidad') + Localidad::select('LOC_LocalidadId', \DB::raw("ALM_Nombre + (CASE WHEN LOC_LocalidadGeneral = 0 THEN ' - ' + LOC_Nombre ELSE '' END) AS FULL_NAME"))
                ->join('Almacenes','ALM_AlmacenId','=','LOC_ALM_AlmacenId')
                ->lists('FULL_NAME','LOC_LocalidadId');*/

        $articulos=array(''=>'Productos') + Articulos::orderby('ART_CodigoArticulo','ASC')->lists('ART_Nombre','ART_ArticuloId')->all();

        return view('Inventario.ArticulosTransporte.create', compact('results', 'encabezados', 'contenidos','articulos', 'almacenes'));

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
		//
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

        $almacenes = array(''=>'Selecciona Almacén') + Localidad::select('LOC_LocalidadId','ALM_Nombre')
                ->join('Almacenes', 'ALM_AlmacenId', '=', 'LOC_ALM_AlmacenId')
                //->where('LOC_LocalidadGeneral','=',1)
                ->where('LOC_LocalidadId','=',DataBaseSession::getLocalidadGeneralId())
                ->orderby('ALM_Nombre','ASC')
                ->lists('ALM_Nombre','LOC_LocalidadId');

        $almacenesLocalidades = array(''=>'Selecciones Almacen/Localidad') + Localidad::select('LOC_LocalidadId', \DB::raw("ALM_Nombre + (CASE WHEN LOC_LocalidadGeneral = 0 THEN ' - ' + LOC_Nombre ELSE '' END) AS FULL_NAME"))
                ->join('Almacenes','ALM_AlmacenId','=','LOC_ALM_AlmacenId')
                ->whereRaw(" ALM_AlmacenId IN (".DataBaseSession::getAlmacenesPorCediId().")")
                ->lists('FULL_NAME','LOC_LocalidadId');
        $articulos=array(''=>'Selecciona Código del Artículo') + Articulos::orderby('ART_CodigoArticulo','ASC')->lists('ART_CodigoArticulo','ART_ArticuloId');

        return view('Inventario.ArticulosTransporte.editar', compact('id','CodigoSolicitud','resultados', 'almacenesLocalidades', 'articulos', 'almacenes'));

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
                $idTrapasosMovtos = ArticulosTransporteController::obtenerTransferenciaMvoto($articulo,$val,$_POST['CCON_contador3'],$operacion);

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
                $CantidadAnteriorYLocalidadId = ArticulosTransporteController::restarPiezasEnLocalidad($_POST['TRAD_CantidadATraspasar'.$val],$articulo[0]->ART_ArticuloId,null,$operacion);
                //INSERTA EN TRASPASOS LOCALIDADES
                ArticulosTransporteController::guardaTransferencia($idTrapasosMovtos,$CantidadAnteriorYLocalidadId[0],$CantidadAnteriorYLocalidadId[1],$_POST['TRAD_CantidadATraspasar'.$val],$articulo[0]->ART_CantidadAMano,$operacion);
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

    public function Get_Consulta(){

        $results = \DB::select(\DB::raw(
            "select TRS_TraspasoSolicitudId AS DT_RowId,TRS_CodigoSolicitud,
                CAST(TRS_FechaSolicitud AS DATE) AS TRS_FechaSolicitud,
                RUT_Nombre, ALM_OR.ALM_Nombre + ' - '+ Origen.LOC_Nombre as Origen,ALM_DES.ALM_Nombre + ' - '+  Destino.LOC_Nombre as Destino, CMM_Valor
                from TraspasosSolicitudes
                inner join Localidades Destino on Destino.LOC_LocalidadId = TRS_LOC_LocalidadDestinoId
                inner join Localidades Origen on Origen.LOC_LocalidadId = TRS_LOC_LocalidadOrigenId
                inner join Almacenes ALM_OR on ALM_OR.ALM_AlmacenId = Origen.LOC_ALM_AlmacenId
                inner join Almacenes ALM_DES on ALM_DES.ALM_AlmacenId = Destino.LOC_ALM_AlmacenId
                inner join ControlesMaestrosMultiples on CMM_ControlId = TRS_CMM_EstatusSolicitudId
                left join TransportesUnidades on TUN_LOC_LocalidadId = TRS_LOC_LocalidadOrigenId AND TUN_Eliminado = 0
                left join Rutas on RUT_TUN_TransporteUnidadId = TUN_TransporteUnidadId
                where TRS_Eliminado = 0
                AND TRS_FechaSolicitud >= convert(char(6), dateadd(month, -1, getdate()), 112) + '01'
                order by CAST(TRS_FechaSolicitud AS DATE) desc, TRS_CodigoSolicitud DESC"
        ));

        $ajaxData = array();
        $ajaxData['data'] = $results;
        $ajaxData['options'] = array();
        return (json_encode($ajaxData));

    }

    public function Get_Localidades(){

        $AlmacenId = NewRequest::input('idAlmacen');

        $Localidades = \DB::select(\DB::raw("
            SELECT LOC_Nombre, LOC_LocalidadId
            FROM Localidades
            WHERE LOC_ALM_AlmacenId = '$AlmacenId'
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
            $where = "WHERE LOC_LocalidadId = '$LocalidadId' AND ART_Anaquel = 1";

        else
            $where = "WHERE ALM_AlmacenId = '$AlmacenId' AND ART_Anaquel = 1";

        $Articulos = \DB::select(\DB::raw("
            SELECT DISTINCT (ART_CodigoArticulo + ' - ' + ART_Nombre) AS ART_Nombre, ART_ArticuloId
            FROM LocalidadesArticulo
            INNER JOIN Articulos ON LOCA_ART_ArticuloId = ART_ArticuloId
            INNER JOIN Localidades ON LOCA_LOC_LocalidadId = LOC_LocalidadId
            INNER JOIN Almacenes ON LOC_ALM_AlmacenId = ALM_AlmacenId
            $where
            --AND ART_Anaquel = 0
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
            $where
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
                            ArticulosTransporteController::procesaTransferirArticulo($TipoOperacion,$TraspasoId,$objeto,$AlmacenId,$LocalidadId,$Codigo_Traspaso,$CantidadPorTraspasar,$ArticuloId,$UnidadMedida[0]->CMUM_Nombre,$Articulo->ART_CodigoArticulo,$Articulo->ART_CantidadAMano);

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
            $autonumericoFicha = $autonumerico_dao->getAutonumericoN("CM_INV_SiguienteTraspaso",$cediId);
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

}
