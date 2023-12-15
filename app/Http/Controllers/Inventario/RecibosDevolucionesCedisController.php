<?php namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Sistema\DataBaseSession;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as NewRequest;
use Carbon\Carbon;
use App\Http\Controllers\Embarques\EmbarquesController;
use App\Http\Controllers\Inventario\Articulos\ArticulosController;
use App\Mapeos\Controles\ControlesMaestrosMultiples;
use App\Models\Articulos;
use App\Models\Inventario\Articulos\Localidad;
use App\Models\Inventario\InventarioFisico\DetallesMovimientoInventario;
use App\Models\Inventario\InventarioFisico\ProcesadorMovimientoInventarios;
use App\Models\Inventario\InventarioFisico\TraspasoMovto;
use App\Models\Localidades;
use App\Models\Lotes;
use App\Models\Traspasos;
use App\Models\TraspasosDetalle;
use App\Models\TraspasosRecibos;
use App\Models\TraspasosSolicitudes;
use App\Models\TraspasosSolicitudesDetalle;
use Response;

class RecibosDevolucionesCedisController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{

        $almacenes = array(''=>'Selecciona Alamacen') + Localidad::select('LOC_LocalidadId','ALM_Nombre')
                ->join('Almacenes', 'ALM_AlmacenId', '=', 'LOC_ALM_AlmacenId')
                ->where('LOC_LocalidadId','=',DataBaseSession::getLocalidadGeneralId())
                ->orderby('ALM_Nombre','ASC')
                ->lists('ALM_Nombre','LOC_LocalidadId')->all();

        $almacenesLocalidades = array(''=>'Selecciones Almacen/Localidad') + Localidad::select('LOC_LocalidadId', \DB::raw("ALM_Nombre + (CASE WHEN LOC_LocalidadGeneral = 0 THEN ' - ' + LOC_Nombre ELSE '' END) AS FULL_NAME"))
                ->join('Almacenes','ALM_AlmacenId','=','LOC_ALM_AlmacenId')
                ->whereRaw(" ALM_AlmacenId IN (".DataBaseSession::getAlmacenesPorCediId().")")
                ->lists('FULL_NAME','LOC_LocalidadId')->all();

        return view('inventario.recibosdevolucionescedis.create', compact('almacenesLocalidades', 'almacenes'));

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

        \DB::beginTransaction();

