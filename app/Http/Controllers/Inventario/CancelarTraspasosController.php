<?php namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Inventario\Articulos\ArticulosController;
use App\Http\Controllers\inventario\Localidades\LocalidadesArticuloController;
use App\Http\Controllers\inventario\Localidades\LocalidadesController;
use App\Http\Controllers\Sistema\DataBaseSession;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\ControlesMaestros;
use App\Models\ControlesMaestrosMultiples;
use Illuminate\Support\Facades\Response;
use App\Models\Inventario\InventarioFisico\ProcesadorMovimientoInventarios;
use App\Models\Inventario\InventarioFisico\TraspasoMovto;
use App\Models\inventario\TraspasosLocalidades;
use App\Models\Lotes;
use App\Models\LotesLocalidades;
use App\Models\TraspasosDetalle;
use App\Models\TraspasosDevoluciones;
use App\Models\TraspasosDevolucionesDetalle;
use App\Models\TraspasosLotes;
use App\Models\TraspasosRecibos;
use Illuminate\Support\Facades\Request as NewRequest;

class CancelarTraspasosController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{

        $motivocancelacion = ControlesMaestrosMultiples::where('CMM_Control','=', \App\Mapeos\Controles\ControlesMaestrosMultiples::$CMM_MotivoDevolucionTraspaso)->lists('CMM_Valor', 'CMM_ControlId')->all() + array(''=>'Selecciona Motivo de Devolución');
        //date_default_timezone_set('America/Mexico_City');
        $fecha=date('d/m/Y');

        return view('inventario.cancelaciontraspasos.create', compact('motivocancelacion','fecha'));

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

    public static function consultarcantidaddecimales(){

        $result = ControlesMaestros::select('CMA_Valor')
            ->where('CMA_Control','=','CMA_INV_DecimalesCantidades')
            ->get();

        return Response::json($result);

    }

    public static function consultarcantidaddecimalesgeneral(){

        $result = ControlesMaestros::select('CMA_Valor')
            ->where('CMA_Control','=','CMA_CSVP_DecimalesCantidades')
            ->get();

        return Response::json($result);

    }

    public static function buscaTraspasos(){

        $FechaInicio = NewRequest::input('fechaDesde');
        $FechaFinal = NewRequest::input('fechaHasta');

        $consultaTraspasos = \DB::select(

            \DB::raw(

                "SELECT TRS_TraspasoSolicitudId AS DT_RowId,TRS_CodigoSolicitud,CONVERT(VARCHAR(10),TRS_FechaSolicitud,103) AS TRS_FechaCreacion,
                (EMP_Nombre + ' ' + EMP_PrimerApellido + ' ' + EMP_SegundoApellido) AS EMP_NombreCompleto, RUT_Nombre
                FROM TraspasosSolicitudes
                LEFT JOIN Empleados ON TRS_EMP_CreadoPorId = EMP_EmpleadoId
                LEFT JOIN TransportesUnidades ON TRS_LOC_LocalidadOrigenId = TUN_LOC_LocalidadId
                LEFT JOIN Rutas ON RUT_TUN_TransporteUnidadId = TUN_TransporteUnidadId
                where (TRS_CMM_EstatusSolicitudId = '".\App\Mapeos\Controles\ControlesMaestrosMultiples::$CMM_EstatusSolicitudTraspaso_Recibido."'
                OR TRS_CMM_EstatusSolicitudId = '".\App\Mapeos\Controles\ControlesMaestrosMultiples::$CMM_EstatusSolicitudTraspaso_Recibo_Parcial."')
                AND CAST(TRS_FechaSolicitud AS DATE) BETWEEN '".$FechaInicio."' AND '".$FechaFinal."'
                ".(DataBaseSession::isPermisoCorporativo() ? "" : " AND TRS_LOC_LocalidadOrigenId IN (SELECT LOC_LocalidadId FROM Localidades WHERE LOC_ALM_AlmacenId IN (".DataBaseSession::getAlmacenesPorCediId()."))")."
                ORDER BY TRS_FechaSolicitud desc, TRS_CodigoSolicitud DESC"

            )

        );

        $ajaxData = array();
        $ajaxData['data'] = $consultaTraspasos;
        $ajaxData['options'] = array();
        return (json_encode($ajaxData));

        //return Response::json($consultaEmbarques);

    }

