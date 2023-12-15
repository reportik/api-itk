<?php namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Inventario\Articulos\ArticulosController;
use App\Http\Controllers\inventario\Localidades\LocalidadesArticuloController;
use App\Http\Controllers\inventario\Localidades\LocalidadesController;
use App\Http\Controllers\Sistema\DataBaseSession;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;
use App\Mapeos\Controles\ControlesMaestros;
use App\Models\ControlesMaestrosMultiples;
use App\Models\Inventario\InventarioFisico\LocalidadesArticulo;
use App\Models\Inventario\InventarioFisico\ProcesadorMovimientoInventarios;
use App\Models\Inventario\InventarioFisico\TraspasoMovto;
use App\Models\inventario\LocalidadesArticulos;
use App\Models\inventario\TraspasosLocalidades;
use App\Models\Lotes;
use App\Models\LotesLocalidades;
use App\Models\TraspasosDetalle;
use App\Models\TraspasosLotes;
use App\Models\TraspasosRecibos;
use Illuminate\Support\Facades\Request as NewRequest;

class CancelacionDevolucionesTraspasosController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{

        //date_default_timezone_set('America/Mexico_City');
        $fecha=date('d/m/Y');

        return view('inventario.cancelaciondevolucionestraspasos.create', compact('fecha'));

	}

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

    public function consultarcantidaddecimales(){
        $result = ControlesMaestros::select('CMA_Valor')
            ->where('CMA_Control','=','CMA_INV_DecimalesCantidades')
            ->get();
        return Response::json($result);
    }

    public function consultarcantidaddecimalesgeneral(){
        $result = ControlesMaestros::select('CMA_Valor')
            ->where('CMA_Control','=','CMA_CSVP_DecimalesCantidades')
            ->get();
        return Response::json($result);
    }

    public function buscaDevolucionesTraspasos(){

        $FechaInicio = NewRequest::input('fechaDesde');
        $FechaFinal = NewRequest::input('fechaHasta');

        $consultaDevolucionesTraspasos = \DB::select(

            \DB::raw(

                "SELECT TRD_TraspasoDevolucionId AS DT_RowId, TRD_CodigoDevolucion, TRS_CodigoSolicitud, TRD_FechaDevolucion, RUT_Nombre
                FROM TraspasosDevoluciones
                INNER JOIN TraspasosSolicitudes ON TRS_TraspasoSolicitudId = TRD_TRS_TraspasoSolicitudId
                INNER JOIN TransportesUnidades ON TRS_LOC_LocalidadOrigenId = TUN_LOC_LocalidadId
                INNER JOIN Rutas ON RUT_TUN_TransporteUnidadId = TUN_TransporteUnidadId
                WHERE TRD_Estatus = 1
                AND CAST(TRD_FechaDevolucion AS DATE) BETWEEN '".$FechaInicio."' AND '".$FechaFinal."'
                ".(DataBaseSession::isPermisoCorporativo() ? "" : " AND TRS_LOC_LocalidadOrigenId IN ( SELECT LOC_LocalidadId FROM Localidades WHERE LOC_ALM_AlmacenId IN (".DataBaseSession::getAlmacenesPorCediId()."))")."
                GROUP BY TRD_TraspasoDevolucionId, TRD_CodigoDevolucion, TRS_CodigoSolicitud, TRD_FechaDevolucion, RUT_Nombre
                ORDER BY TRD_FechaDevolucion desc, TRD_CodigoDevolucion ASC"

            )

        );

        $ajaxData = array();
        $ajaxData['data'] = $consultaDevolucionesTraspasos;
        $ajaxData['options'] = array();
        return (json_encode($ajaxData));

    }

    public function CancelarDevolucionesTraspasosGeneral($TRD_TraspasoDevolucionId){

        \DB::beginTransaction();

        try {

            $consultaTraspasoMovto = CancelacionDevolucionesTraspasosController::consultaTraspasoMovto($TRD_TraspasoDevolucionId);
            $cuenta = count($consultaTraspasoMovto);
            $banderaExistencias = true;
            for($x = 0; $x < $cuenta; $x ++)
            {

                //localidad a restar
                $buscaLoteYLocalidadId = CancelacionDevolucionesTraspasosController::buscaLoteYLocalidadId($consultaTraspasoMovto[$x]->TRAM_TraspasoMovtoId);
                $LoteId = "";

                if(count($buscaLoteYLocalidadId) < 1)
                {

                    $buscaLoclidadArticuloId = CancelacionDevolucionesTraspasosController::buscaLocalidadArticuloId($consultaTraspasoMovto[$x]->TRAM_TraspasoMovtoId);
                    $LocalidadId = $buscaLoclidadArticuloId[0]->LOCA_LOC_LocalidadId;

                }
                else{

                    $LocalidadId = $buscaLoteYLocalidadId[0]->LOTL_LOC_LocalidadId;
                    $LoteId = $buscaLoteYLocalidadId[0]->LOTL_LOT_LoteId;

                }

                //VALIDAR SI HAY EXISTENCIAS EN EL LOTE SUFICIENTES PARA RESTAR Y NO QUEDE NEGATIVO
                //dd($buscaLoteYLocalidadId[0]->LOTL_Cantidad." *** ".$consultaTraspasoMovto[$x]->TRAM_CantidadATraspasar);
                if($buscaLoteYLocalidadId[0]->LOTL_Cantidad >= $consultaTraspasoMovto[$x]->TRAM_CantidadATraspasar)
                {

                    //localidad a sumar
                    $obtenerTraspasoMvtoIdRecibo = TraspasosRecibos::find($consultaTraspasoMovto[0]->TRAM_TraspasoMovtoId);
                    $buscaLoteYLocalidadIdRecibo = CancelacionDevolucionesTraspasosController::buscaLoteYLocalidadId($obtenerTraspasoMvtoIdRecibo->TRAR_TRAM_TraspasoMovtoId);
                    $LoteIdRecibo = "";
                    if(count($buscaLoteYLocalidadIdRecibo) < 1)
                    {
                        $buscaLoclidadArticuloId = CancelacionDevolucionesTraspasosController::buscaLocalidadArticuloId($obtenerTraspasoMvtoIdRecibo->TRAM_TraspasoMovtoId);
                        $LocalidadIdRecibo = $buscaLoclidadArticuloId[0]->LOCA_LOC_LocalidadId;
                    }
                    else{
                        $LocalidadIdRecibo = $buscaLoteYLocalidadIdRecibo[0]->LOTL_LOC_LocalidadId;
                        $LoteIdRecibo = $buscaLoteYLocalidadIdRecibo[0]->LOTL_LOT_LoteId;
                    }

                    $Articulo = ArticulosController::buscaPorId($consultaTraspasoMovto[$x]->TRAM_ART_ArticuloId);
                    $CantidadPorAjustar = $consultaTraspasoMovto[$x]->TRAM_CantidadATraspasar;
                    $UnidadMedida = ArticulosController::buscaNombreUMInventarioPorArticuloId($consultaTraspasoMovto[$x]->TRAM_ART_ArticuloId);
                    $MotivoAjuste = "Cancelacion de la Devolución de Traspaso de la Solicitud";
                    $Comentarios = "";

                    //traspaso detalle
                    $signo = "negativo";
                    $tipo_transferenciaId = \App\Mapeos\Controles\ControlesMaestrosMultiples::$Cancelación_Devolución_Traspaso;
                    $transferencia_id = CancelacionDevolucionesTraspasosController::procesaTransferirArticulo($CantidadPorAjustar * -1,$consultaTraspasoMovto[$x]->TRAM_ART_ArticuloId,$MotivoAjuste,$UnidadMedida[0]->CMUM_Nombre,$Articulo->ART_CantidadAMano,$tipo_transferenciaId);
                    CancelacionDevolucionesTraspasosController::guardaCambios($transferencia_id,$CantidadPorAjustar,$Articulo->ART_ArticuloId,$Articulo->ART_CantidadAMano,$Articulo->ART_SeguimientoLotMult,$LocalidadId,$LoteId,$signo);
                    CancelacionDevolucionesTraspasosController::guardaTraspasosDetalle($consultaTraspasoMovto[$x]->TRAD_TRSD_DetalleId,$transferencia_id,$CantidadPorAjustar,$MotivoAjuste,$Comentarios);

                    //traspasos recibos
                    $signo = "positivo";
                    $tipo_transferenciaId = \App\Mapeos\Controles\ControlesMaestrosMultiples::$Cancelación_Devolución_Traspaso_Recibo;
                    $transferencia_id = CancelacionDevolucionesTraspasosController::procesaTransferirArticulo($CantidadPorAjustar,$consultaTraspasoMovto[$x]->TRAM_ART_ArticuloId,$MotivoAjuste,$UnidadMedida[0]->CMUM_Nombre,$Articulo->ART_CantidadAMano,$tipo_transferenciaId);
                    CancelacionDevolucionesTraspasosController::guardaCambios($transferencia_id,$CantidadPorAjustar,$Articulo->ART_ArticuloId,$Articulo->ART_CantidadAMano,$Articulo->ART_SeguimientoLotMult,$LocalidadIdRecibo,$LoteIdRecibo,$signo);
                    CancelacionDevolucionesTraspasosController::guardaTraspasosRecibos($consultaTraspasoMovto[$x]->TRAD_TraspasoDetalleId,$transferencia_id,$CantidadPorAjustar,$consultaTraspasoMovto[$x]->TRAR_TraspasoReciboId);

                    $cantidadAManoActual = $Articulo->ART_CantidadAMano;
                    $nuevoCantidadAMano = $cantidadAManoActual+$CantidadPorAjustar;
                    $Articulo->ART_CantidadAMano = $nuevoCantidadAMano;
                    $Articulo->ART_CantidadUltimoAjuste = $CantidadPorAjustar;
                    ArticulosController::actualizaCamposDeAjustePorId($Articulo);

                }
                else
                {

                    $banderaExistencias = false;
                    break;

                }

            }

            $valor = 'Valido';
            if($banderaExistencias)
            {

                CancelacionDevolucionesTraspasosController::cambiarStatusTRD($TRD_TraspasoDevolucionId);
                CancelacionDevolucionesTraspasosController::cambiarStatusST($consultaTraspasoMovto[0]->TRS_TraspasoSolicitudId);

                //return Response::json(true);

                $mensaje = 'La Cancelación de la Devolucion se registró con éxito.';

                \DB::commit();

            }
            else
            {

                $valor = 'Error';
                $mensaje = 'No se puede hacer la cancelacion de la devolucion, no existe suficiente existencia para restar al lote.';

            }

            return ['Status' => $valor, 'Mensaje' => $mensaje];

        }
        catch (\Exception $e) {

            \DB::rollback();

            return ['Status' => 'Error', 'Mensaje' => 'No se registró La Cancelación de la Devolucion. Ocurrió un error al realizar el proceso. <br><br><br>Error: <br>'.$e->getMessage()];

        }

    }

    public function consultaTraspasoMovto($TRD_TraspasoDevolucionId){

        $consultaTraspasoMovto = \DB::select(

            \DB::raw(

                "SELECT TRAM_TraspasoMovtoId,TRAM_ART_ArticuloId,TRAM_CantidadATraspasar, TRAD_TRSD_DetalleId,
                TRAD_TraspasoDetalleId, TRAR_TraspasoReciboId, TRS_TraspasoSolicitudId
                FROM TraspasosDevoluciones
                INNER JOIN TraspasosDevolucionesDetalle ON TRDD_TRD_TraspasoDevolucionId = TRD_TraspasoDevolucionId
                INNER JOIN TraspasosSolicitudes ON TRS_TraspasoSolicitudId = TRD_TRS_TraspasoSolicitudId
                INNER JOIN TraspasosDetalle ON TRAD_TraspasoDetalleId = TRDD_TRAD_TraspasoDetalleId
                LEFT JOIN TraspasosRecibos ON TRAD_TraspasoDetalleId = TRAR_TRAD_TraspasoDetalleId
                INNER JOIN TraspasosMovtos ON TRAM_TraspasoMovtoId = TRAD_TRAM_TraspasoMovtoId
                WHERE TRD_TraspasoDevolucionId = '".$TRD_TraspasoDevolucionId."'"

            )

        );

        return $consultaTraspasoMovto;

    }

    public function buscaLoteYLocalidadId($TraspasoMvtoId){

        $LoteYLocalidadId = TraspasoMovto::select('LOTL_LOT_LoteId','LOTL_LOC_LocalidadId','LOTL_Cantidad')
            ->join('TraspasosLotes','TRAM_TraspasoMovtoId','=','TRLOT_TRAM_TraspasoMovtoId')
            ->join('LotesLocalidades','TRLOT_LOTL_LoteLocalidadId','=','LOTL_LoteLocalidadId')
            ->where('TRAM_TraspasoMovtoId','=',$TraspasoMvtoId)
            ->get();

        return $LoteYLocalidadId;

    }

    public function buscaLocalidadArticuloId($TraspasoMvtoId){

        $LocalidadArticuloId = TraspasoMovto::select('LOCA_LOC_LocalidadId')
            ->join('TraspasosLocalidades','TRAM_TraspasoMovtoId','=','TRLOC_TRAM_TraspasoMovtoId')
            ->join('LocalidadesArticulo','TRLOC_LOCA_LocalidadArticuloId','=','LOCA_LocalidadArticuloId')
            ->where('TRAM_TraspasoMovtoId','=',$TraspasoMvtoId)
            ->get();

        return $LocalidadArticuloId;

    }

    public static function procesaTransferirArticulo($CantidadPorAjustar,$idArt,$MotivoAjuste,$UnidadMedida,$CantidadAMano,$tipoTrasnferenciaId){

        try{

            $TraspasosMovtos = new TraspasoMovto();
            $TraspasosMovtos->TRAM_ART_ArticuloId = $idArt;
            $TraspasosMovtos->TRAM_CantidadATraspasar = $CantidadPorAjustar * -1;
            $TraspasosMovtos->TRAM_CMM_TipoTransferenciaId = $tipoTrasnferenciaId;
            $TraspasosMovtos->TRAM_Razon = $MotivoAjuste;
            //$TraspasosMovtos->TRAM_Referencia=$comentarios;
            $TraspasosMovtos->TRAM_UnidadMedidadArt = $UnidadMedida;
            $TraspasosMovtos->TRAM_EstatusContable = false;
            $TraspasosMovtos->TRAM_CantidadAMano = $CantidadAMano;
            $TraspasosMovtos->TRAM_CantidadAManoConTraspaso = $CantidadAMano + ($CantidadPorAjustar * -1);
            $TraspasosMovtos->save();

            $ultimoinsertado = ProcesadorMovimientoInventarios::buscaIdUltimoInsertado();

            return $ultimoinsertado;

        }
        catch(\Exception $e){

            throw $e;

        }

    }

    public static function guardaCambios($transferencia_id,$CantidadPorAjustar,$ART_ArticuloId,$CantidadAMano,$ART_SeguimientoLotMult,$LocalidadId,$LoteId,$signo){

        try{

            if($signo == 'negativo')
            {

                $TotalCantidadAjuste=$CantidadPorAjustar * -1;//cantidadajuste
                $CantidadPorAjustar=$CantidadPorAjustar * -1;//cantidadajuste

            }
            else
            {

                $TotalCantidadAjuste=$CantidadPorAjustar;//cantidadajuste
                $CantidadPorAjustar=$CantidadPorAjustar;//cantidadajuste

            }

            $codigoLocalidad = LocalidadesController::buscaCodigoLocalidad($LocalidadId);//localidadId
            CancelacionDevolucionesTraspasosController::asignarInventarioEnLocalidad($TotalCantidadAjuste,$ART_ArticuloId,$LocalidadId,$CantidadPorAjustar,$codigoLocalidad,$transferencia_id,$CantidadAMano);//articuloId, localidadId
            if($ART_SeguimientoLotMult == 1)
            {

                if($LoteId != "")
                {

                    $codigoLote = CancelacionDevolucionesTraspasosController::buscaCodigoLotePorId($LoteId);
                    CancelacionDevolucionesTraspasosController::asignarInventarioEnLote($codigoLote[0]->LOT_CodigoLote,$TotalCantidadAjuste,$ART_ArticuloId,$LocalidadId,$CantidadPorAjustar,$codigoLocalidad,$transferencia_id,$CantidadAMano);//codigoLote, articuloId, localidadId

                }

            }

        }
        catch(\Exception $e){

            throw $e;

        }

    }

    public static function buscaCodigoLotePorId($LoteId){

        $codigoLote = Lotes::where('LOT_LoteId','=',$LoteId)->get();

        return $codigoLote;

    }

    public static function asignarInventarioEnLocalidad($TotalCantidadAjuste,$articuloId,$localidadId,$CantidadPorAjustar,$codigoLocalidad,$transferencia_id,$CantidadAMano){

        try{

            $loc_art = LocalidadesArticuloController::buscaPorArticuloIdYLocalidadIdTodos($articuloId,$localidadId);
            if(count($loc_art) == 0)
            {

                $loc_art = new LocalidadesArticulos();
                $loc_art->LOCA_LOC_LocalidadId = $localidadId;
                $loc_art->LOCA_ART_ArticuloId = $articuloId;
                $loc_art->LOCA_Cantidad = $CantidadPorAjustar * -1;
                $loc_art->save();
                $loc_art =LocalidadesArticuloController::buscaIdUltimoInsertado();
                $loc_art[0]->LOCA_Cantidad = 0;
            }
            else if($loc_art[0]->LOCA_Eliminado == 1)
            {

                $nuevaCantidadPorAjustar = $loc_art[0]->LOCA_Cantidad + ($CantidadPorAjustar * -1);
                LocalidadesArticuloController::restauraPorId($loc_art,$nuevaCantidadPorAjustar);

            }
            else
            {

                $nuevaCantidadPorAjustar = $loc_art[0]->LOCA_Cantidad+ ($CantidadPorAjustar * -1);
                LocalidadesArticuloController::actualizaPorId($loc_art,$nuevaCantidadPorAjustar);

            }
            $trans_localidad = new TraspasosLocalidades();
            $trans_localidad->TRLOC_TRAM_TraspasoMovtoId = $transferencia_id;
            $trans_localidad->TRLOC_LOCA_LocalidadArticuloId = $loc_art[0]->LOCA_LocalidadArticuloId;
            $trans_localidad->TRLOC_CantidadTransferida = $CantidadPorAjustar * -1;
            $trans_localidad->TRLOC_CodigoLocalidad = $codigoLocalidad;
            $trans_localidad->TRLOC_CantidadAMano = $CantidadAMano;
            $trans_localidad->TRLOC_CantidadAnteriorLocalidad = $loc_art[0]->LOCA_Cantidad;
            $trans_localidad->save();

        }
        catch(\Exception $e){

            throw $e;

        }

    }

    public static function asignarInventarioEnLote($CodigoLote,$TotalCantidadAjuste,$articuloId,$localidadId,$CantidadPorAjustar,$codigoLocalidad,$transferencia_id,$CantidadAMano){

        try{

            $Lotes = LotesController::buscaLotePorCodigoLote($CodigoLote);
            $loteloca = LotesLocalidadesController::buscaPorLoteIdYLocalidadId($Lotes[0]->LOT_LoteId,$localidadId);

            if(count($loteloca) == 0)
            {

                $loteloca = new LotesLocalidades();
                $loteloca->LOTL_LOT_LoteId = $Lotes[0]->LOT_LoteId;
                $loteloca->LOTL_LOC_LocalidadId = $localidadId;
                $loteloca->LOTL_Cantidad = $CantidadPorAjustar * -1;
                $loteloca->save();
                $loteloca = LotesLocalidadesController::buscaIdUltimoInsertado();
                $loteloca[0]->LOTL_Cantidad = 0;

            }
            else if($loteloca[0]->LOTL_Eliminado == 1)
            {

                $nuevaCantidadPorAjustar = $loteloca[0]->LOTL_Cantidad + ($CantidadPorAjustar * -1);
                LotesLocalidadesController::restauraPorId($loteloca,$nuevaCantidadPorAjustar);

            }
            else
            {

                $nuevaCantidadPorAjustar = $loteloca[0]->LOTL_Cantidad + ($CantidadPorAjustar * -1);
                LotesLocalidadesController::actualizaPorId($loteloca,$nuevaCantidadPorAjustar);

            }
            $trans_lotes = new TraspasosLotes();
            $trans_lotes->TRLOT_TRAM_TraspasoMovtoId = $transferencia_id;
            $trans_lotes->TRLOT_LOTL_LoteLocalidadId = $loteloca[0]->LOTL_LoteLocalidadId;
            $trans_lotes->TRLOT_CantidadTraspaso = $CantidadPorAjustar * -1;
            $trans_lotes->TRLOT_CantidadAMano = $CantidadAMano;
            $trans_lotes->TRLOT_CantidadAnteriorLote = $loteloca[0]->LOTL_Cantidad;
            $trans_lotes->save();

        }
        catch(\Exception $e){

            throw $e;

        }

    }

    public function guardaTraspasosDetalle($TRSD_DetalleId,$transferencia_id,$CantidadPorAjustar,$MotivoDevolucion,$TRAD_Comentarios){

        try{

            //date_default_timezone_set('America/Mexico_City');
            $hoy=date('d/m/Y H:i:s');

            $TraspasoDetalle = new TraspasosDetalle();
            $TraspasoDetalle->TRAD_TRSD_DetalleId = $TRSD_DetalleId;
            $TraspasoDetalle->TRAD_CantidadATraspasar = $CantidadPorAjustar;
            $TraspasoDetalle->TRAD_TRAM_TraspasoMovtoId = $transferencia_id;
            //$TraspasoDetalle->TRAD_CMM_MotivoDevolucionId = $MotivoDevolucion;
            //$TraspasoDetalle->TRAD_Comentarios = $TRAD_Comentarios;
            $TraspasoDetalle->TRAD_FechaLote = $hoy;
            $TraspasoDetalle->TRAD_FechaTraspaso = $hoy;
            $TraspasoDetalle->save();

        }
        catch(\Exception $e){

            throw $e;

        }

    }

    public function guardaTraspasosRecibos($TRAD_TraspasoDetalleId,$transferencia_id,$CantidadPorAjustar,$TRAR_TraspasoReciboId){

        try{

            //date_default_timezone_set('America/Mexico_City');
            $hoy=date('d/m/Y H:i:s');

            $TraspasoRecibo = new TraspasosRecibos();
            $TraspasoRecibo->TRAR_TRAD_TraspasoDetalleId = $TRAD_TraspasoDetalleId;
            $TraspasoRecibo->TRAR_TRAM_TraspasoMovtoId = $transferencia_id;
            $TraspasoRecibo->TRAR_CantidadRecibo = $CantidadPorAjustar;
            $TraspasoRecibo->TRAR_ReferenciaReciboId = $TRAR_TraspasoReciboId;
            $TraspasoRecibo->TRAR_FechaRecibo = $hoy;
            $TraspasoRecibo->save();

        }
        catch(\Exception $e){

            throw $e;

        }

    }

    public function cambiarStatusTRD($TRD_TraspasoDevolucionId){

        try{

            \DB::table('TraspasosDevoluciones')->where('TRD_TraspasoDevolucionId', '=', $TRD_TraspasoDevolucionId)
                ->update(
                    array(
                        'TRD_Estatus' => 0
                    )
                );

        }
        catch(\Exception $e){

            throw $e;

        }

    }

    public function cambiarStatusST($TRS_TraspasoSolicitudId){

        try{

            $consultaCantidades = \DB::select(

                \DB::raw(

                    "SELECT TRSD_Cantidad - TRAR_CantidadRecibo AS Total
                    FROM TraspasosSolicitudes
                    INNER JOIN TraspasosSolicitudesDetalle ON TRSD_TRS_TraspasoSolicitudId = TRS_TraspasoSolicitudId
                    INNER JOIN TraspasosDetalle ON TRAD_TRSD_DetalleId = TRSD_DetalleId
                    INNER JOIN TraspasosRecibos ON TRAR_TRAD_TraspasoDetalleId = TRAD_TraspasoDetalleId
                    WHERE TRS_TraspasoSolicitudId = '".$TRS_TraspasoSolicitudId."'"

                )

            );

            $cuenta = count($consultaCantidades);
            $bandera = true;
            for($x = 0; $x < $cuenta; $x ++)
            {

                if($consultaCantidades[$x]->Total > 0)
                {

                    $bandera = false;
                    break;

                }

            }
            if($bandera == true)
            {

                $status = \App\Mapeos\Controles\ControlesMaestrosMultiples::$CMM_EstatusSolicitudTraspaso_Recibido;

            }
            elseif($bandera == false)
            {

                $status = \App\Mapeos\Controles\ControlesMaestrosMultiples::$CMM_EstatusSolicitudTraspaso_Recibo_Parcial;

            }
            CancelacionDevolucionesTraspasosController::ActualizaStatusST($TRS_TraspasoSolicitudId,$status);

        }
        catch(\Exception $e){

            throw $e;

        }

    }

    public function ActualizaStatusST($TRS_TraspasoSolicitudId,$status){

        try{

            //date_default_timezone_set('America/Mexico_City');
            $hoy=date('d/m/Y H:i:s');

            \DB::table('TraspasosSolicitudes')->where('TRS_TraspasoSolicitudId', '=', $TRS_TraspasoSolicitudId)
                ->update(
                    array(
                        'TRS_CMM_EstatusSolicitudId' => $status,
                        'TRS_FechaUltimaModificacion' => $hoy
                        //'LOCA_EMP_ModificadoPor' => '',
                    )
                );

        }
        catch(\Exception $e){

            throw $e;

        }

    }

}