        try {

            $jsonTraspasos = NewRequest::input('Traspasos');
            //$form = Request::input('formulario');
            $fecharecibo = NewRequest::input('fecha');
            $comentarios = NewRequest::input('comentarios');

            $CantidadPorTraspasar = 0;
            $AlmacenId = "";
            $LocalidadId = "";
            $TipoOperacion = "";
            $Traspaso = \DB::select(\DB::raw("SELECT * FROM Traspasos WHERE TRA_TraspasoId = '".$id."'"));

            $articuloAnterior = '';
            $TraspasoMovtoAnterior = '';
            $cuentaJson = count($jsonTraspasos);
            for($x = 0; $x < $cuentaJson; $x++)
            {

                if($articuloAnterior == ''){

                    $articuloAnterior = $jsonTraspasos[$x][1];
                    $CantidadPositiva= $jsonTraspasos[$x][0];
                    $ArticuloId = $jsonTraspasos[$x][1];

                    $UnidadMedida = ArticulosController::buscaNombreUMInventarioPorArticuloId($ArticuloId);
                    $Articulo = ArticulosController::buscaPorId($ArticuloId);

                    //REGISTRA TRASPASOS MOVTOS POR ARTICULO (POSITIVO)
                    $CantidadPorTraspasar = $CantidadPositiva;
                    $LocalidadId = 'AD69F54E-D428-4960-B721-EB48CC3B6C3F';//LOCALIDAD GENERAL MATRIZ
                    $AlmacenOrigenId = \DB::select(\DB::raw("SELECT * FROM Localidades WHERE LOC_LocalidadId = '".$LocalidadId."' AND LOC_Eliminado = 0"));
                    $AlmacenId = $AlmacenOrigenId[0]->LOC_LocalidadId;
                    $TipoOperacion = "Positivo";
                    $TraspasoId = $id;
                    $Codigo_DevolucionCedi = $Traspaso[0]->TRA_CodigoTraspaso;
                    $trapasoMovtoId = RecibosDevolucionesCedisController::procesaTransferirArticulo($TipoOperacion,$TraspasoId,$jsonTraspasos,$AlmacenId,$LocalidadId,$Codigo_DevolucionCedi,$CantidadPorTraspasar,$ArticuloId,$UnidadMedida[0]->CMUM_Nombre,$Articulo->ART_CodigoArticulo,$Articulo->ART_CantidadAMano);
                    $TraspasoMovtoAnterior = $trapasoMovtoId;

                    $ActualizaTraspaso = Traspasos::find($TraspasoId);
                    $ActualizaTraspaso->TRA_CMM_EstadoTraspasoId = '1FE8CD4A-2051-4A43-8491-BEAFC5D427BB';
                    $ActualizaTraspaso->save();

                }
                else{

                    if($articuloAnterior != $jsonTraspasos[$x][1]){

                        $articuloAnterior = $jsonTraspasos[$x][1];
                        $CantidadPositiva= $jsonTraspasos[$x][0];
                        $ArticuloId = $jsonTraspasos[$x][1];

                        $UnidadMedida = ArticulosController::buscaNombreUMInventarioPorArticuloId($ArticuloId);
                        $Articulo = ArticulosController::buscaPorId($ArticuloId);

                        //REGISTRA TRASPASOS MOVTOS POR ARTICULO (POSITIVO)
                        $CantidadPorTraspasar = $CantidadPositiva;
                        $LocalidadId = 'AD69F54E-D428-4960-B721-EB48CC3B6C3F';//LOCALIDAD GENERAL MATRIZ
                        $AlmacenOrigenId = \DB::select(\DB::raw("SELECT * FROM Localidades WHERE LOC_LocalidadId = '".$LocalidadId."' AND LOC_Eliminado = 0"));
                        $AlmacenId = $AlmacenOrigenId[0]->LOC_LocalidadId;
                        $TipoOperacion = "Positivo";
                        $TraspasoId = $id;
                        $Codigo_DevolucionCedi = $Traspaso[0]->TRA_CodigoTraspaso;
                        $trapasoMovtoId = RecibosDevolucionesCedisController::procesaTransferirArticulo($TipoOperacion,$TraspasoId,$jsonTraspasos,$AlmacenId,$LocalidadId,$Codigo_DevolucionCedi,$CantidadPorTraspasar,$ArticuloId,$UnidadMedida[0]->CMUM_Nombre,$Articulo->ART_CodigoArticulo,$Articulo->ART_CantidadAMano);
                        $TraspasoMovtoAnterior = $trapasoMovtoId;

                        $ActualizaTraspaso = Traspasos::find($TraspasoId);
                        $ActualizaTraspaso->TRA_CMM_EstadoTraspasoId = '1FE8CD4A-2051-4A43-8491-BEAFC5D427BB';
                        $ActualizaTraspaso->save();

                    }
                    else{

                        //ACTUALIZA TRASPASOS MOVTOS (POSITIVO)
                        $CantidadPositiva = $jsonTraspasos[$x][0];
                        RecibosDevolucionesCedisController::actualizaTraspasoMovto($TraspasoMovtoAnterior,$CantidadPositiva);

                    }

                }

            }

            //return json_encode(array());

            $mensaje = 'La transacción fue realizada exitosamente.';

            \DB::commit();

            return ['Status' => 'Valido', 'Mensaje' => $mensaje];

        }
        catch (\Exception $e) {

            \DB::rollback();

            return ['Status' => 'Error', 'Mensaje' => 'No se registró el Recibo de Traspaso. Ocurrió un error al realizar el proceso. Error: '.$e->getMessage()];

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

    public function ajaxResponseListado(){

        $consulta = \DB::select(

            \DB::raw(

                "SELECT DISTINCT
                    TRA_TraspasoId as DT_RowId,
                    TRA_CodigoTraspaso,
                    TRA_FechaTraspaso,
                    tipo.CMM_Valor AS TIPO,
                    estatus.CMM_Valor AS ESTATUS,
                    DEP_Nombre AS CEDIS
                FROM Traspasos
                LEFT JOIN Departamentos ON TRA_DEP_DepartamentoId = DEP_DeptoId
                INNER JOIN ControlesMaestrosMultiples tipo ON TRA_CMM_TipoTraspasoId = tipo.CMM_ControlId
                INNER JOIN ControlesMaestrosMultiples estatus ON TRA_CMM_EstadoTraspasoId = estatus.CMM_ControlId
                WHERE TRA_CMM_TipoTraspasoId = '52CF87B2-5FF9-44D5-980B-7CD25C2ADCF6'
                AND TRA_CMM_EstadoTraspasoId = 'F609E2E4-2E68-4F83-B03A-5FE549D02490'
                AND TRA_FechaTraspaso >= convert(char(6), dateadd(month, -1, getdate()), 112) + '01'
                "/*.(DataBaseSession::isPermisoCorporativo() ? "" : " AND TRA_TraspasoId IN (
                            SELECT TRAM_TRA_TraspasoId FROM TraspasosMovtos
                                                    INNER JOIN TraspasosLocalidades ON TRAM_TraspasoMovtoId = TRLOC_TRAM_TraspasoMovtoId
                                                    INNER JOIN LocalidadesArticulo ON LOCA_LocalidadArticuloId = TRLOC_LOCA_LocalidadArticuloId
                                                    INNER JOIN Localidades ON LOC_LocalidadId = LOCA_LOC_LocalidadId
                                                    WHERE TRAM_TRA_TraspasoId IS NOT NULL AND LOC_ALM_AlmacenId IN (".DataBaseSession::getAlmacenesPorCediId().")
                                                )
                            ")*/."
                ORDER BY TRA_FechaTraspaso desc, TRA_CodigoTraspaso"

            )

        );

        $ajaxData = array();
        $ajaxData['data'] = $consulta;    
        $ajaxData['options'] = array();

        return (json_encode($ajaxData));

    }

    public function consultarDetalleTraspasoRT2($TRA_TraspasoId){

        $consultaDetallesSolicitudTraspaso = \DB::select(
            \DB::raw(
                "SELECT
                    ART_CodigoArticulo, ART_Nombre, CMUM_Nombre, LOT_CodigoLote,
                    CAST(ISNULL(LOCA_Cantidad,0)AS INT) AS Existencia_Localidad,
                    CAST(ISNULL(LOTL_Cantidad,0)AS INT) AS Existencia_Lote,
                    DC_CANTIDADDEVUELTA AS CANTIDAD_DEVUELTA,
                    LOT_LoteId,CMM_Valor,ART_ArticuloId
                FROM Traspasos
                INNER JOIN DevolucionesCedis on DC_TRA_TraspasoId = TRA_TraspasoId
                INNER JOIN Articulos ON ART_ArticuloId = DC_ART_ArticuloId
                INNER JOIN Localidades ON LOC_LocalidadId = '".DataBaseSession::getLocalidadGeneralId()."'
                INNER JOIN Lotes ON LOT_LoteId = DC_LOT_LOTEID
                LEFT JOIN LocalidadesArticulo ON LOCA_ART_ArticuloId = ART_ArticuloId AND LOCA_LOC_LocalidadId = LOC_LocalidadId
                LEFT JOIN LotesLocalidades ON LOTL_LOT_LoteId = LOT_LoteId AND LOC_LocalidadId = LOTL_LOC_LocalidadId
                INNER JOIN ControlesMaestrosUM ON CMUM_UnidadMedidaId = ART_CMUM_UMInventarioId
                INNER JOIN ControlesMaestrosMultiples ON CMM_ControlId = TRA_CMM_EstadoTraspasoId
                WHERE TRA_TraspasoId = '".$TRA_TraspasoId."'
                ORDER BY
                    ART_CodigoArticulo ASC"
            )
        );

        return Response::json($consultaDetallesSolicitudTraspaso);

    }

    public function actualizaTraspasoMovto($TraspasoMovtoAnterior,$CantidadPositiva){

        $TraspasosMovtos= TraspasoMovto::find($TraspasoMovtoAnterior);
        $TraspasosMovtos->TRAM_CantidadATraspasar = floatval($TraspasosMovtos->TRAM_CantidadATraspasar) + floatval($CantidadPositiva);
        $TraspasosMovtos->TRAM_CantidadAMano = floatval($TraspasosMovtos->TRAM_CantidadAMano + $CantidadPositiva);
        $TraspasosMovtos->TRAM_CantidadAManoConTraspaso = floatval($TraspasosMovtos->TRAM_CantidadAManoConTraspaso + $CantidadPositiva);
        $TraspasosMovtos->save();

    }

    public function procesaTransferirArticulo($TipoOperacion,$TraspasoId,$objeto,$AlmacenId,$LocalidadId,$Codigo_Traspaso,$CantidadPorTraspasar,$idArt,$UnidadMedida,$CodigoArticulo,$CantidadAMano){
        try{

            $TraspasosMovtos=new TraspasoMovto();
            $TraspasosMovtos->TRAM_TraspasoMovtoId=self::getNuevoId();
            $TraspasosMovtos->TRAM_TRA_TraspasoId=$TraspasoId;
            $TraspasosMovtos->TRAM_ART_ArticuloId=$idArt;
            $TraspasosMovtos->TRAM_CantidadATraspasar=$CantidadPorTraspasar;
            $TraspasosMovtos->TRAM_Razon="Recibo de la Devolucion de Cedi con código: ".$Codigo_Traspaso;
            $TraspasosMovtos->TRAM_Referencia="Recibo de la Devolucion de Cedi del Articulo: ".$CodigoArticulo." Cantidad: ".$CantidadPorTraspasar;
            $TraspasosMovtos->TRAM_UnidadMedidadArt=$UnidadMedida;
            $TraspasosMovtos->TRAM_EstatusContable=false;
            $TraspasosMovtos->TRAM_CantidadAMano=$CantidadAMano;
            $TraspasosMovtos->TRAM_CantidadAManoConTraspaso=$CantidadAMano+$CantidadPorTraspasar;
            $TraspasosMovtos->TRAM_CMM_TipoTransferenciaId='4FDCBC10-5D9B-43BC-92E7-34B64930EBD1';//RECIBO DEV. CEDI
            $TraspasosMovtos->save();

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

            return $TraspasosMovtos->TRAM_TraspasoMovtoId;

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
