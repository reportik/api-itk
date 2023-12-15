<?php namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Sistema\DataBaseSession;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Carbon\Carbon;
use Illuminate\Support\Facades\Facade;
use App\Http\Controllers\Embarques\EmbarquesController;
use App\Http\Controllers\Inventario\Articulos\ArticulosController;
use App\Http\Controllers\inventario\Localidades\LocalidadesArticuloController;
use App\Http\Controllers\Sistema\AutonumericoController;
use Illuminate\Support\Facades\Request as NewRequest;
use App\Mapeos\Controles\ControlesMaestrosMultiples;
use App\Models\Articulos;
use App\Models\ControlesMaestros;
use App\Models\DevolucionesCedis;
use App\Models\Inventario\Articulos\Almacen;
use App\Models\Inventario\Articulos\Articulo;
use App\Models\Inventario\Articulos\CMMult;
use App\Models\Inventario\InventarioFisico\DetallesMovimientoInventario;
use App\Models\Inventario\InventarioFisico\ProcesadorMovimientoInventarios;
use App\Models\Inventario\InventarioFisico\TraspasoMovto;
use App\Models\inventario\Localidad;
use App\Models\inventario\TraspasosLocalidades;
use App\Models\Localidades;
use App\Models\Lotes;
use App\Models\Traspasos;
use App\Models\TraspasosDetalle;
use App\Models\TraspasosSolicitudes;
use App\Models\TraspasosSolicitudesDetalle;
use Response;

class DevolucionesCedisController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{

        $encabezados =array(
            'Id',
            'Codigo',
            'Status',
            'Imprimir'
        );

        $contenidos=array(
            'TRA_TraspasoId',
            'TRA_CodigoTraspaso',
            'CMM_Valor',
            "BTN_HTML"
        );

        /*
        $results=\DB::table('Traspasos')
            ->join('ControlesMaestrosMultiples','CMM_ControlId','=','TRA_CMM_EstadoTraspasoId')
            ->where('TRA_CMM_TipoTraspasoId','=','52CF87B2-5FF9-44D5-980B-7CD25C2ADCF6')//Devolucion CEDI
            ->select($contenidos.\DB::raw("'NULL' AS COL_NULL"))
            ->get();
        */

        $HTML_AFTER = '<center><a href=javascript:Generar_Reporte("';
        $HTML_BEFORE = '"); class="btn btn-info m-r-5"><i class="fa fa-print"></i></a></center>';