    public static function buscaTraspasosDetalle($id){

        $consultaTraspasosDetalle = \DB::select(

            \DB::raw(

                "SELECT ART_ArticuloId,ART_CodigoArticulo,ART_Nombre,CMUM_Nombre,TRS_CodigoSolicitud,
                CONVERT(VARCHAR(10),TRS_FechaCreacion,103) AS TRS_FechaCreacion,TRSD_Cantidad,SUM(TRAD_CantidadATraspasar) AS TRAD_CantidadATraspasar,
                LocalidadesOrigen.LOC_Nombre AS LOC_Origen, LocalidadesDestino.LOC_Nombre AS LOC_Destino, X.Suma
                FROM TraspasosSolicitudes
                INNER JOIN TraspasosSolicitudesDetalle ON  TRS_TraspasoSolicitudId = TRSD_TRS_TraspasoSolicitudId
                INNER JOIN TraspasosDetalle ON TRSD_DetalleId = TRAD_TRSD_DetalleId
                INNER JOIN(
                            SELECT TRAD_TRSD_DetalleId,SUM(TRAD_CantidadATraspasar) AS Suma
                            FROM TraspasosDetalle
                            INNER JOIN TraspasosSolicitudesDetalle ON TRAD_TRSD_DetalleId = TRSD_DetalleId
                            INNER JOIN TraspasosSolicitudes ON TRS_TraspasoSolicitudId = TRSD_TRS_TraspasoSolicitudId
                            WHERE TRS_TraspasoSolicitudId = '".$id."'
                            GROUP BY TRAD_TRSD_DetalleId
                        )AS X ON X.TRAD_TRSD_DetalleId = TRSD_DetalleId
                INNER JOIN Articulos ON TRSD_ART_ArticuloId = ART_ArticuloId
                INNER JOIN ControlesMaestrosUM ON CMUM_UnidadMedidaId = ART_CMUM_UMInventarioId
                INNER JOIN Localidades LocalidadesOrigen ON TRS_LOC_LocalidadOrigenId = LocalidadesOrigen.LOC_LocalidadId
                INNER JOIN Localidades LocalidadesDestino ON TRS_LOC_LocalidadDestinoId = LocalidadesDestino.LOC_LocalidadId
                WHERE TRS_TraspasoSolicitudId = '".$id."' AND X.Suma > 0
                GROUP BY ART_ArticuloId,ART_CodigoArticulo,ART_Nombre,CMUM_Nombre,TRS_CodigoSolicitud,
                TRS_FechaCreacion,TRSD_Cantidad,LocalidadesOrigen.LOC_Nombre, LocalidadesDestino.LOC_Nombre, X.Suma"

            )

        );

        return Response::json($consultaTraspasosDetalle);

    }

