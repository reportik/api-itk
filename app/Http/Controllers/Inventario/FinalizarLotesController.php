<?php namespace App\Http\Controllers\Inventario;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Request AS NewRequest;
use Illuminate\Http\Request;
use App\Mapeos\Controles\ControlesMaestrosEsquemas;
use App\Mapeos\Controles\ControlesMaestrosMultiples;
use App\Models\Inventario\InventarioFisico\Empleado;
use App\Models\Lotes;
use App\Models\LotesCajas;
use App\Models\LotesPallets;
use Response;

class FinalizarLotesController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */

	public function index()
	{


        $idEmpleado = isset($_SESSION['empleadoId']) ? $_SESSION['empleadoId'] : null;
        //$idEmpleado = '32423501-373E-4958-A9FE-A34922AF7FFF';
        //$idEmpleado = 'CCEECA23-9A26-466C-9B71-8500984C606D';
        $lineaProduccionNombre = "";
        if($idEmpleado != "")
        {

            $lineaProduccionNombre = Empleado::select('LIP_Nombre')
            ->leftJoin('LineasProduccion','EMP_LIP_LineaProduccionId','=','LIP_LineaProduccionId')
            ->where('EMP_EmpleadoId', '=', $idEmpleado)
            ->get();

            $lineaProduccionNombre = count($lineaProduccionNombre) > 0 ? $lineaProduccionNombre[0]->LIP_Nombre : "";

        }

        return view('Inventario.FinalizarLotes.create', compact('idEmpleado','lineaProduccionNombre'));

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

    public static function BuscarLotesAbiertos(){

        $consultaLotesAbiertos = \DB::select(

            \DB::raw(

                "SELECT ART_Nombre, ART_CodigoArticulo, LOTP_CodigoLotePreliminar, LOTP_FechaLotePreliminar, ART_DiasVidaAnaquel,
                ART_CantidadCajasEnPallet, ART_CantidadUMEmpaqueEnCaja, APC_PesoInicial, APC_PesoFinal, ARTM_Nombre,
                ART_ArticuloId, LOTP_LotePreliminarId
                FROM LotesPreliminares
                INNER JOIN Articulos ON ART_ArticuloId = LOTP_ART_ArticuloId
                INNER JOIN ArticulosParametrosCalidad ON ART_ArticuloId = APC_ART_ArticuloId
                INNER JOIN ArticulosMarcas ON ART_ARTM_MarcaId = ARTM_MarcaId
                INNER JOIN LineasProduccionArticulos ON LPA_ART_ArticuloId = ART_ArticuloId
                WHERE LOTP_CMM_EstatusLotePreliminarId = '362B0AC5-85A1-4DB1-A725-DA1C64702E7D'
                ORDER BY LOTP_NumeroLotePreliminar DESC"

            )

        );

        return Response::json($consultaLotesAbiertos);

    }

    public static function ConsultarEspecificacionesArticuloPorCodigo($codigoArticulo){

        $sub = \DB::select(

            \DB::raw(

                "SELECT ART_Nombre,CMM_Valor,AET_Valor FROM Articulos
                LEFT JOIN ArticulosEspecificaciones ON ART_ArticuloId = AET_ART_ArticuloId
                LEFT JOIN ControlesMaestrosMultiples ON AET_CMM_ArticuloEspecificaciones = CMM_ControlId
                WHERE ART_CodigoArticulo = '".$codigoArticulo."'
                ORDER BY CMM_Valor ASC"

            )

        );

        return Response::json($sub);

    }

    public static function cerrarPallet($codigoLote){

        \DB::beginTransaction();

        try {

            //date_default_timezone_set('America/Mexico_City');
            $hoy=date('d/m/Y H:i:s');

            $idEmpleado = $_SESSION['empleadoId'];
            //$idEmpleado = '32423501-373E-4958-A9FE-A34922AF7FFF';
            //$idEmpleado = 'CCEECA23-9A26-466C-9B71-8500984C606D';

            $lineaProduccion = EmpaqueController::buscarLineaProduccionEmpleado($idEmpleado);

            $idLote = EmpaqueController::BuscaLotePorCodigo($codigoLote);

            $UltimoPalletRegistradoId = LotesPallets::where('LPA_LOT_LoteId','=',$idLote[0]->LOT_LoteId)
                ->where('LPA_LIP_LineaProduccionId','=',$lineaProduccion[0]->EMP_LIP_LineaProduccionId)
                ->orderby('LPA_FechaRegistro', 'DESC')->first()->LPA_LotePalletId;

            \DB::table('LotesPallets')->where('LPA_LotePalletId', '=', $UltimoPalletRegistradoId)
                ->update(
                    array(
                        'LPA_CMM_EstatusId' => ControlesMaestrosMultiples::$CMM_INV_EstatusPallet_Cerrado,
                        //'LPA_FechaRegistro' => $hoy,
                        'LPA_FechaUltimaModificacion' => $hoy
                    )
                );

            $mensaje = 'El Pallet se cerró con éxito.';

            \DB::commit();

            return ['Status' => 'Valido', 'Mensaje' => $mensaje];

        }
        catch (\Exception $e) {

            \DB::rollback();

            return ['Status' => 'Error', 'Mensaje' => 'No se cerró el Pallet. Ocurrió un error al realizar el proceso. <br><br><br>Error: <br>'.$e->getMessage()];

        }

    }

    public static function InsertaNuevoLotePallet($codigoLote){

        \DB::beginTransaction();

        try {

            //date_default_timezone_set('America/Mexico_City');
            $hoy=date('d/m/Y H:i:s');

            $idEmpleado = $_SESSION['empleadoId'];
            //$idEmpleado = '32423501-373E-4958-A9FE-A34922AF7FFF';
            //$idEmpleado = 'CCEECA23-9A26-466C-9B71-8500984C606D';

            $lineaProduccion = EmpaqueController::buscarLineaProduccionEmpleado($idEmpleado);

            $idLote = EmpaqueController::BuscaLotePorCodigo($codigoLote);

            //VERIFICAR SI HAY PALLET ELIMINADO REGISTRADO
            $palletEliminado = LotesPallets::where('LPA_Eliminado','=',1)
                ->where('LPA_LOT_LoteId','=',$idLote[0]->LOT_LoteId)
                ->orderBy('LPA_NumeroPallet','ASC')
                ->get();
            /////////////////////////////////////////////////

            $cuentaPalletEliminado = count($palletEliminado);

            if($cuentaPalletEliminado > 0)
            {

                \DB::table('LotesPallets')->where('LPA_LotePalletId', '=', $palletEliminado[0]->LPA_LotePalletId)
                    ->update(
                        array(
                            'LPA_Eliminado' => 0,
                            'LPA_CMM_EstatusId' => ControlesMaestrosMultiples::$CMM_INV_EstatusPallet_Abierto,
                            'LPA_LIP_LineaProduccionId' => $lineaProduccion[0]->EMP_LIP_LineaProduccionId,
                            'LPA_FechaRegistro' => $hoy,
                            'LPA_FechaUltimaModificacion' => $hoy
                        )
                    );

            }
            else
            {

                $UltimoPalletRegistradoId = LotesPallets::where('LPA_LOT_LoteId','=',$idLote[0]->LOT_LoteId)
                    ->where('LPA_LIP_LineaProduccionId','=',$lineaProduccion[0]->EMP_LIP_LineaProduccionId)
                    ->orderby('LPA_FechaRegistro', 'DESC')->first()->LPA_LotePalletId;

                \DB::table('LotesPallets')->where('LPA_LotePalletId', '=', $UltimoPalletRegistradoId)
                    ->update(
                        array(
                            'LPA_CMM_EstatusId' => ControlesMaestrosMultiples::$CMM_INV_EstatusPallet_Cerrado,
                            'LPA_FechaUltimaModificacion' => $hoy
                        )
                    );

                $UltimoPalletRegistrado = LotesPallets::where('LPA_LOT_LoteId','=',$idLote[0]->LOT_LoteId)
                    ->orderby(\DB::raw('CAST(LPA_NumeroPallet AS Int)'), 'DESC')->first()->LPA_NumeroPallet;

                \DB::table('LotesPallets')->insert(

                    array(

                        'LPA_LOT_LoteId' => $idLote[0]->LOT_LoteId,
                        'LPA_NumeroPallet' => $UltimoPalletRegistrado + 1,
                        'LPA_CMM_EstatusId' => ControlesMaestrosMultiples::$CMM_INV_EstatusPallet_Abierto,
                        'LPA_EMP_CreadoPorId' => $idEmpleado,
                        'LPA_LIP_LineaProduccionId' => $lineaProduccion[0]->EMP_LIP_LineaProduccionId

                    )

                );

            }

            $mensaje = 'Se registró el Pallet con éxito.';

            \DB::commit();

            return ['Status' => 'Valido', 'Mensaje' => $mensaje];

        }
        catch (\Exception $e) {

            \DB::rollback();

            return ['Status' => 'Error', 'Mensaje' => 'No se registró el Pallet. Ocurrió un error al realizar el proceso. <br><br><br>Error: <br>'.$e->getMessage()];

        }

    }
    public function VerificarLote2($codigoLote){

        $idLote = EmpaqueController::BuscaLotePorCodigo($codigoLote);

        $cuentaIdLote = count($idLote);

        $bandera = 0;

        if($cuentaIdLote > 0)
        {

            $bandera = 1;

        }

        return $bandera;

    }

    public function VerificarLote(){

        //date_default_timezone_set('America/Mexico_City');
        $dia = date('d');
        $mes = date('m');
        $ano = date('Y');
        $cortaAno = substr($ano, -2);
        //$parte2Lote = $dia.$mes.$cortaAno;
        $parte2Lote = $dia.$mes;

        $codigoLote = $_POST['arreglo'][2].$parte2Lote;

        $idLote = EmpaqueController::BuscaLotePorCodigo($codigoLote);

        $cuentaIdLote = count($idLote);

        $bandera = 0;

        if($cuentaIdLote > 0)
        {

            $bandera = 1;

        }

        return $bandera;

    }

    public function InsertarLotesPalletsInicio2($arreglo, $EmpleadoId, $lineaProduccionId){

        try {

            //date_default_timezone_set('America/Mexico_City');
            $dia = date('d');
            $mes = date('m');
            $ano = date('Y');
            $cortaAno = substr($ano, -2);
            //$parte2Lote = $dia.$mes.$cortaAno;
            $parte2Lote = $dia.$mes;

            $codigoLote = $arreglo[2].$parte2Lote;

            $numeroLote = substr($arreglo[2], 0, 3);

            //INSERTA LOTE
            \DB::table('Lotes')->insert(

                array(

                    'LOT_NumeroLote' => $numeroLote,
                    'LOT_ART_ArticuloId' => $arreglo[11],
                    'LOT_CodigoLote' => $codigoLote,
                    'LOT_FechaCaducidad' => $arreglo[5],
                    'LOT_FechaLote' => $arreglo[4],
                    'LOT_CMM_EstatusLoteId' => ControlesMaestrosMultiples::$CMM_EstatusLote_Empacado,
                    'LOT_LOTP_LotePreliminarId' => $arreglo[12]

                )

            );

            $idLote = EmpaqueController::BuscaLotePorCodigo($codigoLote);

            //INSERTA PALLET
            \DB::table('LotesPallets')->insert(

                array(

                    'LPA_LOT_LoteId' => $idLote[0]->LOT_LoteId,
                    'LPA_NumeroPallet' => 1,
                    'LPA_CMM_EstatusId' => ControlesMaestrosMultiples::$CMM_INV_EstatusPallet_Abierto,
                    'LPA_EMP_CreadoPorId' => $EmpleadoId,
                    'LPA_LIP_LineaProduccionId' => $lineaProduccionId

                )

            );

        }
        catch(\Exception $e){

            throw $e;

        }

    }

    public static function InsertarLotesPalletsInicio(){

        \DB::beginTransaction();

        try {

            //date_default_timezone_set('America/Mexico_City');
            $dia = date('d');
            $mes = date('m');
            $ano = date('Y');
            $cortaAno = substr($ano, -2);
            //$parte2Lote = $dia.$mes.$cortaAno;
            $parte2Lote = $dia.$mes;

            $codigoLote = $_POST['arreglo'][2].$parte2Lote;

            $idLote = EmpaqueController::BuscaLotePorCodigo($codigoLote);

            $cuentaIdLote = count($idLote);

            if($cuentaIdLote <= 0)
            {

                $numeroLote = substr($_POST['arreglo'][2], 0, 3);

                \DB::table('Lotes')->insert(

                    array(

                        'LOT_NumeroLote' => $numeroLote,
                        'LOT_ART_ArticuloId' => $_POST['arreglo'][11],
                        'LOT_CodigoLote' => $codigoLote,
                        'LOT_FechaCaducidad' => $_POST['arreglo'][5],
                        'LOT_FechaLote' => $_POST['arreglo'][4],
                        'LOT_CMM_EstatusLoteId' => ControlesMaestrosMultiples::$CMM_EstatusLote_Empacado,
                        'LOT_LOTP_LotePreliminarId' => $_POST['arreglo'][12]

                    )

                );

            }

            $idEmpleado = $_SESSION['empleadoId'];
            //$idEmpleado = '32423501-373E-4958-A9FE-A34922AF7FFF';
            //$idEmpleado = 'CCEECA23-9A26-466C-9B71-8500984C606D';

            $lineaProduccion = EmpaqueController::buscarLineaProduccionEmpleado($idEmpleado);

            $idLote = EmpaqueController::BuscaLotePorCodigo($codigoLote);

            $existe = EmpaqueController::verificaSiExistenMovimientosDeLote($idLote);

            $count = count($existe);

            if($count <= 0)
            {


                \DB::table('LotesPallets')->insert(

                    array(

                        'LPA_LOT_LoteId' => $idLote[0]->LOT_LoteId,
                        'LPA_NumeroPallet' => 1,
                        'LPA_CMM_EstatusId' => ControlesMaestrosMultiples::$CMM_INV_EstatusPallet_Abierto,
                        'LPA_EMP_CreadoPorId' => $idEmpleado,
                        'LPA_LIP_LineaProduccionId' => $lineaProduccion[0]->EMP_LIP_LineaProduccionId

                    )

                );

                $lotesEmpleado = EmpaqueController::consultaPalletYCajasPorEmpleadoYLinea($lineaProduccion[0]->EMP_LIP_LineaProduccionId,$idLote[0]->LOT_LoteId,$_POST['arreglo'][11]);

            }
            else
            {

                $buscaUltimoPalletRegistradoPorLineaProduccion = LotesPallets::where('LPA_LIP_LineaProduccionId','=',$lineaProduccion[0]->EMP_LIP_LineaProduccionId)
                    ->where('LPA_LOT_LoteId','=',$idLote[0]->LOT_LoteId)
                    ->orderby('LPA_FechaRegistro', 'DESC')->first();

                $cuentaPallet = count($buscaUltimoPalletRegistradoPorLineaProduccion);

                if($cuentaPallet <= 0)
                {

                    $UltimoPalletRegistrado = LotesPallets::where('LPA_LOT_LoteId','=',$idLote[0]->LOT_LoteId)
                        ->orderby(\DB::raw('CAST(LPA_NumeroPallet AS INT)'),'DESC')->first()->LPA_NumeroPallet;

                    \DB::table('LotesPallets')->insert(

                        array(

                            'LPA_LOT_LoteId' => $idLote[0]->LOT_LoteId,
                            'LPA_NumeroPallet' => $UltimoPalletRegistrado + 1,
                            'LPA_CMM_EstatusId' => ControlesMaestrosMultiples::$CMM_INV_EstatusPallet_Abierto,
                            'LPA_EMP_CreadoPorId' => $idEmpleado,
                            'LPA_LIP_LineaProduccionId' => $lineaProduccion[0]->EMP_LIP_LineaProduccionId

                        )

                    );

                    $lotesEmpleado = EmpaqueController::consultaPalletYCajasPorEmpleadoYLinea($lineaProduccion[0]->EMP_LIP_LineaProduccionId,$idLote[0]->LOT_LoteId,$_POST['arreglo'][11]);

                }
                else
                {

                    $lotesEmpleado = EmpaqueController::consultaPalletYCajasPorEmpleadoYLinea($lineaProduccion[0]->EMP_LIP_LineaProduccionId,$idLote[0]->LOT_LoteId,$_POST['arreglo'][11]);

                }

            }

            $mensaje = 'Se registró el Pallet con éxito.';

            \DB::commit();

            return ['Status' => 'Valido', 'Mensaje' => $mensaje, 'json' => $lotesEmpleado];

        }
        catch (\Exception $e) {

            \DB::rollback();

            return ['Status' => 'Error', 'Mensaje' => 'No se registró el Pallet. Ocurrió un error al realizar el proceso. <br><br><br>Error: <br>'.$e->getMessage()];

        }

    }

    public static function buscarLineaProduccionEmpleado($empleadoId){

        $sub = \DB::select(

            \DB::raw(

                "SELECT EMP_LIP_LineaProduccionId FROM Empleados WHERE EMP_EmpleadoId = '".$empleadoId."'"

            )

        );

        return $sub;

    }

    public static function verificaSiExistenMovimientosDeLote($idLote){

        $sub = \DB::select(

            \DB::raw(

                "SELECT LPA_LotePalletId FROM LotesPallets WHERE LPA_LOT_LoteId = '".$idLote[0]->LOT_LoteId."' AND LPA_Eliminado = 0"

            )

        );

        return $sub;

    }

    public static function consultaPalletYCajasPorEmpleadoYLinea($lineaProduccionId,$loteId,$articuloId){

        $sub = \DB::select(

            \DB::raw(

                "SELECT LPA_LotePalletId,LPA_NumeroPallet,LPA_Recibido,CMM_Valor,COUNT(cajasActivas.LCA_NumeroCaja)AS Cajas,
                cajasEliminadas.NumeroCajasEliminadas,SUM(cajasActivas.LCA_PesoCaja) AS Kilos, SUM(cajasActivas.LCA_PiezasCaja) AS Piezas
                FROM LotesPallets
                INNER JOIN Lotes ON LOT_LoteId = LPA_LOT_LoteId
                INNER JOIN ControlesMaestrosMultiples ON LPA_CMM_EstatusId = CMM_ControlId
                LEFT JOIN LotesCajas cajasActivas ON LPA_LotePalletId = cajasActivas.LCA_LPA_LotePalletId
                LEFT JOIN (select LCA_LPA_LotePalletId,COUNT(LCA_NumeroCaja) as NumeroCajasEliminadas from LotesCajas
			              where LCA_Eliminado = 1
			              group by LCA_LPA_LotePalletId) cajasEliminadas on LPA_LotePalletId = cajasEliminadas.LCA_LPA_LotePalletId
                WHERE LPA_LIP_LineaProduccionId = '".$lineaProduccionId."'
                AND LPA_LOT_LoteId = '".$loteId."'
                AND LPA_Eliminado = 0
                AND LOT_ART_ArticuloId = '".$articuloId."'
                GROUP BY LPA_FechaRegistro,LPA_LotePalletId,LPA_NumeroPallet,LPA_Recibido,CMM_Valor, cajasEliminadas.NumeroCajasEliminadas
                ORDER BY LPA_FechaRegistro DESC"

            )

        );

        return $sub;

    }

    public function InsertarLotesCajasNuevo(){

        \DB::beginTransaction();

        try {

            //date_default_timezone_set('America/Mexico_City');
            $hoy=date('d/m/Y H:i:s');

            $idEmpleado = $_SESSION['empleadoId'];
            //$idEmpleado = '32423501-373E-4958-A9FE-A34922AF7FFF';
            //$idEmpleado = 'CCEECA23-9A26-466C-9B71-8500984C606D';

            $lineaProduccion = EmpaqueController::buscarLineaProduccionEmpleado($idEmpleado);

            //RECUPERO VARIABLES DE JAVASCRIPT
            $numPallet = NewRequest::input('NoPallet');
            $numCaja = NewRequest::input('NoCaja');
            $pesoCaja = NewRequest::input('PesoReal');
            $codigoLote = NewRequest::input('CodigoLote');
            $piezasCaja = NewRequest::input('PiezasPorCaja');
            $arreglo = NewRequest::input('arreglo');

            //VERIFICAR SI YA EXISTE LOTE
            $idLote = EmpaqueController::BuscaLotePorCodigo($codigoLote);

            $cuentaIdLote = count($idLote);

            //SI NO HAY LOTE --> REGISTRAR (LOTE Y PALLET)
            if($cuentaIdLote < 1)
            {

                EmpaqueController::InsertarLotesPalletsInicio2($arreglo, $idEmpleado, $lineaProduccion[0]->EMP_LIP_LineaProduccionId);
                $idLote = EmpaqueController::BuscaLotePorCodigo($codigoLote);

            }

            //CONSULTAR ULTIMO PALLET
            $UltimoRegistrado =LotesPallets::where('LPA_CMM_EstatusId','=',ControlesMaestrosMultiples::$CMM_INV_EstatusPallet_Abierto)
                ->where('LPA_LOT_LoteId','=',$idLote[0]->LOT_LoteId)
                ->where('LPA_LIP_LineaProduccionId','=',$lineaProduccion[0]->EMP_LIP_LineaProduccionId)
                ->orderby('LPA_FechaRegistro', 'DESC')->first()->LPA_LotePalletId;

            //CONSULTAR CAJA
            $idCaja = LotesPallets::select('LCA_LoteCajaId','LCA_NumeroCaja','LCA_PesoCaja','LCA_PiezasCaja','LCA_Eliminado')
                ->join('LotesCajas','LPA_LotePalletId','=','LCA_LPA_LotePalletId')
                ->where('LCA_NumeroCaja','=',$numCaja)
                ->where('LPA_NumeroPallet','=',$numPallet)
                ->where('LPA_LOT_LoteId','=',$idLote[0]->LOT_LoteId)
                ->where('LPA_LIP_LineaProduccionId','=',$lineaProduccion[0]->EMP_LIP_LineaProduccionId)
                ->get();

            $cuentaResultados = count($idCaja);

            $bandera = 0;

            //SI EXISTE CAJA ACTUALIZAR DATOS, SI NO, INSERTAR NUEVA CAJA
            if($cuentaResultados > 0)
            {

                \DB::table('LotesCajas')->where('LCA_LoteCajaId', '=', $idCaja[0]->LCA_LoteCajaId)
                    ->update(
                        array(
                            'LCA_PesoCaja' => $pesoCaja,
                            'LCA_PiezasCaja' => $piezasCaja,
                            'LCA_Eliminado' => 0,
                            'LCA_FechaUltimaModificacion' => $hoy
                        )
                    );

                $bandera = 1;
            }
            else
            {

                \DB::table('LotesCajas')->insert(

                    array(

                        'LCA_LPA_LotePalletId' => $UltimoRegistrado,
                        'LCA_LOT_LoteId' => $idLote[0]->LOT_LoteId,
                        'LCA_NumeroCaja' => $numCaja,
                        'LCA_PesoCaja' => $pesoCaja,
                        'LCA_PiezasCaja' => $piezasCaja

                    )

                );

            }

            $mensaje = 'Se registró la Caja con éxito.';

            \DB::commit();

            return ['Status' => 'Valido', 'Mensaje' => $mensaje, 'Bandera' => $bandera];

        }
        catch (\Exception $e) {

            \DB::rollback();

            return ['Status' => 'Error', 'Mensaje' => 'No se registró la Caja. Ocurrió un error al realizar el proceso. <br><br><br>Error: <br>'.$e->getMessage()];

        }

    }

    public static function InsertarLotesCajas($numPallet,$numCaja,$pesoCaja,$codigoLote,$piezasCaja){

        \DB::beginTransaction();

        try {

            //date_default_timezone_set('America/Mexico_City');
            $hoy=date('d/m/Y H:i:s');

            $idEmpleado = $_SESSION['empleadoId'];
            //$idEmpleado = '32423501-373E-4958-A9FE-A34922AF7FFF';
            //$idEmpleado = 'CCEECA23-9A26-466C-9B71-8500984C606D';

            $lineaProduccion = EmpaqueController::buscarLineaProduccionEmpleado($idEmpleado);

            $idLote = EmpaqueController::BuscaLotePorCodigo($codigoLote);

            $UltimoRegistrado =LotesPallets::where('LPA_CMM_EstatusId','=',ControlesMaestrosMultiples::$CMM_INV_EstatusPallet_Abierto)
                ->where('LPA_LOT_LoteId','=',$idLote[0]->LOT_LoteId)
                ->where('LPA_LIP_LineaProduccionId','=',$lineaProduccion[0]->EMP_LIP_LineaProduccionId)
                ->orderby('LPA_FechaRegistro', 'DESC')->first()->LPA_LotePalletId;

            $idCaja = LotesPallets::select('LCA_LoteCajaId','LCA_NumeroCaja','LCA_PesoCaja','LCA_PiezasCaja','LCA_Eliminado')
                ->join('LotesCajas','LPA_LotePalletId','=','LCA_LPA_LotePalletId')
                ->where('LCA_NumeroCaja','=',$numCaja)
                ->where('LPA_NumeroPallet','=',$numPallet)
                ->where('LPA_LOT_LoteId','=',$idLote[0]->LOT_LoteId)
                ->where('LPA_LIP_LineaProduccionId','=',$lineaProduccion[0]->EMP_LIP_LineaProduccionId)
                ->get();

            $cuentaResultados = count($idCaja);

            $bandera = 0;

            if($cuentaResultados > 0)
            {

                \DB::table('LotesCajas')->where('LCA_LoteCajaId', '=', $idCaja[0]->LCA_LoteCajaId)
                    ->update(
                        array(
                            'LCA_PesoCaja' => $pesoCaja,
                            'LCA_PiezasCaja' => $piezasCaja,
                            'LCA_Eliminado' => 0,
                            'LCA_FechaUltimaModificacion' => $hoy
                        )
                    );

                $bandera = 1;
            }
            else
            {

                \DB::table('LotesCajas')->insert(

                    array(

                        'LCA_LPA_LotePalletId' => $UltimoRegistrado,
                        'LCA_LOT_LoteId' => $idLote[0]->LOT_LoteId,
                        'LCA_NumeroCaja' => $numCaja,
                        'LCA_PesoCaja' => $pesoCaja,
                        'LCA_PiezasCaja' => $piezasCaja

                    )

                );

            }

            $mensaje = 'Se registró la Caja con éxito.';

            \DB::commit();

            return ['Status' => 'Valido', 'Mensaje' => $mensaje, 'Bandera' => $bandera];

        }
        catch (\Exception $e) {

            \DB::rollback();

            return ['Status' => 'Error', 'Mensaje' => 'No se registró la Caja. Ocurrió un error al realizar el proceso. <br><br><br>Error: <br>'.$e->getMessage()];

        }

    }

    public function CambiarStatusLotesPreliminares($preLoteId){

        \DB::beginTransaction();

        try {

            ///////CODIGO NUEVO - CERRAR PRE-LOTE/////
            \DB::table('LotesPreliminares')->where('LOTP_LotePreliminarId', '=', $preLoteId)
                ->update(
                    array(
                        'LOTP_CMM_EstatusLotePreliminarId' => ControlesMaestrosMultiples::$CMM_EstatusLote_Cerrado
                    )
                );
            //////////////////////////////////////////

            $mensaje = 'Se Finalizó el Pre-Lote con éxito.';

            \DB::commit();

            return ['Status' => 'Valido', 'Mensaje' => $mensaje];

        }
        catch (\Exception $e) {

            \DB::rollback();

            return ['Status' => 'Error', 'Mensaje' => 'No se Finalizó el Pre-Lote. Ocurrió un error al realizar el proceso. <br><br><br>Error: <br>'.$e->getMessage()];

        }

    }

    public static function CambiarStatusLotes($codigoLote){

        \DB::beginTransaction();

        try {

            //date_default_timezone_set('America/Mexico_City');
            $hoy=date('d/m/Y H:i:s');

            $idLote = EmpaqueController::BuscaLotePorCodigo($codigoLote);

            \DB::table('Lotes')->where('LOT_LoteId', '=', $idLote[0]->LOT_LoteId)
                ->update(
                    array(
                        'LOT_CMM_EstatusLoteId' => ControlesMaestrosMultiples::$CMM_EstatusLote_Cerrado,
                        'LOT_FechaUltimaModificacion' => $hoy,
                        'LOT_Cerrado' => 1
                    )
                );

            $mensaje = 'Se Finalizó el Lote con éxito.';

            \DB::commit();

            return ['Status' => 'Valido', 'Mensaje' => $mensaje];

        }
        catch (\Exception $e) {

            \DB::rollback();

            return ['Status' => 'Error', 'Mensaje' => 'No se Finalizó el Lote. Ocurrió un error al realizar el proceso. <br><br><br>Error: <br>'.$e->getMessage()];

        }

    }

    public static function CambiarStatusPallet($codigoLote){

        \DB::beginTransaction();

        try {

            //date_default_timezone_set('America/Mexico_City');
            $hoy=date('d/m/Y H:i:s');

            $idEmpleado = $_SESSION['empleadoId'];
            //$idEmpleado = '32423501-373E-4958-A9FE-A34922AF7FFF';
            //$idEmpleado = 'CCEECA23-9A26-466C-9B71-8500984C606D';

            $lineaProduccion = EmpaqueController::buscarLineaProduccionEmpleado($idEmpleado);

            $idLote = EmpaqueController::BuscaLotePorCodigo($codigoLote);

            \DB::table('LotesPallets')->where('LPA_LOT_LoteId', '=', $idLote[0]->LOT_LoteId)
                ->where('LPA_LIP_LineaProduccionId','=',$lineaProduccion[0]->EMP_LIP_LineaProduccionId)
                ->where('LPA_CMM_EstatusId','=',ControlesMaestrosMultiples::$CMM_INV_EstatusPallet_Abierto)
                ->update(
                    array(
                        'LPA_CMM_EstatusId' => ControlesMaestrosMultiples::$CMM_INV_EstatusPallet_Cerrado,
                        'LPA_FechaUltimaModificacion' => $hoy
                    )
                );

            $mensaje = 'Se Actualizó el estado del Pallet con éxito.';

            \DB::commit();

            return ['Status' => 'Valido', 'Mensaje' => $mensaje];

        }
        catch (\Exception $e) {

            \DB::rollback();

            return ['Status' => 'Error', 'Mensaje' => 'No se Actualizó el estado del Pallet. Ocurrió un error al realizar el proceso. <br><br><br>Error: <br>'.$e->getMessage()];

        }

    }

    public static function BuscarCajasRegistradasPorPallet($codigoLote,$numeroPallet){

        $idEmpleado = $_SESSION['empleadoId'];
        //$idEmpleado = '32423501-373E-4958-A9FE-A34922AF7FFF';
        //$idEmpleado = 'CCEECA23-9A26-466C-9B71-8500984C606D';

        $lineaProduccion = EmpaqueController::buscarLineaProduccionEmpleado($idEmpleado);

        $idLote = EmpaqueController::BuscaLotePorCodigo($codigoLote);

        $sub = \DB::select(

            \DB::raw(

                "SELECT LCA_NumeroCaja,LCA_PiezasCaja,LCA_PesoCaja, LCA_Eliminado FROM LotesPallets
                INNER JOIN LotesCajas ON LPA_LotePalletId = LCA_LPA_LotePalletId
                WHERE LPA_LOT_LoteId = '".$idLote[0]->LOT_LoteId."'
                AND LPA_LIP_LineaProduccionId = '".$lineaProduccion[0]->EMP_LIP_LineaProduccionId."'
                AND LPA_NumeroPallet = '".$numeroPallet."'
                ORDER BY CAST(LCA_NumeroCaja AS Int) DESC"
                //AND LPA_CMM_EstatusId = '".ControlesMaestrosMultiples::$CMM_INV_EstatusPallet_Abierto."'
            )

        );

        return Response::json($sub);

    }

    public function consultarCajasPorPallet($codigoLote,$noPallet){

        $idEmpleado = $_SESSION['empleadoId'];
        //$idEmpleado = '32423501-373E-4958-A9FE-A34922AF7FFF';
        //$idEmpleado = 'CCEECA23-9A26-466C-9B71-8500984C606D';

        $lineaProduccion = EmpaqueController::buscarLineaProduccionEmpleado($idEmpleado);

        $idLote = EmpaqueController::BuscaLotePorCodigo($codigoLote);

        $cuentaIdLote = count($idLote);
        if($cuentaIdLote > 0)
        {

            $sub = \DB::select(

                \DB::raw(

                    "SELECT LCA_NumeroCaja,LCA_PiezasCaja,LCA_PesoCaja
                    FROM LotesPallets
                    INNER JOIN LotesCajas ON LPA_LotePalletId = LCA_LPA_LotePalletId
                    WHERE LPA_LOT_LoteId = '".$idLote[0]->LOT_LoteId."'
                    AND LPA_LIP_LineaProduccionId = '".$lineaProduccion[0]->EMP_LIP_LineaProduccionId."'
                    AND LPA_NumeroPallet = '".$noPallet."'
                    AND LCA_Eliminado = 0
                    ORDER BY CAST(LCA_NumeroCaja AS Int) DESC"

                )

            );

        }
        else
        {

            $sub = null;

        }


        return Response::json($sub);

    }

    public static function BuscaLotePorCodigo($codigoLote){

        $sub = \DB::select(

            \DB::raw(

                "SELECT LOT_LoteId,LOT_CantidadIntervencionSupervisor,LOT_LOTP_LotePreliminarId FROM Lotes WHERE LOT_CodigoLote = '".$codigoLote."'"

            )

        );

        return $sub;

    }

    public static function EliminarPallet($codigoLote,$noPallet){

        \DB::beginTransaction();

        try {

            //date_default_timezone_set('America/Mexico_City');
            $hoy=date('d/m/Y H:i:s');

            $idEmpleado = $_SESSION['empleadoId'];
            //$idEmpleado = '32423501-373E-4958-A9FE-A34922AF7FFF';
            //$idEmpleado = 'CCEECA23-9A26-466C-9B71-8500984C606D';

            $lineaProduccion = EmpaqueController::buscarLineaProduccionEmpleado($idEmpleado);

            $idLote = EmpaqueController::BuscaLotePorCodigo($codigoLote);

            $idPallet = LotesPallets::select('LPA_LotePalletId')
                ->where('LPA_LOT_LoteId', '=', $idLote[0]->LOT_LoteId)
                ->where('LPA_LIP_LineaProduccionId','=',$lineaProduccion[0]->EMP_LIP_LineaProduccionId)
                ->where('LPA_NumeroPallet','=',$noPallet)
                ->get();

            $cajasEnPallet = LotesCajas::select('LCA_LoteCajaId')
                ->where('LCA_LPA_LotePalletId','=',$idPallet[0]->LPA_LotePalletId)
                ->get();

            $cuentaCajasEnPallet = count($cajasEnPallet);

            if($cuentaCajasEnPallet > 0)
            {

                \DB::table('LotesCajas')->where('LCA_LPA_LotePalletId', '=', $idPallet[0]->LPA_LotePalletId)->delete();

            }

            if($noPallet != 1)
            {

                \DB::table('LotesPallets')->where('LPA_LotePalletId', '=', $idPallet[0]->LPA_LotePalletId)
                    ->update(
                        array(
                            'LPA_Eliminado' => 1,
                            'LPA_FechaUltimaModificacion' => $hoy
                        )
                    );

                $mensaje = 'Se Elimino el Pallet con éxito.';

            }
            else
            {

                $mensaje = 'Se Elimnaron solo las cajas del Pallet con éxito, ya que no se puede elimiar el PALLET 1.';

            }

            \DB::commit();

            return ['Status' => 'Valido', 'Mensaje' => $mensaje];

        }
        catch (\Exception $e) {

            \DB::rollback();

            return ['Status' => 'Error', 'Mensaje' => 'No se Elimino el Pallet. Ocurrió un error al realizar el proceso. <br><br><br>Error: <br>'.$e->getMessage()];

        }

    }

    public static function EliminarCaja($codigoLote,$noCaja){

        \DB::beginTransaction();

        try {

            //date_default_timezone_set('America/Mexico_City');
            $hoy=date('d/m/Y H:i:s');

            $idEmpleado = $_SESSION['empleadoId'];
            //$idEmpleado = '32423501-373E-4958-A9FE-A34922AF7FFF';
            //$idEmpleado = 'CCEECA23-9A26-466C-9B71-8500984C606D';

            $lineaProduccion = EmpaqueController::buscarLineaProduccionEmpleado($idEmpleado);

            $idLote = EmpaqueController::BuscaLotePorCodigo($codigoLote);

            $palletId = EmpaqueController::BuscaPalletId($idLote[0]->LOT_LoteId,$lineaProduccion[0]->EMP_LIP_LineaProduccionId);

            \DB::table('LotesCajas')->where('LCA_LOT_LoteId', '=', $idLote[0]->LOT_LoteId)
                ->where('LCA_LPA_LotePalletId','=',$palletId[0]->LPA_LotePalletId)
                ->where('LCA_NumeroCaja','=',$noCaja)
                ->update(
                    array(
                        'LCA_Eliminado' => 1,
                        'LCA_FechaUltimaModificacion' => $hoy
                    )
                );

            \DB::table('LotesPallets')->where('LPA_LotePalletId', '=', $palletId[0]->LPA_LotePalletId)
                ->update(
                    array(
                        'LPA_CMM_EstatusId' => ControlesMaestrosMultiples::$CMM_INV_EstatusPallet_Abierto,
                        'LPA_FechaUltimaModificacion' => $hoy
                    )
                );

            $mensaje = 'Se Elimino la Caja con éxito.';

            \DB::commit();

            return ['Status' => 'Valido', 'Mensaje' => $mensaje];

        }
        catch (\Exception $e) {

            \DB::rollback();

            return ['Status' => 'Error', 'Mensaje' => 'No se Elimino la Caja. Ocurrió un error al realizar el proceso. <br><br><br>Error: <br>'.$e->getMessage()];

        }

    }

    public static function BuscaPalletId($idLote,$idLineaProduccion){

        //busacapallet
        $sub = \DB::select(

            \DB::raw(

                "SELECT LPA_LotePalletId FROM LotesPallets
                WHERE LPA_LOT_LoteId = '".$idLote."'
                AND LPA_LIP_LineaProduccionId = '".$idLineaProduccion."' ORDER BY LPA_FechaRegistro DESC"

            )

        );

        return $sub;

    }

    public function buscaSupervisor($usuario,$pass){

        $sub = \DB::select(

            \DB::raw(

                "SELECT * FROM Usuarios
                WHERE USU_Nombre = '".$usuario."'
                AND USU_Contrasenia = '".$pass."'
                AND USU_Activo = 1"

            )

        );

        return $sub;

    }

    public function ActualizaSupervisorYCantidadEnLote($idEmpleado,$codigoLote){

        \DB::beginTransaction();

        try {

            $idLote = EmpaqueController::BuscaLotePorCodigo($codigoLote);
            $cuentaLote = count($idLote);
            if($cuentaLote > 0)
            {

                if($idLote[0]->LOT_CantidadIntervencionSupervisor == null)
                {

                    $cant = 1;

                }
                else
                {

                    $cant = $idLote[0]->LOT_CantidadIntervencionSupervisor + 1;

                }

                \DB::table('Lotes')->where('LOT_LoteId', '=', $idLote[0]->LOT_LoteId)
                    ->update(
                        array(
                            'LOT_EMP_SupervisorId' => $idEmpleado,
                            'LOT_CantidadIntervencionSupervisor' => $cant
                        )
                    );

                $mensaje = 'Se Actualizo la Cantidad en Lote con éxito.';

            }
            else
            {

                $mensaje = 'Se Actualizo la Cantidad en Lote con éxito...';

            }

            \DB::commit();

            return ['Status' => 'Valido', 'Mensaje' => $mensaje];

        }
        catch (\Exception $e) {

            \DB::rollback();

            return ['Status' => 'Error', 'Mensaje' => 'No se Actualizó la Cantidad en Lote. Ocurrió un error al realizar el proceso. <br><br><br>Error: <br>'.$e->getMessage()];

        }

    }

    public function consultaLotesPorPreLote2(){

        $PreLoteId = \Illuminate\Support\Facades\Request::input("PreLoteId");

        $sub = \DB::select(

            \DB::raw(

                "SELECT *
                FROM Lotes
                INNER JOIN ControlesMaestrosMultiples ON CMM_ControlId = LOT_CMM_EstatusLoteId
                WHERE LOT_CMM_EstatusLoteId IN ('362B0AC5-85A1-4DB1-A725-DA1C64702E7D','5F608B87-8FD8-4A0A-8C41-BFFAEAAC211F','98344A16-D332-4282-BD71-ED4FCC468D2F','8601CEC0-3271-4EC6-B857-AE1D352208D8')
                AND LOT_LOTP_LotePreliminarId = '".$PreLoteId."'
                ORDER BY LOT_FechaCreacion DESC"

            )

        );

        return $sub;

    }

    public function consultaLotesPorPreLote(){

        $PreLote = \Illuminate\Support\Facades\Request::input("PreLote");
        $PreLote_formateada = trim($PreLote);

        /*$preLoteId = \DB::select(

            \DB::raw(

                "SELECT * FROM LotesPreliminares WHERE LOTP_CodigoLotePreliminar = '".$CodigoPreLote_formateada."'"

            )

        );*/

        $sub = \DB::select(

            \DB::raw(

                "SELECT *
                FROM Lotes
                INNER JOIN ControlesMaestrosMultiples ON CMM_ControlId = LOT_CMM_EstatusLoteId
                WHERE LOT_CMM_EstatusLoteId IN ('362B0AC5-85A1-4DB1-A725-DA1C64702E7D','5F608B87-8FD8-4A0A-8C41-BFFAEAAC211F','98344A16-D332-4282-BD71-ED4FCC468D2F','8601CEC0-3271-4EC6-B857-AE1D352208D8')
                AND LOT_LOTP_LotePreliminarId = '".$PreLote_formateada."'
                ORDER BY LOT_FechaCreacion DESC"

            )

        );

        return $sub;

    }

    public function consultaPalletPorLote(){

        $CodigoLote = \Illuminate\Support\Facades\Request::input("CodigoLote");
        $CodigoLote_formateada = trim($CodigoLote);

        $LoteId = \DB::select(

            \DB::raw(

                "SELECT * FROM Lotes WHERE LOT_CodigoLote = '".$CodigoLote_formateada."'"

            )

        );

        $sub = \DB::select(

            \DB::raw(

                "SELECT * FROM LotesPallets WHERE LPA_LOT_LoteId = '".$LoteId[0]->LOT_LoteId."' ORDER BY CAST(LPA_NumeroPallet AS Int) ASC"

            )

        );

        return $sub;

    }

    public function consultaCajasPorPallet(){

        $NoPallet = \Illuminate\Support\Facades\Request::input("NoPallet");
        $NoPallet_formateada = trim($NoPallet);
        $CodigoLote = \Illuminate\Support\Facades\Request::input("CodigoLote");
        $CodigoLote_formateada = trim($CodigoLote);

        $LoteId = \DB::select(

            \DB::raw(

                "SELECT * FROM Lotes WHERE LOT_CodigoLote = '".$CodigoLote_formateada."'"

            )

        );

        $PalletId = \DB::select(

            \DB::raw(

                "SELECT * FROM LotesPallets WHERE LPA_LOT_LoteId = '".$LoteId[0]->LOT_LoteId."' AND LPA_NumeroPallet = '".$NoPallet_formateada."'"

            )

        );

        $sub = \DB::select(

            \DB::raw(

                "SELECT * FROM LotesCajas WHERE LCA_LPA_LotePalletId = '".$PalletId[0]->LPA_LotePalletId."' ORDER BY CAST(LCA_NumeroCaja AS Int) ASC"

            )

        );

        return $sub;

    }

    public function consultaDatosPorPallet(){

        $NoPallet = \Illuminate\Support\Facades\Request::input("NoPallet");
        $NoPallet_formateada = trim($NoPallet);
        $CodigoLote = \Illuminate\Support\Facades\Request::input("CodigoLote");
        $CodigoLote_formateada = trim($CodigoLote);

        $LoteId = \DB::select(

            \DB::raw(

                "SELECT * FROM Lotes WHERE LOT_CodigoLote = '".$CodigoLote_formateada."'"

            )

        );

        $datosPallet = \DB::select(

            \DB::raw(

                "SELECT
                    ART_Nombre,
                    ART_CodigoArticulo,
                    LPA_NumeroPallet,
                    Total_Cajas,
                    PesoReal,
                    TotalPiezas,
                    Promedio,
                    LOT_CodigoLote,
                    LOT_FechaCaducidad,
                    LOT_CodigoLote + ART_CodigoArticulo AS CodigoBarras
                FROM LotesPallets
                INNER JOIN (SELECT LCA_LPA_LotePalletId,
                                COUNT(*) AS Total_Cajas,
                                SUM(LCA_PesoCaja) AS PesoReal,
                                SUM(LCA_PiezasCaja) AS TotalPiezas,
                                SUM(LCA_PesoCaja)/SUM(LCA_PiezasCaja) AS Promedio
                           FROM LotesCajas
                           GROUP BY
                                LCA_LPA_LotePalletId) AS CAJAS ON LCA_LPA_LotePalletId = LPA_LotePalletId
                INNER JOIN Lotes ON LPA_LOT_LoteId = LOT_LoteId
                INNER JOIN Articulos ON ART_ArticuloId = LOT_ART_ArticuloId
                WHERE LPA_NumeroPallet = '".$NoPallet_formateada."'
                AND LPA_LOT_LoteId = '".$LoteId[0]->LOT_LoteId."'"

            )

        );

        return $datosPallet;

    }

    public function consultaDatosPorCaja(){

        $NoCaja = \Illuminate\Support\Facades\Request::input("NoCaja");
        $NoCaja_formateada = trim($NoCaja);
        $NoPallet = \Illuminate\Support\Facades\Request::input("NoPallet");
        $NoPallet_formateada = trim($NoPallet);
        $CodigoLote = \Illuminate\Support\Facades\Request::input("CodigoLote");
        $CodigoLote_formateada = trim($CodigoLote);

        $LoteId = \DB::select(

            \DB::raw(

                "SELECT * FROM Lotes WHERE LOT_CodigoLote = '".$CodigoLote_formateada."'"

            )

        );

        $PalletId = \DB::select(

            \DB::raw(

                "SELECT * FROM LotesPallets WHERE LPA_LOT_LoteId = '".$LoteId[0]->LOT_LoteId."' AND LPA_NumeroPallet = '".$NoPallet_formateada."'"

            )

        );

        $datosCaja = \DB::select(

            \DB::raw(

                "SELECT
                    LOT_FechaCaducidad,
                    ARTM_Nombre,
                    ART_Nombre,
                    LCA_PesoCaja,
                    LCA_NumeroCaja,
                    LCA_PesoCaja/LCA_PiezasCaja AS Promedio,
                    LPA_NumeroPallet,
                    LCA_PiezasCaja,
                    ART_CodigoArticulo,
                    LOT_CodigoLote,
                    LOT_CodigoLote + ART_CodigoArticulo AS CodigoBarras
                FROM LotesCajas
                INNER JOIN LotesPallets ON LPA_LotePalletId = LCA_LPA_LotePalletId
                INNER JOIN Lotes ON LOT_LoteId = LCA_LOT_LoteId
                INNER JOIN Articulos ON ART_ArticuloId = LOT_ART_ArticuloId
                INNER JOIN ArticulosMarcas ON ARTM_MarcaId = ART_ARTM_MarcaId
                WHERE LCA_LOT_LoteId = '".$LoteId[0]->LOT_LoteId."'
                AND LCA_LPA_LotePalletId = '".$PalletId[0]->LPA_LotePalletId."'
                AND LCA_NumeroCaja = '".$NoCaja_formateada."'"

            )

        );

        return $datosCaja;

    }

}