        $results = \DB::select(\DB::raw("
            SELECT
                TRA_TraspasoId,
                TRA_CodigoTraspaso,
                CMM_Valor,
               '$HTML_AFTER'+
               CAST(TRA_TraspasoId AS VARCHAR(40))
               +'$HTML_BEFORE' AS BTN_HTML

            FROM Traspasos
            INNER JOIN ControlesMaestrosMultiples ON CMM_ControlId = TRA_CMM_EstadoTraspasoId
            WHERE TRA_CMM_TipoTraspasoId = '52CF87B2-5FF9-44D5-980B-7CD25C2ADCF6'"
            .(DataBaseSession::isPermisoCorporativo() ? "" : " AND TRA_TraspasoId IN (
                                    SELECT TRAM_TRA_TraspasoId FROM TraspasosMovtos
                                    INNER JOIN TraspasosLocalidades ON TRAM_TraspasoMovtoId = TRLOC_TRAM_TraspasoMovtoId
                                    INNER JOIN LocalidadesArticulo ON LOCA_LocalidadArticuloId = TRLOC_LOCA_LocalidadArticuloId
                                    INNER JOIN Localidades ON LOC_LocalidadId = LOCA_LOC_LocalidadId
                                    WHERE TRAM_TRA_TraspasoId IS NOT NULL AND LOC_ALM_AlmacenId IN (".DataBaseSession::getAlmacenesPorCediId().")
                                )
        ")));

        $localidades = array(''=>'Localidad') +
            Localidades::whereRaw('LOC_Eliminado =0'.(DataBaseSession::isPermisoCorporativo() ? "" : " AND LOC_ALM_AlmacenId IN(".DataBaseSession::getAlmacenesPorCediId().")"))->orderby('LOC_Nombre','ASC')->lists('LOC_Nombre','LOC_LocalidadId')->all();

        $articulos = array(''=>'Productos') + Articulos::orderby('ART_CodigoArticulo','ASC')->lists('ART_Nombre','ART_ArticuloId')->all();

        $motivos = array(''=>'Motivo') + CMMult::where('CMM_Control','=','CMM_MotivoDevolucionCEDI')->where('CMM_Eliminado','=',0)->orderby('CMM_Valor','ASC')->lists('CMM_Valor','CMM_ControlId')->all();

        return view('inventario.devolucionescedis.create', compact('encabezados','contenidos','results','articulos', 'localidades','motivos'));

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

    public function Get_Productos(){

        //$LocalidadId = NewRequest::input('idProducto');

        $Articulos = \DB::select(\DB::raw("
            SELECT DISTINCT (ART_CodigoArticulo + ' - ' + ART_Nombre) AS ART_Nombre, ART_ArticuloId
            FROM LocalidadesArticulo
            INNER JOIN Articulos ON LOCA_ART_ArticuloId = ART_ArticuloId
            INNER JOIN Localidades ON LOCA_LOC_LocalidadId = LOC_LocalidadId
            --INNER JOIN Almacenes ON LOC_ALM_AlmacenId = ALM_AlmacenId
            WHERE LOC_LocalidadId = '".DataBaseSession::getLocalidadGeneralId()."' AND LOC_Eliminado = 0 AND ART_Eliminado = 0
            ORDER BY ART_Nombre"));

        return $Articulos;
    }

    public function Get_DatosTabla(){

        $where = '';

        //$IdLocalidad = NewRequest::input('idLocalidad');
        $IdProductos = NewRequest::input('idProductos');
        $IdLocalidad = \DB::select(\DB::raw("SELECT * FROM Localidades WHERE LOC_LocalidadId = '".DataBaseSession::getLocalidadGeneralId()."' AND LOC_Eliminado = 0"));


        if($IdLocalidad[0]->LOC_LocalidadId != 'All'){

            $where = "WHERE LOTL_LOC_LocalidadId = '". $IdLocalidad[0]->LOC_LocalidadId ."'";
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
            AND LOTL_Cantidad > 0
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
            //$LocalidadOrigenId = NewRequest::input('locOrigen');
            $motivo = NewRequest::input('motivo');
            $Comentarios = NewRequest::input('comentarios');

            //GENERAR AUTONUMERICO
            $autonumerico_dao = new AutonumericoController();
            $clienteId = null;
            $empleadoId = null;
            $autonumerico_id = self::establecerAutonumerico('CM_DC_SiguienteDevolucionCedi', null);
            $Codigo_DevolucionCedi = $autonumerico_dao->getSiguienteAutonumericoPorId($autonumerico_id);

            $TraspasoId = self::getNuevoId();
            //INERTAR EL TRASPASO
            \DB::table('Traspasos')->insert(
                array(
                    'TRA_TraspasoId' => $TraspasoId,
                    'TRA_CodigoTraspaso' => $Codigo_DevolucionCedi,
                    'TRA_CMM_TipoTraspasoId' => '52CF87B2-5FF9-44D5-980B-7CD25C2ADCF6',//dev. CEDI
                    'TRA_CMM_EstadoTraspasoId' => 'F609E2E4-2E68-4F83-B03A-5FE549D02490',//status en transito
                    'TRA_CMM_MotivoTraspasoId' => $motivo,
                    'TRA_Comentario' => $Comentarios,
                    'TRA_DEP_DepartamentoId' => DataBaseSession::getCediAsignadoId()
                )
            );

            //CONSULTAR ULTIMO TRASPASO INSERTADO
            //$TraspasoId = Traspasos::orderby('TRA_FechaTraspaso', 'DESC')->first()->TRA_TraspasoId;

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
                $bandera = true;
                //INICIA PROCESADOR DE INVENTARIO
                if($cuentaArrayArticulo > 0)
                {

                    for($x = 0; $x < $cuentaArrayArticulo; $x ++)
                    {

                        $CantidadPorTraspasar = 0;
                        $AlmacenId = "";
                        $LocalidadId = "";
                        $TipoOperacion = "";
                        $CantidadPositiva = $ArrayArticulo[$x]['cantidad'] * 1;
                        $CantidadNegativa = $ArrayArticulo[$x]['cantidad'] * -1;
                        $ArticuloId = $ArrayArticulo[$x]['articuloId'];
                        $UnidadMedida = ArticulosController::buscaNombreUMInventarioPorArticuloId($ArticuloId);
                        $Articulo = ArticulosController::buscaPorId($ArticuloId);

                        //REGISTRA TRASPASOS MOVTOS POR ARTICULO (NEGATIVO)
                        $CantidadPorTraspasar = $CantidadNegativa;
                        $ConsultaLocalidadId = \DB::select(\DB::raw("SELECT * FROM Localidades WHERE LOC_LocalidadId = '".DataBaseSession::getLocalidadGeneralId()."' AND LOC_Eliminado = 0"));
                        $LocalidadId = $ConsultaLocalidadId[0]->LOC_LocalidadId;
                        $AlmacenOrigenId = \DB::select(\DB::raw("SELECT * FROM Localidades WHERE LOC_LocalidadId = '".$LocalidadId."' AND LOC_Eliminado = 0"));
                        $AlmacenId = $AlmacenOrigenId[0]->LOC_LocalidadId;
                        $TipoOperacion = "Negativo";

                        //VALIDAR SI HAY EXISTENCIAS
                        $localidadesArticuloExisencias = \DB::select(\DB::raw("SELECT * FROM LocalidadesArticulo WHERE LOCA_LOC_LocalidadId = '".$LocalidadId."' AND LOCA_ART_ArticuloId = '".$ArticuloId."'"));

                        if(count($localidadesArticuloExisencias) > 0){

                            if($CantidadPositiva > $localidadesArticuloExisencias[0]->LOCA_Cantidad){

                                $mensaje = 'No se realizo la devolucion, no hay suficiente inventario.';
                                $bandera = false;
                                break;

                            }
                            else{

                                DevolucionesCedisController::procesaTransferirArticulo($TipoOperacion,$TraspasoId,$objeto,$AlmacenId,$LocalidadId,$Codigo_DevolucionCedi,$CantidadPorTraspasar,$ArticuloId,$UnidadMedida[0]->CMUM_Nombre,$Articulo->ART_CodigoArticulo,$Articulo->ART_CantidadAMano);

                            }

                        }
                        else{

                            $mensaje = 'No se encontro la localidad articulo.';
                            $bandera = false;
                            break;

                        }

                    }

                }

            }

            if($bandera){

                $mensaje = 'Se registró la Devolución de Cedi con éxito.';

                \DB::commit();

                return ['Status' => 'Valido', 'Mensaje' => $mensaje];

            }
            else{

                return ['Status' => 'Error', 'Mensaje' => $mensaje];

            }

        }
        catch (\Exception $e) {

            \DB::rollback();

            return ['Status' => 'Error', 'Mensaje' => 'No se registró la Devolución de Cedi. Ocurrió un error al realizar el proceso. Error: '.$e->getMessage(). " Linea: ".$e->getLine()];

        }

    }

    public function GuardaDevolucionesCedis($TraspasoId,$idArt,$CantidadPorTraspasar,$LocalidadId,$LoteId){

        try{

            $DevolcionCedi=new DevolucionesCedis();
            $DevolcionCedi->DC_TRA_TraspasoId=$TraspasoId;
            $DevolcionCedi->DC_ART_ArticuloId=$idArt;
            $DevolcionCedi->DC_CantidadDevuelta=$CantidadPorTraspasar;
            $DevolcionCedi->DC_LOC_LocalidadId=$LocalidadId;
            $DevolcionCedi->DC_LOT_LoteId=$LoteId;
            $DevolcionCedi->save();

        }
        catch(\Exception $e){

            throw $e;

        }

    }

    public function establecerAutonumerico($autonumerico, $rutaId)
    {
        try {
            $autonumerico_dao = new AutonumericoController();
            $autonumericoFicha = $autonumerico_dao->getAutonumericoN($autonumerico, DataBaseSession::getCediId());
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
            $TraspasosMovtos->TRAM_Razon="Devolucion de Cedi con código: ".$Codigo_Traspaso;
            $TraspasosMovtos->TRAM_Referencia="Devolucion de Cedi del Articulo: ".$CodigoArticulo." Cantidad: ".$CantidadPorTraspasar;
            $TraspasosMovtos->TRAM_UnidadMedidadArt=$UnidadMedida;
            $TraspasosMovtos->TRAM_EstatusContable=false;
            $TraspasosMovtos->TRAM_CantidadAMano=$CantidadAMano;
            $TraspasosMovtos->TRAM_CantidadAManoConTraspaso=$CantidadAMano+$CantidadPorTraspasar;
            $TraspasosMovtos->TRAM_CMM_TipoTransferenciaId='80D22B29-2BFF-4044-9FD5-AFA580C175D8';//DEV. CEDI

            $cuentaObjeto = count($objeto);
            $arrayDetallesMovimiento = array();

            for ($i = 0; $i < $cuentaObjeto; $i++)
            {

                if($objeto[$i][1] == $idArt)
                {

                    //registra en nueva tabla
                    DevolucionesCedisController::GuardaDevolucionesCedis($TraspasoId,$idArt,$objeto[$i][0],$LocalidadId,$objeto[$i][2]);

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

    public static function getNuevoId()
    {
        if (function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

}