    public static function CancelarTraspasoGeneral($TRA_TraspasoId,$valor){

        \DB::beginTransaction();

        try {

            $val=$_POST['CCON_contador'];
            $val2=$_POST['CCON_contador2'];
            $codigoSolicitud = $_POST['TRS_CodigoSolicitud'];
            $arreglo = array();
            $TotalCantidadAjuste = 0;
            $banderaExistencias = true;

            for($x = 0; $x < $val; $x ++)
            {

                $arreglo[$x][0] = $_POST['Checkbox'.($x+1)];
                $arreglo[$x][1] = $_POST['ART_CodigoArticulo'.($x+1)];
                $arreglo[$x][2] = $_POST['ART_Nombre'.($x+1)];
                $arreglo[$x][3] = $_POST['CMUM_Nombre'.($x+1)];
                $arreglo[$x][4] = $_POST['EMB_FechaEmbarque'.($x+1)];
                $arreglo[$x][5] = $_POST['OVD_CantidadRequerida'.($x+1)];
                $arreglo[$x][6] = $_POST['TRAD_CantidadTraspasada'.($x+1)];
                $arreglo[$x][7] = $_POST['Cantidad'.($x+1)];
                $arreglo[$x][8] = $_POST['Diferencia'.($x+1)];
                $arreglo[$x][9] = $_POST['ComentariosPorPartida'.($x+1)];
                $arreglo[$x][10] = $_POST['ART_ArticuloId'.($x+1)];

                $obtenerTraspasoMvtoId = CancelarTraspasosController::buscaTraspasoMovtoId($TRA_TraspasoId,$_POST['ART_ArticuloId'.($x+1)]);
                //dd($_POST['Cantidad'.($x+1)]." > ".$obtenerTraspasoMvtoId[0]->TRAR_CantidadRecibo);
                if(count($obtenerTraspasoMvtoId)>1)//CONTAR CONSULTA DE TRASPASOS MOVIMIENTOS
                {

                    $cantidadRestante = $_POST['Cantidad'.($x+1)];
                    $cantidadRegistra = 0;
                    for($i = 0; $i < count($obtenerTraspasoMvtoId); $i++)//CICLO PARA RECORRER TRASPASOS MOVTOS.
                    {

                        if($cantidadRestante >= $obtenerTraspasoMvtoId[$i]->TRAR_CantidadRecibo)//COMPARAR SI LA CANTIDAD ES MAYOR
                        {

                            $cantidadRegistra =  $obtenerTraspasoMvtoId[$i]->TRAR_CantidadRecibo;
                            $cantidadRestante = $cantidadRestante - $cantidadRegistra;

                        }
                        else
                        {

                            $cantidadRegistra = $cantidadRestante;
                            $cantidadRestante = $cantidadRestante - $cantidadRegistra;

                        }

                        //localidad a restar
                        $obtenerTraspasoMvtoIdRecibo = TraspasosRecibos::find($obtenerTraspasoMvtoId[$i]->TRAR_TraspasoReciboId);
                        $buscaLoteYLocalidadIdRecibo = CancelarTraspasosController::buscaLoteYLocalidadId($obtenerTraspasoMvtoIdRecibo->TRAR_TRAM_TraspasoMovtoId);
                        $LoteIdRecibo = "";
                        if(count($buscaLoteYLocalidadIdRecibo) < 1)
                        {

                            $buscaLoclidadArticuloId = CancelarTraspasosController::buscaLocalidadArticuloId($obtenerTraspasoMvtoIdRecibo->TRAR_TRAM_TraspasoMovtoId);
                            $LocalidadIdRecibo = $buscaLoclidadArticuloId[0]->LOCA_LOC_LocalidadId;

                        }
                        else{

                            $LocalidadIdRecibo = $buscaLoteYLocalidadIdRecibo[0]->LOTL_LOC_LocalidadId;
                            $LoteIdRecibo = $buscaLoteYLocalidadIdRecibo[0]->LOTL_LOT_LoteId;

                        }

                        //VALIDAR SI HAY EXISTENCIAS EN EL LOTE SUFICIENTES PARA RESTAR Y NO QUEDE NEGATIVO
                        //dd($buscaLoteYLocalidadIdRecibo[0]->LOTL_Cantidad." *** ".$_POST['Cantidad'.($x+1)]);
                        if($buscaLoteYLocalidadIdRecibo[0]->LOTL_Cantidad >= $cantidadRegistra)
                        {

                            //localidad a sumar
                            $buscaLoteYLocalidadId = CancelarTraspasosController::buscaLoteYLocalidadId($obtenerTraspasoMvtoId[$i]->TRAD_TRAM_TraspasoMovtoId);
                            $LoteId = "";
                            if(count($buscaLoteYLocalidadId) < 1)
                            {

                                $buscaLoclidadArticuloId = CancelarTraspasosController::buscaLocalidadArticuloId($obtenerTraspasoMvtoId[$i]->TRAD_TRAM_TraspasoMovtoId);
                                $LocalidadId = $buscaLoclidadArticuloId[0]->LOCA_LOC_LocalidadId;

                            }
                            else{

                                $LocalidadId = $buscaLoteYLocalidadId[0]->LOTL_LOC_LocalidadId;
                                $LoteId = $buscaLoteYLocalidadId[0]->LOTL_LOT_LoteId;

                            }
                            $Articulo = ArticulosController::buscaPorId($_POST['ART_ArticuloId'.($x+1)]);
                            $CantidadPorAjustar = $cantidadRegistra;
                            $UnidadMedida = ArticulosController::buscaNombreUMInventarioPorArticuloId($_POST['ART_ArticuloId'.($x+1)]);
                            $MotivoAjuste = ControlesMaestrosMultiples::select('CMM_Valor')->where('CMM_ControlId','=',$_POST['TRS_CMM_MotivoDevolucionId'])->get();

                            $signo = "negativo";
                            $transferencia_id = CancelarTraspasosController::procesaTransferirArticulo($CantidadPorAjustar * -1,$_POST['ART_ArticuloId'.($x+1)],$MotivoAjuste[0]->CMM_Valor,$UnidadMedida[0]->CMUM_Nombre,$Articulo->ART_CantidadAMano,$codigoSolicitud,$valor);
                            CancelarTraspasosController::guardaCambios($transferencia_id,$x,$arreglo,$Articulo->ART_ArticuloId,$Articulo->ART_CantidadAMano,$Articulo->ART_SeguimientoLotMult,$LocalidadIdRecibo,$LoteIdRecibo,$signo);
                            CancelarTraspasosController::guardaTraspasosRecibo($obtenerTraspasoMvtoId[$i]->TRAD_TraspasoDetalleId,$transferencia_id,$CantidadPorAjustar,$obtenerTraspasoMvtoId[$i]->TRAR_TraspasoReciboId);

                            $signo = "positivo";
                            $transferencia_id2 = CancelarTraspasosController::procesaTransferirArticulo($CantidadPorAjustar,$_POST['ART_ArticuloId'.($x+1)],$MotivoAjuste[0]->CMM_Valor,$UnidadMedida[0]->CMUM_Nombre,$Articulo->ART_CantidadAMano,$codigoSolicitud,$valor);
                            CancelarTraspasosController::guardaCambios($transferencia_id2,$x,$arreglo,$Articulo->ART_ArticuloId,$Articulo->ART_CantidadAMano,$Articulo->ART_SeguimientoLotMult,$LocalidadId,$LoteId,$signo);
                            $ultimoTraspasoDetalleInsertado = CancelarTraspasosController::guardaTraspasosDetalle($obtenerTraspasoMvtoId[$i]->TRSD_DetalleId,$transferencia_id2,$CantidadPorAjustar,$_POST['TRS_CMM_MotivoDevolucionId'],$_POST['ComentariosPorPartida'.($x+1)]);

                            //$ultimoTraspasoDetalleInsertado = TraspasosDetalle::orderby('TRAD_FechaRegistro', 'DESC')->first()->TRAD_TraspasoDetalleId;
                            if($x == 0)
                            {

                                CancelarTraspasosController::guardaTraspasosDevoluciones($TRA_TraspasoId);

                            }
                            CancelarTraspasosController::guardaTraspasosDevolucionesDetalle($ultimoTraspasoDetalleInsertado);

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

                }
                else//PROCESO NORMAL
                {

                    //localidad a restar
                    $obtenerTraspasoMvtoIdRecibo = TraspasosRecibos::find($obtenerTraspasoMvtoId[0]->TRAR_TraspasoReciboId);
                    $buscaLoteYLocalidadIdRecibo = CancelarTraspasosController::buscaLoteYLocalidadId($obtenerTraspasoMvtoIdRecibo->TRAR_TRAM_TraspasoMovtoId);
                    $LoteIdRecibo = "";
                    if(count($buscaLoteYLocalidadIdRecibo) < 1)
                    {

                        $buscaLoclidadArticuloId = CancelarTraspasosController::buscaLocalidadArticuloId($obtenerTraspasoMvtoIdRecibo->TRAR_TRAM_TraspasoMovtoId);
                        $LocalidadIdRecibo = $buscaLoclidadArticuloId[0]->LOCA_LOC_LocalidadId;

                    }
                    else{

                        $LocalidadIdRecibo = $buscaLoteYLocalidadIdRecibo[0]->LOTL_LOC_LocalidadId;
                        $LoteIdRecibo = $buscaLoteYLocalidadIdRecibo[0]->LOTL_LOT_LoteId;

                    }

                    //VALIDAR SI HAY EXISTENCIAS EN EL LOTE SUFICIENTES PARA RESTAR Y NO QUEDE NEGATIVO
                    //dd($buscaLoteYLocalidadIdRecibo[0]->LOTL_Cantidad." *** ".$_POST['Cantidad'.($x+1)]);
                    if($buscaLoteYLocalidadIdRecibo[0]->LOTL_Cantidad >= $_POST['Cantidad'.($x+1)])
                    {

                        //localidad a sumar
                        $buscaLoteYLocalidadId = CancelarTraspasosController::buscaLoteYLocalidadId($obtenerTraspasoMvtoId[0]->TRAD_TRAM_TraspasoMovtoId);
                        $LoteId = "";
                        if(count($buscaLoteYLocalidadId) < 1)
                        {

                            $buscaLoclidadArticuloId = CancelarTraspasosController::buscaLocalidadArticuloId($obtenerTraspasoMvtoId[0]->TRAD_TRAM_TraspasoMovtoId);
                            $LocalidadId = $buscaLoclidadArticuloId[0]->LOCA_LOC_LocalidadId;

                        }
                        else{

                            $LocalidadId = $buscaLoteYLocalidadId[0]->LOTL_LOC_LocalidadId;
                            $LoteId = $buscaLoteYLocalidadId[0]->LOTL_LOT_LoteId;

                        }
                        $Articulo = ArticulosController::buscaPorId($_POST['ART_ArticuloId'.($x+1)]);
                        $CantidadPorAjustar = $_POST['Cantidad'.($x+1)];
                        $UnidadMedida = ArticulosController::buscaNombreUMInventarioPorArticuloId($_POST['ART_ArticuloId'.($x+1)]);
                        $MotivoAjuste = ControlesMaestrosMultiples::select('CMM_Valor')->where('CMM_ControlId','=',$_POST['TRS_CMM_MotivoDevolucionId'])->get();

                        $signo = "negativo";
                        $transferencia_id = CancelarTraspasosController::procesaTransferirArticulo($CantidadPorAjustar * -1,$_POST['ART_ArticuloId'.($x+1)],$MotivoAjuste[0]->CMM_Valor,$UnidadMedida[0]->CMUM_Nombre,$Articulo->ART_CantidadAMano,$codigoSolicitud,$valor);
                        CancelarTraspasosController::guardaCambios($transferencia_id,$x,$arreglo,$Articulo->ART_ArticuloId,$Articulo->ART_CantidadAMano,$Articulo->ART_SeguimientoLotMult,$LocalidadIdRecibo,$LoteIdRecibo,$signo);
                        CancelarTraspasosController::guardaTraspasosRecibo($obtenerTraspasoMvtoId[0]->TRAD_TraspasoDetalleId,$transferencia_id,$CantidadPorAjustar,$obtenerTraspasoMvtoId[0]->TRAR_TraspasoReciboId);

                        $signo = "positivo";
                        $transferencia_id2 = CancelarTraspasosController::procesaTransferirArticulo($CantidadPorAjustar,$_POST['ART_ArticuloId'.($x+1)],$MotivoAjuste[0]->CMM_Valor,$UnidadMedida[0]->CMUM_Nombre,$Articulo->ART_CantidadAMano,$codigoSolicitud,$valor);
                        CancelarTraspasosController::guardaCambios($transferencia_id2,$x,$arreglo,$Articulo->ART_ArticuloId,$Articulo->ART_CantidadAMano,$Articulo->ART_SeguimientoLotMult,$LocalidadId,$LoteId,$signo);
                        $ultimoTraspasoDetalleInsertado = CancelarTraspasosController::guardaTraspasosDetalle($obtenerTraspasoMvtoId[0]->TRSD_DetalleId,$transferencia_id2,$CantidadPorAjustar,$_POST['TRS_CMM_MotivoDevolucionId'],$_POST['ComentariosPorPartida'.($x+1)]);

                        //$ultimoTraspasoDetalleInsertado = TraspasosDetalle::orderby('TRAD_FechaRegistro', 'DESC')->first()->TRAD_TraspasoDetalleId;
                        if($x == 0)
                        {

                            CancelarTraspasosController::guardaTraspasosDevoluciones($TRA_TraspasoId);

                        }
                        CancelarTraspasosController::guardaTraspasosDevolucionesDetalle($ultimoTraspasoDetalleInsertado);

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

            }

            $valor = 'Valido';
            if($banderaExistencias)
            {

                CancelarTraspasosController::cambiarStatusTraspasoSolicitud($TRA_TraspasoId);

                //return Response::json(true);

                $mensaje = 'El Traspaso ha sido cancelado con éxito.';

                \DB::commit();

            }
            else
            {

                $valor = 'Error';
                $mensaje = 'No se puede hacer el Traspaso, no existe suficiente existencia para restar al lote.';

            }

            return ['Status' => $valor, 'Mensaje' => $mensaje];

        }
        catch (\Exception $e) {

            \DB::rollback();

            return ['Status' => 'Error', 'Mensaje' => 'No se cancelado el Traspaso. Ocurrió un error al realizar el proceso. <br><br><br>Error: <br>'.$e->getMessage()];

        }

    }

    public static function buscaTraspasoMovtoId($TRA_TraspasoId,$ART_ArticuloId){

        $TraspasoMvtoId = \DB::select(

            \DB::raw(

                "SELECT TRAD_TRAM_TraspasoMovtoId,TRSD_DetalleId,TRAD_TraspasoDetalleId,TRAR_TraspasoReciboId,TRAR_CantidadRecibo
                FROM TraspasosSolicitudesDetalle
                INNER JOIN TraspasosDetalle ON TRSD_DetalleId = TRAD_TRSD_DetalleId
                INNER JOIN TraspasosRecibos ON TRAD_TraspasoDetalleId = TRAR_TRAD_TraspasoDetalleId
                WHERE TRSD_TRS_TraspasoSolicitudId = '".$TRA_TraspasoId."'
                AND TRSD_ART_ArticuloId = '".$ART_ArticuloId."'"

            )

        );

        return $TraspasoMvtoId;

    }

    public static function buscaLoteYLocalidadId($TraspasoMvtoId){

        $LoteYLocalidadId = TraspasoMovto::select('LOTL_LOT_LoteId','LOTL_LOC_LocalidadId','LOTL_Cantidad')
            ->join('TraspasosLotes','TRAM_TraspasoMovtoId','=','TRLOT_TRAM_TraspasoMovtoId')
            ->join('LotesLocalidades','TRLOT_LOTL_LoteLocalidadId','=','LOTL_LoteLocalidadId')
            ->where('TRAM_TraspasoMovtoId','=',$TraspasoMvtoId)
            ->get();

        return $LoteYLocalidadId;

    }

    public static function buscaLocalidadArticuloId($TraspasoMvtoId){

        $LocalidadArticuloId = TraspasoMovto::select('LOCA_LOC_LocalidadId')
            ->join('TraspasosLocalidades','TRAM_TraspasoMovtoId','=','TRLOC_TRAM_TraspasoMovtoId')
            ->join('LocalidadesArticulo','TRLOC_LOCA_LocalidadArticuloId','=','LOCA_LocalidadArticuloId')
            ->where('TRAM_TraspasoMovtoId','=',$TraspasoMvtoId)
            ->get();

        return $LocalidadArticuloId;

    }

    public static function procesaTransferirArticulo($CantidadPorAjustar,$idArt,$MotivoAjuste,$UnidadMedida,$CantidadAMano,$codigoSolicitud,$valor){

        try{

            $tipoTransferencia = "";
            $referencia = "";
            if($valor == 2)//CANCELAR
            {

                $tipoTransferencia = '934C2C6F-0E97-479E-95EB-9556DC979B62';
                $referencia = "Cancelación de Traspaso de la solicitud: ".$codigoSolicitud;

            }
            elseif($valor == 1)//DEVOLVER
            {

                $tipoTransferencia = 'A3AD0ED0-8193-4311-8017-42A35D1277AE';
                $referencia = "Devolución de Traspaso de la solicitud: ".$codigoSolicitud;

            }

            $TraspasosMovtos = new TraspasoMovto();
            $TraspasosMovtos->TRAM_TraspasoMovtoId = self::getNuevoId();
            $TraspasosMovtos->TRAM_ART_ArticuloId = $idArt;
            $TraspasosMovtos->TRAM_CantidadATraspasar = $CantidadPorAjustar;
            $TraspasosMovtos->TRAM_CMM_TipoTransferenciaId = $tipoTransferencia;//DEVOLUCION DE TRASPASO
            $TraspasosMovtos->TRAM_Razon = $MotivoAjuste;
            $TraspasosMovtos->TRAM_Referencia=$referencia;
            $TraspasosMovtos->TRAM_UnidadMedidadArt = $UnidadMedida;
            $TraspasosMovtos->TRAM_EstatusContable = false;
            $TraspasosMovtos->TRAM_CantidadAMano = $CantidadAMano;
            $TraspasosMovtos->TRAM_CantidadAManoConTraspaso = $CantidadAMano + $CantidadPorAjustar;
            $TraspasosMovtos->save();

            //$ultimoinsertado = ProcesadorMovimientoInventarios::buscaIdUltimoInsertado();
            $ultimoinsertado = $TraspasosMovtos->TRAM_TraspasoMovtoId;

            return $ultimoinsertado;

        }
        catch(\Exception $e){

            throw $e;

        }

    }

    public static function guardaTraspasosRecibo($TRAD_TraspasoDetalleId,$transferencia_id,$CantidadPorAjustar,$TRAR_TraspasoReciboId){

        try{

            //date_default_timezone_set('America/Mexico_City');
            $hoy=date('d/m/Y H:i:s');
            $TraspasoRecibo = new TraspasosRecibos();
            $TraspasoRecibo->TRAR_TRAD_TraspasoDetalleId = $TRAD_TraspasoDetalleId;
            $TraspasoRecibo->TRAR_TRAM_TraspasoMovtoId = $transferencia_id;
            $TraspasoRecibo->TRAR_CantidadRecibo = $CantidadPorAjustar * -1;
            $TraspasoRecibo->TRAR_ReferenciaReciboId = $TRAR_TraspasoReciboId;
            $TraspasoRecibo->TRAR_FechaRecibo = $hoy;
            $TraspasoRecibo->save();

        }
        catch(\Exception $e){

            throw $e;

        }

    }

    public static function guardaTraspasosDetalle($TRSD_DetalleId,$transferencia_id,$CantidadPorAjustar,$MotivoDevolucion,$TRAD_Comentarios){

        try{

            //date_default_timezone_set('America/Mexico_City');
            $hoy=date('d/m/Y H:i:s');

            $TraspasoDetalle = new TraspasosDetalle();
            $TraspasoDetalle->TRAD_TraspasoDetalleId = self::getNuevoId();
            $TraspasoDetalle->TRAD_TRSD_DetalleId = $TRSD_DetalleId;
            $TraspasoDetalle->TRAD_CantidadATraspasar = $CantidadPorAjustar * -1;
            $TraspasoDetalle->TRAD_TRAM_TraspasoMovtoId = $transferencia_id;
            $TraspasoDetalle->TRAD_CMM_MotivoDevolucionId = $MotivoDevolucion;
            $TraspasoDetalle->TRAD_Comentarios = $TRAD_Comentarios;
            $TraspasoDetalle->TRAD_FechaLote = $hoy;
            $TraspasoDetalle->TRAD_FechaTraspaso = $hoy;
            $TraspasoDetalle->save();

            $ultimoInsertado = $TraspasoDetalle->TRAD_TraspasoDetalleId;

            return $ultimoInsertado;

        }
        catch(\Exception $e){

            throw $e;

        }

    }

    public static function guardaCambios($transferencia_id,$x,$arreglo,$ART_ArticuloId,$CantidadAMano,$ART_SeguimientoLotMult,$LocalidadId,$LoteId,$signo){

        try{

            if($signo == "negativo")
            {

                $TotalCantidadAjuste=$arreglo[$x][7] * -1;//cantidadajuste
                $CantidadPorAjustar=$arreglo[$x][7] * -1;//cantidadajuste

            }
            else
            {

                $TotalCantidadAjuste=$arreglo[$x][7];//cantidadajuste
                $CantidadPorAjustar=$arreglo[$x][7];//cantidadajuste

            }

            $codigoLocalidad = LocalidadesController::buscaCodigoLocalidad($LocalidadId);//localidadId
            CancelarTraspasosController::asignarInventarioEnLocalidad($TotalCantidadAjuste,$ART_ArticuloId,$LocalidadId,$CantidadPorAjustar,$codigoLocalidad,$transferencia_id,$CantidadAMano);//articuloId, localidadId
            if($ART_SeguimientoLotMult == 1)
            {

                if($LoteId != "")
                {

                    $codigoLote = CancelarTraspasosController::buscaCodigoLotePorId($LoteId);
                    CancelarTraspasosController::asignarInventarioEnLote($codigoLote[0]->LOT_CodigoLote,$TotalCantidadAjuste,$ART_ArticuloId,$LocalidadId,$CantidadPorAjustar,$codigoLocalidad,$transferencia_id,$CantidadAMano);//codigoLote, articuloId, localidadId

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
                $loc_art->LOCA_Cantidad = $CantidadPorAjustar;
                $loc_art->save();
                $loc_art = LocalidadesArticuloController::buscaIdUltimoInsertado();
                $loc_art[0]->LOCA_Cantidad = 0;
            }
            else if($loc_art[0]->LOCA_Eliminado == 1)
            {

                $nuevaCantidadPorAjustar = $loc_art[0]->LOCA_Cantidad + $CantidadPorAjustar;
                LocalidadesArticuloController::restauraPorId($loc_art,$nuevaCantidadPorAjustar);

            }
            else
            {

                $nuevaCantidadPorAjustar = $loc_art[0]->LOCA_Cantidad+$CantidadPorAjustar;
                LocalidadesArticuloController::actualizaPorId($loc_art,$nuevaCantidadPorAjustar);

            }
            $trans_localidad = new TraspasosLocalidades();
            $trans_localidad->TRLOC_TRAM_TraspasoMovtoId = $transferencia_id;
            $trans_localidad->TRLOC_LOCA_LocalidadArticuloId = $loc_art[0]->LOCA_LocalidadArticuloId;
            $trans_localidad->TRLOC_CantidadTransferida = $CantidadPorAjustar;
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
                $loteloca->LOTL_Cantidad = $CantidadPorAjustar;
                $loteloca->save();
                $loteloca = LotesLocalidadesController::buscaIdUltimoInsertado();
                $loteloca[0]->LOTL_Cantidad = 0;

            }
            else if($loteloca[0]->LOTL_Eliminado == 1)
            {

                $nuevaCantidadPorAjustar = $loteloca[0]->LOTL_Cantidad + $CantidadPorAjustar;
                LotesLocalidadesController::restauraPorId($loteloca,$nuevaCantidadPorAjustar);

            }
            else
            {

                $nuevaCantidadPorAjustar = $loteloca[0]->LOTL_Cantidad + $CantidadPorAjustar;
                LotesLocalidadesController::actualizaPorId($loteloca,$nuevaCantidadPorAjustar);

            }
            $trans_lotes = new TraspasosLotes();
            $trans_lotes->TRLOT_TRAM_TraspasoMovtoId = $transferencia_id;
            $trans_lotes->TRLOT_LOTL_LoteLocalidadId = $loteloca[0]->LOTL_LoteLocalidadId;
            $trans_lotes->TRLOT_CantidadTraspaso = $CantidadPorAjustar;
            $trans_lotes->TRLOT_CantidadAMano = $CantidadAMano;
            $trans_lotes->TRLOT_CantidadAnteriorLote = $loteloca[0]->LOTL_Cantidad;
            $trans_lotes->save();

        }
        catch(\Exception $e){

            throw $e;

        }

    }

    public static function cambiarStatusTraspasoSolicitud($TRA_TraspasoId){

        try{

            $sumaRecibos = \DB::select(

                \DB::raw(

                    "SELECT SUM(TRAR_CantidadRecibo) AS Suma FROM TraspasosRecibos
                    INNER JOIN TraspasosDetalle ON TRAR_TRAD_TraspasoDetalleId = TRAD_TraspasoDetalleId
                    INNER JOIN TraspasosSolicitudesDetalle ON TRAD_TRSD_DetalleId = TRSD_DetalleId
                    INNER JOIN TraspasosSolicitudes ON TRS_TraspasoSolicitudId = TRSD_TRS_TraspasoSolicitudId
                    WHERE TRS_TraspasoSolicitudId = '".$TRA_TraspasoId."'"

                )

            );

            if($sumaRecibos[0]->Suma == 0)
            {

                $suma = \DB::select(

                    \DB::raw(

                        "SELECT SUM(TRAD_CantidadATraspasar) AS Suma FROM TraspasosDetalle
                        INNER JOIN TraspasosSolicitudesDetalle ON TRSD_DetalleId = TRAD_TRSD_DetalleId
                        INNER JOIN TraspasosSolicitudes ON TRS_TraspasoSolicitudId = TRSD_TRS_TraspasoSolicitudId
                        WHERE TRS_TraspasoSolicitudId = '".$TRA_TraspasoId."'"

                    )

                );

                if($suma[0]->Suma == 0)
                {

                    //solicitado
                    CancelarTraspasosController::cambiarStatus($TRA_TraspasoId,\App\Mapeos\Controles\ControlesMaestrosMultiples::$CMM_EstatusSolicitudTraspaso_Solicitado);

                }
                else if($suma[0]->Suma > 0)
                {

                    //traspaso parcial
                    CancelarTraspasosController::cambiarStatus($TRA_TraspasoId,\App\Mapeos\Controles\ControlesMaestrosMultiples::$CMM_EstatusSolicitudTraspaso_Traspaso_Parcial);

                }


            }
            elseif($sumaRecibos[0]->Suma > 0)
            {

                //reciboparcial
                CancelarTraspasosController::cambiarStatus($TRA_TraspasoId,\App\Mapeos\Controles\ControlesMaestrosMultiples::$CMM_EstatusSolicitudTraspaso_Recibo_Parcial);

            }

        }
        catch(\Exception $e){

            throw $e;

        }

    }

    public static function cambiarStatus($TRA_TraspasoId,$Status){

        try{

            //date_default_timezone_set('America/Mexico_City');
            $hoy=date('d/m/Y H:i:s');
            \DB::table('TraspasosSolicitudes')->where('TRS_TraspasoSolicitudId', '=', $TRA_TraspasoId)
                ->update(
                    array(
                        'TRS_CMM_EstatusSolicitudId' => $Status,
                        'TRS_FechaUltimaModificacion' => $hoy
                    )
                );

        }
        catch(\Exception $e){

            throw $e;

        }

    }

    public static function guardaTraspasosDevoluciones($TRA_TraspasoId){

        try{

            $consultaUltimCodigo = TraspasosDevoluciones::orderby('TRD_FechaDevolucion', 'DESC')->get();//->first()->TRD_CodigoDevolucion;
            $cuenta = count($consultaUltimCodigo);
            if($cuenta <= 0)
            {

                $ultimoCodigoInsertado = "00001";

            }
            else
            {

                $ultimoCodigoInsertado = $consultaUltimCodigo[0]->TRD_CodigoDevolucion + 1;

            }

            $TraspasosDevoluciones = new TraspasosDevoluciones();
            $TraspasosDevoluciones->TRD_CodigoDevolucion = $ultimoCodigoInsertado;
            $TraspasosDevoluciones->TRD_TRS_TraspasoSolicitudId = $TRA_TraspasoId;
            //$TraspasosDevoluciones->ED_EMP_CreadoPorId = "";
            $TraspasosDevoluciones->TRD_Estatus = 1;
            $TraspasosDevoluciones->save();

        }
        catch(\Exception $e){

            throw $e;

        }

    }

    public static function guardaTraspasosDevolucionesDetalle($TRAD_TraspasoDetalleId){

        try{

            $ultimoTraspasoDevolucionesInsertado = TraspasosDevoluciones::orderby('TRD_FechaDevolucion', 'DESC')->first()->TRD_TraspasoDevolucionId;

            $TraspasosDevolucionesDetalle = new TraspasosDevolucionesDetalle();
            $TraspasosDevolucionesDetalle->TRDD_TRD_TraspasoDevolucionId = $ultimoTraspasoDevolucionesInsertado;
            $TraspasosDevolucionesDetalle->TRDD_TRAD_TraspasoDetalleId = $TRAD_TraspasoDetalleId;
            $TraspasosDevolucionesDetalle->save();

        }
        catch(\Exception $e){

            throw $e;

        }

    }

    public static function getNuevoId()
    {
        if (function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

}
