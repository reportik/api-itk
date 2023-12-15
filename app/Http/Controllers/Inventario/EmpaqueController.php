<?php namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Compras\OrdenesCompras\OrdenesCompraController;
use App\Http\Controllers\Sistema\AutonumericoController;
use App\Http\Controllers\Sistema\DAOGeneralController;
use App\Http\Controllers\Sistema\DataBaseSession;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Request AS NewRequest;
use Illuminate\Http\Request;
use App\Mapeos\Controles\ControlesMaestrosEsquemas;
use App\Mapeos\Controles\ControlesMaestrosMultiples;
use App\Models\Articulos;
use App\Models\Bultos;
use App\Models\BultosDetalle;
use App\Models\BultosDetalleCaracteristicas;
use App\Models\Inventario\InventarioFisico\Articulo;
use App\Models\Inventario\InventarioFisico\DetallesMovimientoInventario;
use App\Models\Inventario\InventarioFisico\Empleado;
use App\Models\Inventario\InventarioFisico\ProcesadorMovimientoInventarios;
use App\Models\Inventario\InventarioFisico\TraspasoMovto;
use App\Models\Localidades;
use App\Models\Lotes;
use App\Models\LotesCajas;
use App\Models\LotesPallets;
use App\Models\OrdenesCompra;
use App\Models\OrdenesTrabajo;
use App\Models\OrdenesTrabajoDetalleArticulos;
use App\Models\OrdenesVenta;
use App\Models\OrdenesVentaDetalle;
use App\Models\Ventas\ListasPrecios\CMMult;
use Response;
require_once(public_path().'/plugins/PHPJasper/PHPJasper.php');

class EmpaqueController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    function __construct(){
        $this->dao = new DAOGeneralController();
    }

    public function index()
    {

        $version = $this->dao->nuevoId();
        $cantidad_decimales = \DB::select(\DB::raw("SELECT CMA_Valor FROM ControlesMaestros WHERE CMA_Control = 'CMA_CCXC_DecimalesCantidades'"))[0]->CMA_Valor;
        $precios_decimales = \DB::select(\DB::raw("SELECT CMA_Valor FROM ControlesMaestros WHERE CMA_Control = 'CMA_CCXC_DecimalesPrecios'"))[0]->CMA_Valor;
        $porcentaje_decimales = \DB::select(\DB::raw("SELECT CMA_Valor FROM ControlesMaestros WHERE CMA_Control = 'CMA_CCXC_DecimalesPorcentajes'"))[0]->CMA_Valor;
        $tc_decimales = \DB::select(\DB::raw("SELECT CMA_Valor FROM ControlesMaestros WHERE CMA_Control = 'CMA_CCXC_DecimalesTipoCambio'"))[0]->CMA_Valor;
        //$ordenesOT = array();
        //date_default_timezone_set('America/Mexico_City');
        $fecha = date('d/m/Y');
        $tipoEtiqueta = array();

        $tipoBulto = CMMult::select('CMM_ControlId','CMM_Valor')
            ->whereRaw("CMM_Control = 'CMM_TipoBulto' AND CMM_Eliminado = 0")
            ->orderBy('CMM_Valor','DESC')
            ->lists('CMM_Valor', 'CMM_ControlId');

        $caracteristasBulto = CMMult::select('CMM_ControlId','CMM_Valor')
            ->whereRaw("CMM_Control = 'CMM_CaracteristicasBulto' AND CMM_Eliminado = 0")
            ->orderBy('CMM_Valor','ASC')
            ->lists('CMM_Valor', 'CMM_ControlId');

        /*$ordenesOT = OrdenesTrabajo::select('OT_OrdenTrabajoId','OT_Codigo')
                ->whereRaw("OT_CMM_Estatus = '3C843D99-87A6-442C-8B89-1E49322B265A' AND OT_Eliminado = 0")
                ->orderBy('OT_Codigo','DESC')
                ->lists('OT_Codigo', 'OT_OrdenTrabajoId');*/

        $password = \DB::select(\DB::raw("SELECT CMM_Valor FROM ControlesMaestrosMultiples WHERE CMM_Etiqueta = 'Empaque'"))[0]->CMM_Valor;

        return view('Inventario.Empaque.crearEmpaque', compact('version','cantidad_decimales'
            ,'precios_decimales','porcentaje_decimales','tc_decimales','fecha','tipoEtiqueta','tipoBulto','caracteristasBulto','password'));

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

    public function cosultaDetalleOT(){

        try{

            ini_set('memory_limit', '-1');
            set_time_limit(0);

            $otId = NewRequest::input('otId');

            $consulta = \DB::select(
                \DB::raw(
                    "SELECT
                        OT_FechaRequeridaOV,
                        CMUM_Nombre,
                        OTDA_Cantidad,
                        OTDA_ART_ArticuloId,
                        CLI_NombreComercial,
                        CLI_RazonSocial,
                        CCON_Nombre,
                        PRY_NombreProyecto AS PRY_Descripcion,
                        ART_CodigoArticulo + ' - ' + ART_Nombre AS ART_FullName,
                        CLI_CodigoCliente,
                        OC_CodigoOC,
                        OT_Codigo,
                        ART_CodigoArticulo,
                        ART_GLN
                    FROM OrdenesTrabajo
                    LEFT JOIN Clientes ON CLI_ClienteId = OT_CLI_ClienteId
                    LEFT JOIN ClientesContactos ON CCON_CLI_ClienteId = CLI_ClienteId
                    --LEFT JOIN Eventos ON EV_EventoId = OT_EV_EventoId
                    LEFT JOIN Proyectos ON PRY_ProyectoId = OT_PRO_ProyectoId
                    INNER JOIN OrdenesTrabajoDetalleArticulos ON OTDA_OT_OrdenTrabajoId = OT_OrdenTrabajoId
                    INNER JOIN Articulos ON ART_ArticuloId = OTDA_ART_ArticuloId
                    INNER JOIN ControlesMaestrosUM ON CMUM_UnidadMedidaId = ART_CMUM_UMInventarioId
                    LEFT JOIN OrdenesCompraDetalle ON OCD_OT_OrdenTrabajoId = OT_OrdenTrabajoId
                    LEFT JOIN OrdenesCompra ON OC_OrdenCompraId = OCD_OC_OrdenCompraId
                    WHERE OT_OrdenTrabajoId = '".$otId."'
                    ORDER BY
                        OT_FechaRequeridaOV
                    ASC"
                )
            );

            /*$ajaxData = array();
            $ajaxData['data'] = $consulta;
            $ajaxData['options'] = array();
            $array = array();
            $array['ocd'] = json_encode($ajaxData);*/

            return $consulta;

        }catch(\Exception $e){

            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json; charset=UTF-8');
            die(json_encode(array("mensaje" => $e->getMessage(),
                "codigo" => $e->getCode(),
                "clase" => $e->getFile(),
                "linea" => $e->getLine())));

        }

    }

    public function cosultaDetalleOV(){

        try{

            ini_set('memory_limit', '-1');
            set_time_limit(0);

            $ovId = NewRequest::input('ovId');

            $consulta = \DB::select(
                \DB::raw(
                    "SELECT
                        OVD_DetalleId AS DT_RowId,
                        OVD_NumeroLinea,
                        OVD_Concepto,
                        OVD_CMUM_Nombre,
                        CAST(OVD_CantidadRequerida AS DECIMAL (28,3)) AS OVD_CantidadRequerida,
                        OVD_ART_ArticuloId
                    FROM OrdenesVentaDetalle
                    WHERE OVD_OV_OrdenVentaId = '".$ovId."'
                    ORDER BY
                        OVD_NumeroLinea
                    ASC"
                )
            );

            $ajaxData = array();
            $ajaxData['data'] = $consulta;
            $ajaxData['options'] = array();
            $array = array();
            $array['ovd'] = json_encode($ajaxData);

            return $array;

        }catch(\Exception $e){

            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json; charset=UTF-8');
            die(json_encode(array("mensaje" => $e->getMessage(),
                "codigo" => $e->getCode(),
                "clase" => $e->getFile(),
                "linea" => $e->getLine())));

        }

    }

    public function buscarBultosPorOT(){

        try{

            ini_set('memory_limit', '-1');
            set_time_limit(0);

            //date_default_timezone_set('America/Mexico_City');
            $fechaDia = date('d/m/Y');
            //$fechaDiaCompleta = date('d/m/Y H:i:s');

            $otId = NewRequest::input('otId');
            $artId = NewRequest::input('artId');
            $cantReq = NewRequest::input('cantidadRequrida');
            $mensaje = "";
            $lote = null;
            $consulta = null;

            //VERIFIAR SI EXISTE LOTE
            $buscaLote = \DB::select(
                \DB::raw(
                    "SELECT TOP 1
                        *
                    FROM Lotes
                    WHERE LOT_ART_ArticuloId = '".$artId."'
                    AND LOT_CMM_EstatusLoteId NOT IN ('35402538-09B8-403B-A798-7EB626525CF7','98344A16-D332-4282-BD71-ED4FCC468D2F','8601CEC0-3271-4EC6-B857-AE1D352208D8')
                    AND LOT_Eliminado = 0
                    AND LOT_FechaCaducidad IS NULL
                    AND LOT_LOTP_LotePreliminarId IS NULL
                    ORDER BY
                        LOT_FechaCreacion
                    DESC"
                )
            );

            //BUSCAR BULTOS CON EL LOTE
            $sumaTotalPiezasEnBulto = \DB::select(
                \DB::raw(
                    "SELECT
                        CAST(SUM(LCA_PiezasCaja) AS DECIMAL (28,3)) AS TOTAL
                    FROM OrdenesVentaDetalle
                    INNER JOIN Lotes ON LOT_ART_ArticuloId = OVD_ART_ArticuloId
                    INNER JOIN LotesCajas ON LCA_LOT_LoteId = LOT_LoteId
                    WHERE OVD_OV_OrdenVentaId = '".$otId."'
                    AND OVD_ART_ArticuloId = '".$artId."'
                    AND LOT_LOTP_LotePreliminarId IS NULL
                    AND LOT_FechaCaducidad IS NULL
                    AND LCA_Eliminado = 0"
                )
            );

            $cuentaBuscaLote = count($buscaLote);
            if($cuentaBuscaLote > 0){

                $lote = $buscaLote[0];

            }
            else{

                if(floatval($cantReq) - floatval($sumaTotalPiezasEnBulto[0]->TOTAL) > 0){

                    //CREAR NUEVO LOTE
                    $resultado = EmpaqueController::crearNuevoLote($artId);
                    if($resultado[1] == null){

                        if($resultado[0] != null){

                            $lote = $resultado[0];

                        }
                        else{

                            $mensaje = "No se creo el lote.";

                        }

                    }
                    else{

                        $mensaje = $resultado[1];

                    }

                }
                else{

                    //VERIFIAR SI EXISTE LOTE
                    $buscaLote2 = \DB::select(
                        \DB::raw(
                            "SELECT TOP 1
                                *
                            FROM Lotes
                            WHERE LOT_ART_ArticuloId = '".$artId."'
                            AND LOT_Eliminado = 0
                            AND LOT_FechaCaducidad IS NULL
                            AND LOT_LOTP_LotePreliminarId IS NULL
                            ORDER BY
                                LOT_FechaCreacion
                            DESC"
                        )
                    );

                    $cuentaBuscaLote2 = count($buscaLote2);
                    if($cuentaBuscaLote2 > 0){

                        $lote = $buscaLote2[0];

                    }
                    else{

                        $mensaje = 'No seencontró ningun lote.';

                    }

                }

            }
            //FIN VERIFICAR SI EXISTE LOTE

            if($lote != null){

                //BUSCAR BULTOS CON EL LOTE
                $consulta = \DB::select(
                    \DB::raw(
                        "SELECT
                            LCA_NumeroCaja,
	                        CAST(LCA_PiezasCaja AS DECIMAL (28,3)) AS LCA_PiezasCaja
                        FROM LotesCajas
                        WHERE LCA_LOT_LoteId = '".$lote->LOT_LoteId."'
                        AND LCA_Eliminado = 0
                        ORDER BY
                            LCA_FechaRegistro
                        ASC"
                    )
                );

            }
            else{

                $mensaje = "No hay lote.";

            }

            $ajaxData = array();
            $ajaxData['data'] = $consulta;
            $ajaxData['sumaTotalPiezasEnBulto'] = $sumaTotalPiezasEnBulto;
            $ajaxData['mensaje'] = $mensaje;
            $array = array();
            $array['bulto'] = json_encode($ajaxData);

            return $array;

        }catch(\Exception $e){

            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json; charset=UTF-8');
            die(json_encode(array("mensaje" => $e->getMessage(),
                "codigo" => $e->getCode(),
                "clase" => $e->getFile(),
                "linea" => $e->getLine())));

        }

    }

    public function buscarBultosPorOV(){

        try{

            ini_set('memory_limit', '-1');
            set_time_limit(0);

            //date_default_timezone_set('America/Mexico_City');
            $fechaDia = date('d/m/Y');
            //$fechaDiaCompleta = date('d/m/Y H:i:s');

            $ovId = NewRequest::input('ovId');
            $artId = NewRequest::input('artId');
            $cantReq = NewRequest::input('cantidadRequrida');
            $mensaje = "";
            $lote = null;
            $consulta = null;

            //VERIFIAR SI EXISTE LOTE
            $buscaLote = \DB::select(
                \DB::raw(
                    "SELECT TOP 1
                        *
                    FROM Lotes
                    WHERE LOT_ART_ArticuloId = '".$artId."'
                    AND LOT_CMM_EstatusLoteId NOT IN ('35402538-09B8-403B-A798-7EB626525CF7','98344A16-D332-4282-BD71-ED4FCC468D2F','8601CEC0-3271-4EC6-B857-AE1D352208D8')
                    AND LOT_Eliminado = 0
                    AND LOT_FechaCaducidad IS NULL
                    AND LOT_LOTP_LotePreliminarId IS NULL
                    ORDER BY
                        LOT_FechaCreacion
                    DESC"
                )
            );

            //BUSCAR BULTOS CON EL LOTE
            $sumaTotalPiezasEnBulto = \DB::select(
                \DB::raw(
                    "SELECT
                        CAST(SUM(LCA_PiezasCaja) AS DECIMAL (28,3)) AS TOTAL
                    FROM OrdenesVentaDetalle
                    INNER JOIN Lotes ON LOT_ART_ArticuloId = OVD_ART_ArticuloId
                    INNER JOIN LotesCajas ON LCA_LOT_LoteId = LOT_LoteId
                    WHERE OVD_OV_OrdenVentaId = '".$ovId."'
                    AND OVD_ART_ArticuloId = '".$artId."'
                    AND LOT_LOTP_LotePreliminarId IS NULL
                    AND LOT_FechaCaducidad IS NULL
                    AND LCA_Eliminado = 0"
                )
            );

            $cuentaBuscaLote = count($buscaLote);
            if($cuentaBuscaLote > 0){

                $lote = $buscaLote[0];

            }
            else{

                if(floatval($cantReq) - floatval($sumaTotalPiezasEnBulto[0]->TOTAL) > 0){

                    //CREAR NUEVO LOTE
                    $resultado = EmpaqueController::crearNuevoLote($artId);
                    if($resultado[1] == null){

                        if($resultado[0] != null){

                            $lote = $resultado[0];

                        }
                        else{

                            $mensaje = "No se creo el lote.";

                        }

                    }
                    else{

                        $mensaje = $resultado[1];

                    }

                }
                else{

                    //VERIFIAR SI EXISTE LOTE
                    $buscaLote2 = \DB::select(
                        \DB::raw(
                            "SELECT TOP 1
                                *
                            FROM Lotes
                            WHERE LOT_ART_ArticuloId = '".$artId."'
                            AND LOT_Eliminado = 0
                            AND LOT_FechaCaducidad IS NULL
                            AND LOT_LOTP_LotePreliminarId IS NULL
                            ORDER BY
                                LOT_FechaCreacion
                            DESC"
                                )
                            );

                    $cuentaBuscaLote2 = count($buscaLote2);
                    if($cuentaBuscaLote2 > 0){

                        $lote = $buscaLote2[0];

                    }
                    else{

                        $mensaje = 'No seencontró ningun lote.';

                    }

                }

            }
            //FIN VERIFICAR SI EXISTE LOTE

            if($lote != null){

                //BUSCAR BULTOS CON EL LOTE
                $consulta = \DB::select(
                    \DB::raw(
                        "SELECT
                            LCA_NumeroCaja,
	                        CAST(LCA_PiezasCaja AS DECIMAL (28,3)) AS LCA_PiezasCaja
                        FROM LotesCajas
                        WHERE LCA_LOT_LoteId = '".$lote->LOT_LoteId."'
                        AND LCA_Eliminado = 0
                        ORDER BY
                            LCA_FechaRegistro
                        ASC"
                    )
                );

            }
            else{

                $mensaje = "No hay lote.";

            }

            $ajaxData = array();
            $ajaxData['data'] = $consulta;
            $ajaxData['sumaTotalPiezasEnBulto'] = $sumaTotalPiezasEnBulto;
            $ajaxData['mensaje'] = $mensaje;
            $array = array();
            $array['bulto'] = json_encode($ajaxData);

            return $array;

        }catch(\Exception $e){

            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json; charset=UTF-8');
            die(json_encode(array("mensaje" => $e->getMessage(),
                "codigo" => $e->getCode(),
                "clase" => $e->getFile(),
                "linea" => $e->getLine())));

        }

    }

    public function crearNuevoLote($artId){

        //date_default_timezone_set('America/Mexico_City');
        $fechaDia = date('d/m/Y');
        $fechaDiaRevez = date('Y/m/d');
        $hoy=date('d/m/Y H:i:s');
        $lote = null;
        $bandera = 0;
        $mensaje = "";
        $dia = null;
        $departamento =null;
        $pasta = null;
        $presentacion = null;

        //BUSCAR FECHA Y NUMERO DE INICIO DE CALENDARIO
        $buscaFechaYNumeroInicio = \DB::select(
            \DB::raw(
                "SELECT CMM_Valor,CMM_Referencia FROM ControlesMaestrosMultiples WHERE CMM_Control = 'CMM_NumeracionLote'"
            )
        );

        //SACAR EL NUMERO DEL DIA CORRESPONDIENTE
        $cuentaBuscaFechaYNumeroInicio = count($buscaFechaYNumeroInicio);
        if($cuentaBuscaFechaYNumeroInicio > 0){

            $fechaInicio = $buscaFechaYNumeroInicio[0]->CMM_Referencia;
            $numeroInicio = $buscaFechaYNumeroInicio[0]->CMM_Valor;

            if($fechaInicio != "" && $numeroInicio != "")
            {

                //SACAR NUMERACION DEL DIA SELECCIONADO
                $diasLaborales = \DB::select(
                    \DB::raw(
                        "SELECT THEDATE, DATEPART(DW,thedate) as NUMERO_DIA, DNL_Eliminado
                        FROM dbo.ExplodeDates('".$fechaInicio."','".$fechaDia."') as d
                    LEFT JOIN DiasNoLaborales ON DNL_Fecha = THEDATE
                    WHERE thedate not in (
                        SELECT DNL_Fecha
                        FROM DiasNoLaborales
                        WHERE DNL_Eliminado = 0
                    )"
                    )
                );

                $cuentaDiasLaborales = count($diasLaborales);
                if($cuentaDiasLaborales > 0){

                    $contadorNumero = $numeroInicio;
                    $valorFinalContador = "";
                    for($x = 0; $x < $cuentaDiasLaborales; $x ++)
                    {

                        $diaDomingo = $diasLaborales[$x]->NUMERO_DIA;
                        $diaEliminado = $diasLaborales[$x]->DNL_Eliminado;
                        $datetime1 = strtotime($fechaDiaRevez);
                        $datetime2 = strtotime($diasLaborales[$x]->THEDATE);
                        if($datetime1 == $datetime2){

                            $valorFinalContador = $contadorNumero;

                        }
                        if($contadorNumero >= 999){

                            $contadorNumero = 1;

                        }
                        else{

                            if($diaDomingo != 7){

                                $contadorNumero++;

                            }
                            elseif($diaDomingo == 7 && $diaEliminado == 1){

                                $contadorNumero++;

                            }

                        }

                    }

                    $dia = $valorFinalContador;
                    if($dia != "")
                    {

                        if((int)$dia < 10)$dia = "00" . $dia;
                        if((int)$dia > 9 && (int)$dia < 100)$dia = "0" . $dia;

                    }
                    else{

                        $mensaje = "El día calculado esta null";

                    }

                }
                else{

                    $mensaje = "No hay días laborales.";

                }

            }
            else{

                if($fechaInicio == "" && $numeroInicio != ""){

                    $mensaje = "No existe fecha de Inicio en el calendario.";

                }
                elseif($numeroInicio == "" && $fechaInicio != ""){

                    $mensaje = "No existe numero de inicio en el calendario.";

                }
                else{

                    $mensaje = "No existe fecha y numero de inicio en el calendario.";

                }

            }

        }
        else{

            $mensaje = "No hay fecha y numero de inicio de calendario.";

        }
        //FIN SACAR EL NUMERO DEL DIA CORRESPONDIENTE

        //VALIDAR DIA
        if($dia != null){

            //CONSULTAR ARTICULO
            $articulo = Articulos::find($artId);

            //VALIDAR DEPARTAMENTO
            if($articulo->ART_Departamento != null){

                $departamento = $articulo->ART_Departamento;

            }
            else{

                $bandera = 1;
                $mensaje = "El articulo no tiene departamento.";

            }

            //VALIDAR PASTA
            if($articulo->ART_Pasta != null){

                $pasta = $articulo->ART_Pasta;

            }
            else{

                $bandera = 1;
                $mensaje = "El articulo no tiene pasta.";

            }

            //VALIDAR PRESENTACION
            if($articulo->ART_Presentacion != null){

                $presentacion = $articulo->ART_Presentacion;

            }
            else{

                $bandera = 1;
                $mensaje = "El articulo no tiene presentacion.";

            }


        }
        else{

            $bandera = 1;
            $mensaje = "No se calculo bien el día.";

        }
        //FIN VALIDAR DIA

        if($bandera == 0){

            //CREAR CODIGO LOTE
            $fechaCortada = explode("/", $fechaDia);
            $diaF = $fechaCortada[0];
            $mesF = $fechaCortada[1];

            $codigoLote = $dia.$departamento.$pasta.$presentacion.$diaF.$mesF;
            $lote = EmpaqueController::insertarNuevoLote($dia,$artId,$codigoLote,$hoy);

        }
        else{

            $mensaje = "No se puede crear el Lote";

        }

        return [$lote, $mensaje];

    }

    public function insertarNuevoLote($dia,$artId,$codigoLote,$hoy){

        $lote = new Lotes();
        $lote->LOT_NumeroLote = $dia;
        $lote->LOT_ART_ArticuloId = $artId;
        $lote->LOT_CodigoLote = $codigoLote;
        $lote->LOT_FechaLote = $hoy;
        $lote->LOT_CMM_EstatusLoteId = '362B0AC5-85A1-4DB1-A725-DA1C64702E7D'; //abierto
        //$lote->LOT_CMM_EstatusLoteId = '5F608B87-8FD8-4A0A-8C41-BFFAEAAC211F'; //empacado
        $lote->save();

        $loteInsertado = EmpaqueController::consultaLotePorCodigo($codigoLote);
        $lote = Lotes::find($loteInsertado[0]->LOT_LoteId);

        return $lote;

    }

    public function consultaLotePorCodigo($codigoLote){

        $consulta = \DB::select(
            \DB::raw(
                "SELECT
                    *
                FROM Lotes
                WHERE LOT_CodigoLote = '".$codigoLote."'"
            )
        );

        return $consulta;

    }

    public function eliminarBulto(){

        try{

            ini_set('memory_limit', '-1');
            set_time_limit(0);

            //date_default_timezone_set('America/Mexico_City');
            $fechaDia = date('d/m/Y');
            $hoy = date('d/m/Y H:i:s');
            $mensaje = "";
            $lote = null;
            $bandera = 0;

            $ovId = NewRequest::input('ovId');
            $artId = NewRequest::input('artId');
            $numeroBulto = NewRequest::input('numeroBulto');

            //VERIFIAR SI EXISTE LOTE
            $buscaLote = \DB::select(
                \DB::raw(
                    "SELECT TOP 1
                        *
                    FROM Lotes
                    WHERE LOT_ART_ArticuloId = '".$artId."'
                    AND LOT_CMM_EstatusLoteId NOT IN ('35402538-09B8-403B-A798-7EB626525CF7','98344A16-D332-4282-BD71-ED4FCC468D2F','8601CEC0-3271-4EC6-B857-AE1D352208D8')
                    AND LOT_Eliminado = 0
                    AND LOT_FechaCaducidad IS NULL
                    AND LOT_LOTP_LotePreliminarId IS NULL
                    ORDER BY
                        LOT_FechaCreacion
                    DESC"
                )
            );

            $cuentaBuscaLote = count($buscaLote);
            if($cuentaBuscaLote > 0){

                $lote = $buscaLote[0];

                //VERIFICAR SI YA HAY REGISTRO
                $buscaCaja = \DB::select(
                    \DB::raw(
                        "SELECT
                            *
                        FROM LotesCajas
                        INNER JOIN Lotes ON LOT_LoteId = LCA_LOT_LoteId
                        WHERE LCA_LOT_LoteId = '".$lote->LOT_LoteId."'
                        AND LCA_NumeroCaja = ".$numeroBulto."
                        AND LCA_Eliminado = 0"
                    )
                );

                $cuentaBuscaCaja = count($buscaCaja);
                if($cuentaBuscaCaja > 0){

                    $bandera = 1;

                    $lotesCaja = LotesCajas::find($buscaCaja[0]->LCA_LoteCajaId);
                    $lotesCaja->LCA_Eliminado = 1;
                    $lotesCaja->LCA_FechaUltimaModificacion = $hoy;
                    $lotesCaja->save();

                }
                else{

                    $bandera = 0;

                }

                if($bandera == 1){

                    //CONSULTA DATOS ACTUALIZADO
                    $consulta = \DB::select(
                        \DB::raw(
                            "SELECT
                               OV_CodigoOV,
                               CLI_CodigoCliente + ' - ' + CLI_RazonSocial AS CLI_Nombre,
                               PRY_Descripcion,
                               ART_CodigoArticulo,
                               ART_CodigoArticulo + ' - ' + OVD_Concepto AS OVD_Concepto,
                               LOT_CodigoLote,
                               LCA_NumeroCaja,
                               LCA_PiezasCaja
                            FROM OrdenesVenta
                            INNER JOIN Clientes ON CLI_ClienteId = OV_CLI_ClienteId
                            LEFT JOIN Proyectos ON PRY_ProyectoId = OV_PRO_ProyectoId
                            INNER JOIN OrdenesVentaDetalle ON OVD_OV_OrdenVentaId = OV_OrdenVentaId
                            INNER JOIN Articulos ON ART_ArticuloId = OVD_ART_ArticuloId
                            LEFT JOIN Lotes ON LOT_ART_ArticuloId = OVD_ART_ArticuloId
                            LEFT JOIN LotesCajas ON LOT_LoteId = LCA_LOT_LoteId
                            WHERE OV_OrdenVentaId = '".$ovId."'
                    AND LOT_LoteId = '".$lote->LOT_LoteId."'
                    AND LCA_NumeroCaja = ".$numeroBulto.""
                        )
                    );

                }
                else{

                    //CONSULTA DATOS ULTIMO INSERTADO
                    $consulta = \DB::select(
                        \DB::raw(
                            "SELECT
                               OV_CodigoOV,
                               CLI_CodigoCliente + ' - ' + CLI_RazonSocial AS CLI_Nombre,
                               PRY_Descripcion,
                               ART_CodigoArticulo,
                               ART_CodigoArticulo + ' - ' + OVD_Concepto AS OVD_Concepto,
                               LOT_CodigoLote,
                               LCA_NumeroCaja,
                               LCA_PiezasCaja
                            FROM OrdenesVenta
                            INNER JOIN Clientes ON CLI_ClienteId = OV_CLI_ClienteId
                            LEFT JOIN Proyectos ON PRY_ProyectoId = OV_PRO_ProyectoId
                            INNER JOIN OrdenesVentaDetalle ON OVD_OV_OrdenVentaId = OV_OrdenVentaId
                            INNER JOIN Articulos ON ART_ArticuloId = OVD_ART_ArticuloId
                            LEFT JOIN Lotes ON LOT_ART_ArticuloId = OVD_ART_ArticuloId
                            LEFT JOIN LotesCajas ON LOT_LoteId = LCA_LOT_LoteId
                            WHERE OV_OrdenVentaId = '".$ovId."'
                            AND LOT_LoteId = '".$lote->LOT_LoteId."'"
                        )
                    );

                }

                //BUSCAR BULTOS CON EL LOTE
                $sumaTotalPiezasEnBulto = \DB::select(
                    \DB::raw(
                        "SELECT
                            CAST(SUM(LCA_PiezasCaja) AS DECIMAL (28,3)) AS TOTAL
                        FROM OrdenesVentaDetalle
                        INNER JOIN Lotes ON LOT_ART_ArticuloId = OVD_ART_ArticuloId
                        INNER JOIN LotesCajas ON LCA_LOT_LoteId = LOT_LoteId
                        WHERE OVD_OV_OrdenVentaId = '".$ovId."'
                        AND OVD_ART_ArticuloId = '".$artId."'
                        AND LOT_LOTP_LotePreliminarId IS NULL
                        AND LOT_FechaCaducidad IS NULL
                        AND LCA_Eliminado = 0"
                    )
                );

            }
            else{

                $mensaje = "No existe lote";

            }
            //FIN VERIFICAR SI EXISTE LOTE

            $ajaxData = array();
            $ajaxData['data'] = $consulta;
            $ajaxData['sumaTotalPiezasEnBulto'] = $sumaTotalPiezasEnBulto;
            $ajaxData['mensaje'] = $mensaje;
            $array = array();
            $array['respuesta'] = json_encode($ajaxData);

            return $array;

        }catch(\Exception $e){

            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json; charset=UTF-8');
            die(json_encode(array("mensaje" => $e->getMessage(),
                "codigo" => $e->getCode(),
                "clase" => $e->getFile(),
                "linea" => $e->getLine())));

        }

    }

    public function guadaBulto(){

        try{

            ini_set('memory_limit', '-1');
            set_time_limit(0);

            //date_default_timezone_set('America/Mexico_City');
            $fechaDia = date('d/m/Y');
            $hoy = date('d/m/Y H:i:s');
            $mensaje = "";
            $lote = null;
            $bandera = 0;

            $ovId = NewRequest::input('ovId');
            $artId = NewRequest::input('artId');
            $numeroBulto = NewRequest::input('numeroBulto');
            $piezasBulto = NewRequest::input('piezasBulto');
            $cantReq = NewRequest::input('cantidadRequrida');

            //VERIFIAR SI EXISTE LOTE
            $buscaLote = \DB::select(
                \DB::raw(
                    "SELECT TOP 1
                        *
                    FROM Lotes
                    WHERE LOT_ART_ArticuloId = '".$artId."'
                    AND LOT_CMM_EstatusLoteId NOT IN ('35402538-09B8-403B-A798-7EB626525CF7','98344A16-D332-4282-BD71-ED4FCC468D2F','8601CEC0-3271-4EC6-B857-AE1D352208D8')
                    AND LOT_Eliminado = 0
                    AND LOT_FechaCaducidad IS NULL
                    AND LOT_LOTP_LotePreliminarId IS NULL
                    ORDER BY
                        LOT_FechaCreacion
                    DESC"
                )
            );

            $cuentaBuscaLote = count($buscaLote);
            if($cuentaBuscaLote > 0){

                $lote = $buscaLote[0];

                //VERIFICAR SI YA HAY REGISTRO
                $buscaCaja = \DB::select(
                    \DB::raw(
                        "SELECT
                            *
                        FROM LotesCajas
                        INNER JOIN Lotes ON LOT_LoteId = LCA_LOT_LoteId
                        WHERE LCA_LOT_LoteId = '".$lote->LOT_LoteId."'
                        AND LCA_NumeroCaja = ".$numeroBulto."
                        AND LCA_Eliminado = 0"
                    )
                );

                $cuentaBuscaCaja = count($buscaCaja);
                if($cuentaBuscaCaja > 0){

                    $bandera = 1;

                    $lotesCaja = LotesCajas::find($buscaCaja[0]->LCA_LoteCajaId);
                    $lotesCaja->LCA_PiezasCaja = floatval($piezasBulto);
                    $lotesCaja->LCA_FechaUltimaModificacion = $hoy;
                    $lotesCaja->save();

                }
                else{

                    $bandera = 0;

                    $lotesCaja = new LotesCajas();
                    $lotesCaja->LCA_LOT_LoteId = $lote->LOT_LoteId;
                    $lotesCaja->LCA_NumeroCaja = intval($numeroBulto);
                    $lotesCaja->LCA_PiezasCaja = floatval($piezasBulto);
                    $lotesCaja->save();

                }

                if($bandera == 1){

                    //CONSULTA DATOS ACTUALIZADO
                    $consulta = \DB::select(
                        \DB::raw(
                            "SELECT
                               OV_CodigoOV,
                               OV_ReferenciaOC,
                               CLI_CodigoCliente,
                               CLI_NombreComercial,
                               CLI_RazonSocial,
                               CCON_Nombre,
                               PRY_Descripcion,
                               ART_CodigoArticulo,
                               ART_CodigoArticulo + ' - ' + OVD_Concepto AS OVD_Concepto,
                               LOT_CodigoLote,
                               LCA_NumeroCaja,
                               LCA_PiezasCaja
                            FROM OrdenesVenta
                            INNER JOIN Clientes ON CLI_ClienteId = OV_CLI_ClienteId
                            LEFT JOIN ClientesContactos ON CCON_CLI_ClienteId = CLI_ClienteId
                            LEFT JOIN Proyectos ON PRY_ProyectoId = OV_PRO_ProyectoId
                            INNER JOIN OrdenesVentaDetalle ON OVD_OV_OrdenVentaId = OV_OrdenVentaId
                            INNER JOIN Articulos ON ART_ArticuloId = OVD_ART_ArticuloId
                            LEFT JOIN Lotes ON LOT_ART_ArticuloId = OVD_ART_ArticuloId
                            LEFT JOIN LotesCajas ON LOT_LoteId = LCA_LOT_LoteId
                            WHERE OV_OrdenVentaId = '".$ovId."'
                            AND LOT_LoteId = '".$lote->LOT_LoteId."'
                            AND LCA_NumeroCaja = ".$numeroBulto.""
                        )
                    );

                }
                else{

                    //CONSULTA DATOS ULTIMO INSERTADO
                    $consulta = \DB::select(
                        \DB::raw(
                            "SELECT
                               OV_CodigoOV,
                               OV_ReferenciaOC,
                               CLI_CodigoCliente,
                               CLI_NombreComercial,
                               CLI_RazonSocial,
                               CCON_Nombre,
                               PRY_Descripcion,
                               ART_CodigoArticulo,
                               ART_CodigoArticulo + ' - ' + OVD_Concepto AS OVD_Concepto,
                               LOT_CodigoLote,
                               LCA_NumeroCaja,
                               LCA_PiezasCaja
                            FROM OrdenesVenta
                            INNER JOIN Clientes ON CLI_ClienteId = OV_CLI_ClienteId
                            LEFT JOIN ClientesContactos ON CCON_CLI_ClienteId = CLI_ClienteId
                            LEFT JOIN Proyectos ON PRY_ProyectoId = OV_PRO_ProyectoId
                            INNER JOIN OrdenesVentaDetalle ON OVD_OV_OrdenVentaId = OV_OrdenVentaId
                            INNER JOIN Articulos ON ART_ArticuloId = OVD_ART_ArticuloId
                            LEFT JOIN Lotes ON LOT_ART_ArticuloId = OVD_ART_ArticuloId
                            LEFT JOIN LotesCajas ON LOT_LoteId = LCA_LOT_LoteId
                            WHERE OV_OrdenVentaId = '".$ovId."'
                            AND LOT_LoteId = '".$lote->LOT_LoteId."'
                            AND LCA_Eliminado = 0
                            ORDER BY
                                LCA_NumeroCaja
                            ASC"
                        )
                    );

                }

                //BUSCAR BULTOS CON EL LOTE
                $sumaTotalPiezasEnBulto = \DB::select(
                    \DB::raw(
                        "SELECT
                            CAST(SUM(LCA_PiezasCaja) AS DECIMAL (28,3)) AS TOTAL
                        FROM OrdenesVentaDetalle
                        INNER JOIN Lotes ON LOT_ART_ArticuloId = OVD_ART_ArticuloId
                        INNER JOIN LotesCajas ON LCA_LOT_LoteId = LOT_LoteId
                        WHERE OVD_OV_OrdenVentaId = '".$ovId."'
                        AND OVD_ART_ArticuloId = '".$artId."'
                        AND LOT_LOTP_LotePreliminarId IS NULL
                        AND LOT_FechaCaducidad IS NULL
                        AND LCA_Eliminado = 0"
                    )
                );

                if(floatval($cantReq) - floatval($sumaTotalPiezasEnBulto[0]->TOTAL) <= 0){

                    //CAMBIAR ESTATUS LOTE A CERRADO
                    $lote = Lotes::find($lote->LOT_LoteId);
                    //$lote->LOT_CMM_EstatusLoteId = '35402538-09B8-403B-A798-7EB626525CF7';//CERRADO
                    $lote->LOT_CMM_EstatusLoteId = '5F608B87-8FD8-4A0A-8C41-BFFAEAAC211F';//EMPACADO
                    $lote->LOT_FechaUltimaModificacion = $hoy;
                    $lote->save();

                }

            }
            else{

                $mensaje = "No existe lote";

            }
            //FIN VERIFICAR SI EXISTE LOTE

            $ajaxData = array();
            $ajaxData['data'] = $consulta;
            $ajaxData['sumaTotalPiezasEnBulto'] = $sumaTotalPiezasEnBulto;
            $ajaxData['mensaje'] = $mensaje;
            $array = array();
            $array['respuesta'] = json_encode($ajaxData);

            return $array;

        }catch(\Exception $e){

            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json; charset=UTF-8');
            die(json_encode(array("mensaje" => $e->getMessage(),
                "codigo" => $e->getCode(),
                "clase" => $e->getFile(),
                "linea" => $e->getLine())));

        }

    }

    public static function BuscarLotesAbiertos(){

        //date_default_timezone_set('America/Mexico_City');
        $hoy=date('d/m/Y');

        $idEmpleado = DataBaseSession::getEmpleadoId();
        //$idEmpleado = '32423501-373E-4958-A9FE-A34922AF7FFF';
        //$idEmpleado = 'B6C2B756-CA2B-445F-90B3-ADF440C97C90';

        $lineaProduccion = EmpaqueController::buscarLineaProduccionEmpleado($idEmpleado);

        $consultaLotesAbiertos = \DB::select(

            \DB::raw(

                "SELECT ART_Nombre, ART_CodigoArticulo, LOTP_CodigoLotePreliminar, LOTP_FechaLotePreliminar, ART_DiasVidaAnaquel,
                ART_CantidadCajasEnPallet, ART_CantidadUMEmpaqueEnCaja, APC_PesoInicial, APC_PesoFinal, ARTM_Nombre,
                ART_ArticuloId, LOTP_LotePreliminarId, ART_IncluirEmpaque
                FROM LotesPreliminares
                INNER JOIN Articulos ON ART_ArticuloId = LOTP_ART_ArticuloId
                INNER JOIN ArticulosParametrosCalidad ON ART_ArticuloId = APC_ART_ArticuloId
                INNER JOIN ArticulosMarcas ON ART_ARTM_MarcaId = ARTM_MarcaId
                INNER JOIN LineasProduccionArticulos ON LPA_ART_ArticuloId = ART_ArticuloId
                WHERE LOTP_CMM_EstatusLotePreliminarId = '362B0AC5-85A1-4DB1-A725-DA1C64702E7D'
                AND LOTP_FechaLotePreliminar <= '".$hoy."'
                AND LPA_LIP_LineaProduccionId = '".$lineaProduccion[0]->EMP_LIP_LineaProduccionId."'
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
                AND ART_Activo = 1
                AND ART_Eliminado = 0
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

            $idEmpleado = DataBaseSession::getEmpleadoId();
            //$idEmpleado = '32423501-373E-4958-A9FE-A34922AF7FFF';
            //$idEmpleado = 'B6C2B756-CA2B-445F-90B3-ADF440C97C90';

            $lineaProduccion = EmpaqueController::buscarLineaProduccionEmpleado($idEmpleado);

            $idLote = EmpaqueController::BuscaLotePorCodigo($codigoLote);

            $UltimoPalletRegistradoId = LotesPallets::where('LPA_LOT_LoteId','=',$idLote[0]->LOT_LoteId)
                ->where('LPA_LIP_LineaProduccionId','=',$lineaProduccion[0]->EMP_LIP_LineaProduccionId)
                ->orderby('LPA_FechaRegistro', 'DESC')->first()->LPA_LotePalletId;

            \DB::table('LotesPallets')->where('LPA_LotePalletId', '=', $UltimoPalletRegistradoId)
                ->update(
                    array(
                        'LPA_CMM_EstatusId' => ControlesMaestrosMultiples::$CMM_INV_EstatusPallet_Cerrado,
                        'LPA_EMP_ModificadoPorId' => $idEmpleado,
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

            $idEmpleado = DataBaseSession::getEmpleadoId();
            //$idEmpleado = '32423501-373E-4958-A9FE-A34922AF7FFF';
            //$idEmpleado = 'B6C2B756-CA2B-445F-90B3-ADF440C97C90';

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
                            'LPA_EMP_ModificadoPorId' => $idEmpleado,
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
                            'LPA_EMP_ModificadoPorId' => $idEmpleado,
                            'LPA_FechaUltimaModificacion' => $hoy
                        )
                    );

                $UltimoPalletRegistrado = LotesPallets::where('LPA_LOT_LoteId','=',$idLote[0]->LOT_LoteId)
                    ->orderby(\DB::raw('CAST(LPA_NumeroPallet AS Int)'), 'DESC')->first()->LPA_NumeroPallet;

                //verificar status del lote este como rebido, si es así cambiarlo a recibido-parcial
                if($idLote[0]->LOT_CMM_EstatusLoteId == '98344A16-D332-4282-BD71-ED4FCC468D2F')//recibido
                {

                    \DB::table('Lotes')->where('LOT_LoteId', '=', $idLote[0]->LOT_LoteId)
                        ->update(
                            array(
                                'LOT_CMM_EstatusLoteId' => '8601CEC0-3271-4EC6-B857-AE1D352208D8',
                                'LOT_EMP_ModificadoPorId' => $idEmpleado,
                                'LOT_FechaUltimaModificacion' => $hoy
                            )
                        );

                }

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

    public function cerrarLote($codigoLote){

        try {

            //date_default_timezone_set('America/Mexico_City');
            $hoy=date('d/m/Y H:i:s');
            $idEmpleado = DataBaseSession::getEmpleadoId();

            $idLote = EmpaqueController::BuscaLotePorCodigo($codigoLote);

            $cuentaIdLote = count($idLote);

            if($cuentaIdLote > 0)
            {

                \DB::table('Lotes')->where('LOT_LoteId', '=', $idLote[0]->LOT_LoteId)
                    ->update(
                        array(
                            'LOT_CMM_EstatusLoteId' => ControlesMaestrosMultiples::$CMM_EstatusLote_Cerrado,
                            'LOT_Cerrado' => 1,
                            'LOT_EMP_ModificadoPorId' => $idEmpleado,
                            'LOT_FechaUltimaModificacion' => $hoy
                        )
                    );

                $mensaje = 'Se registró el Pallet con éxito.';


            }
            else
            {

                $mensaje = 'El Lote '.$codigoLote.' no existe. No se registró con éxito.';

            }

            \DB::commit();

            return ['Status' => 'Valido', 'Mensaje' => $mensaje];

        }
        catch (\Exception $e) {

            \DB::rollback();

            return ['Status' => 'Error', 'Mensaje' => 'No se registró. Ocurrió un error al realizar el proceso. Error: '.$e->getMessage()];

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
        $hoy=date('d/m/Y H:i:s');
        $hoy2=date('Y-m-d H:i:s');
        $time = time();
        $horaActual = date("H:i:s", $time);
        $dia = date('d');
        $mes = date('m');
        $ano = date('Y');
        $cortaAno = substr($ano, -2);
        //$parte2Lote = $dia.$mes.$cortaAno;
        $parte2Lote = $dia.$mes;

        $idEmpleado = DataBaseSession::getEmpleadoId();
        //$idEmpleado = '32423501-373E-4958-A9FE-A34922AF7FFF';
        //$idEmpleado = 'B6C2B756-CA2B-445F-90B3-ADF440C97C90';

        $codigoLote = $_POST['arreglo'][2].$parte2Lote;

        $idLote = EmpaqueController::BuscaLotePorCodigo($codigoLote);

        $cuentaIdLote = count($idLote);

        $bandera = 0; $idLoteEncontrado = "";

        if($cuentaIdLote > 0)
        {

            //VERIFICAR SI EL ULTIMO LOTE ABIERTO PARA CERRARLO
            $fecha_actual = strtotime($hoy2);
            $nuevafecha = strtotime ( '+1 day' , strtotime ( $idLote[0]->LOT_FechaCreacion ) ) ;
            $nuevafecha = date ( 'Y-m-d 03:59:59' , $nuevafecha );
            $fecha_entrada = strtotime($nuevafecha);

            if($fecha_actual > $fecha_entrada)
            {

                //NADA

            }
            else
            {

                $bandera = 1;
                $idLoteEncontrado = $idLote[0]->LOT_LoteId;

            }

        }
        else
        {

            //CONSULTA NOCTURNA
            $consultaNocturna = \DB::select(

                \DB::raw(

                    "SELECT TOP 1
                        EMP_LIP_LineaProduccionId,
                        LIP_Nombre,
                        LOT_LoteId,
                        LOT_CodigoLote,
                        LOT_FechaCreacion,
                        LOT_CMM_EstatusLoteId,
                        LOT_Cerrado,
                        CMM_Valor
                    FROM Empleados
                    INNER JOIN LineasProduccion ON LIP_LineaProduccionId = EMP_LIP_LineaProduccionId
                    INNER JOIN LineasProduccionArticulos ON LIP_LineaProduccionId = LPA_LIP_LineaProduccionId
                    INNER JOIN Lotes ON LOT_ART_ArticuloId = LPA_ART_ArticuloId
                    INNER JOIN ControlesMaestrosMultiples ON CMM_ControlId = LOT_CMM_EstatusLoteId
                    WHERE EMP_EmpleadoId = '".$idEmpleado."'
                    AND LOT_ART_ArticuloId = '".$_POST['arreglo'][11]."'
                    AND LOT_FechaCreacion BETWEEN DATEADD(dd,DATEDIFF(dd,0,GETDATE()),-1) AND DATEADD(ms,-3,DATEADD(dd,DATEDIFF(dd,0,GETDATE()),0))
                    GROUP BY
                        EMP_LIP_LineaProduccionId,
                        LIP_Nombre,
                        LOT_LoteId,
                        LOT_CodigoLote,
                        LOT_FechaCreacion,
                        LOT_CMM_EstatusLoteId,
                        LOT_Cerrado,
                        CMM_Valor
                    ORDER BY
                        LOT_FechaCreacion DESC"

                )

            );

            $cuentaConsultaNocturna = count($consultaNocturna);

            //VERIFICAR SI EL ULTIMO LOTE ABIERTO PARA CERRARLO
            $fecha_actual = strtotime($hoy2);
            $nuevafecha = date ( 'Y-m-d 03:59:59');
            $fecha_entrada = strtotime($nuevafecha);

            if($fecha_actual > $fecha_entrada)
            {

                if($cuentaConsultaNocturna > 0)
                {

                    //CERRAR PALLET
                    \DB::table('LotesPallets')->where('LPA_LOT_LoteId', '=', $consultaNocturna[0]->LOT_LoteId)
                        ->where('LPA_CMM_EstatusId', '=', '0B0D3E21-E967-47C0-9E7E-34DBB9C6B5C4')//PALLET ABIERTO
                        ->update(
                            array(
                                'LPA_CMM_EstatusId' => 'E4AF6E7F-5542-45D3-85CD-137C124109FB',//ESTATUS CERRADO
                                'LPA_EMP_ModificadoPorId' => $idEmpleado,
                                'LPA_FechaUltimaModificacion' => $hoy
                            )
                        );

                    //CERRAR LOTE
                    \DB::table('Lotes')->where('LOT_LoteId', '=', $consultaNocturna[0]->LOT_LoteId)
                        ->update(
                            array(
                                'LOT_CMM_EstatusLoteId' => ControlesMaestrosMultiples::$CMM_EstatusLote_Cerrado,
                                'LOT_Cerrado' => 1,
                                'LOT_EMP_ModificadoPorId' => $idEmpleado,
                                'LOT_FechaUltimaModificacion' => $hoy
                            )
                        );
                    $consultaNocturna[0]->LOT_CMM_EstatusLoteId = ControlesMaestrosMultiples::$CMM_EstatusLote_Cerrado;

                }

            }

            if($cuentaConsultaNocturna <= 0)
            {

                $bandera = 0;

            }
            else
            {

                //VERIFICAR SI EL LOTE ESTA ABIERTO
                if($consultaNocturna[0]->LOT_CMM_EstatusLoteId == ControlesMaestrosMultiples::$CMM_EstatusLote_Cerrado)
                {

                    $bandera = 0;

                }
                else
                {

                    $bandera = 1;
                    $idLoteEncontrado = $consultaNocturna[0]->LOT_LoteId;

                }

            }

            /*
            //VERIFICAR SI EL ULTIMO LOTE ABIERTO PARA CERRARLO
            $fecha_actual = strtotime($hoy2);
            $nuevafecha = date ( 'Y-m-d 03:59:59');
            $fecha_entrada = strtotime($nuevafecha);

            if($fecha_actual > $fecha_entrada)
            {

                \DB::table('Lotes')->where('LOT_LoteId', '=', $consultaNocturna[0]->LOT_LoteId)
                    ->update(
                        array(
                            'LOT_CMM_EstatusLoteId' => ControlesMaestrosMultiples::$CMM_EstatusLote_Cerrado,
                            'LOT_Cerrado' => 1,
                            'LOT_FechaUltimaModificacion' => $hoy
                        )
                    );
                $bandera = 0;

            }
            else
            {

                $cuentaIdLote2 = count($consultaNocturna);

                if($cuentaIdLote2 > 0)
                {

                    //VERIFICAR SI EL ULTIMO LOTE ABIERTO PARA CERRARLO
                    $fecha_actual = strtotime($hoy2);
                    $fechaConsultaMasUno = strtotime ( '+1 day' , strtotime ( $consultaNocturna[0]->LOT_FechaCreacion ) ) ;
                    $nuevafecha = date ( 'Y-m-d 03:59:59' , $fechaConsultaMasUno );
                    $fecha_entrada = strtotime($nuevafecha);

                    if($fecha_actual > $fecha_entrada)
                    {

                        \DB::table('Lotes')->where('LOT_LoteId', '=', $consultaNocturna[0]->LOT_LoteId)
                            ->update(
                                array(
                                    'LOT_CMM_EstatusLoteId' => ControlesMaestrosMultiples::$CMM_EstatusLote_Cerrado,
                                    'LOT_Cerrado' => 1,
                                    'LOT_FechaUltimaModificacion' => $hoy
                                )
                            );
                        $bandera = 0;

                    }
                    else
                    {

                        $bandera = 1;

                    }
                    $idLoteEncontrado = $consultaNocturna[0]->LOT_LoteId;

                }

            }*/

        }

        return ['Bandera' => $bandera, 'idLoteEncontrado' => $idLoteEncontrado];

    }

    public function InsertarLotesPalletsInicio2($arreglo, $EmpleadoId, $lineaProduccionId){

        try {

            //date_default_timezone_set('America/Mexico_City');
            $hoy=date('d/m/Y H:i:s');
            $idEmpleado = DataBaseSession::getEmpleadoId();

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
                    'LOT_EMP_CreadoPorId' => $idEmpleado,
                    'LOT_LOTP_LotePreliminarId' => $arreglo[12]

                )

            );

            $idLote = EmpaqueController::BuscaLotePorCodigo($codigoLote);

            //verificar status del lote este como rebido, si es así cambiarlo a recibido-parcial
            if($idLote[0]->LOT_CMM_EstatusLoteId == '98344A16-D332-4282-BD71-ED4FCC468D2F')//recibido
            {

                \DB::table('Lotes')->where('LOT_LoteId', '=', $idLote[0]->LOT_LoteId)
                    ->update(
                        array(
                            'LOT_CMM_EstatusLoteId' => '8601CEC0-3271-4EC6-B857-AE1D352208D8',
                            'LOT_EMP_ModificadoPorId' => $idEmpleado,
                            'LOT_FechaUltimaModificacion' => $hoy
                        )
                    );

            }

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
            $hoy=date('d/m/Y H:i:s');

            $idEmpleado = DataBaseSession::getEmpleadoId();

            $dia = date('d');
            $mes = date('m');
            $ano = date('Y');
            $cortaAno = substr($ano, -2);
            //$parte2Lote = $dia.$mes.$cortaAno;
            $parte2Lote = $dia.$mes;

            $idLoteEnontrado = $_POST['idLoteEncontrado'];

            if($idLoteEnontrado == "" || $idLoteEnontrado == null)
            {

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
                            'LOT_EMP_CreadoPorId' => $idEmpleado,
                            'LOT_LOTP_LotePreliminarId' => $_POST['arreglo'][12]

                        )

                    );

                }

                
                //$idEmpleado = '32423501-373E-4958-A9FE-A34922AF7FFF';
                //$idEmpleado = 'B6C2B756-CA2B-445F-90B3-ADF440C97C90';

                $lineaProduccion = EmpaqueController::buscarLineaProduccionEmpleado($idEmpleado);

                $idLote = EmpaqueController::BuscaLotePorCodigo($codigoLote);

                $existe = EmpaqueController::verificaSiExistenMovimientosDeLote($idLote);

            }
            else
            {

                //$idEmpleado = DataBaseSession::getEmpleadoId();
                //$idEmpleado = '32423501-373E-4958-A9FE-A34922AF7FFF';
                //$idEmpleado = 'B6C2B756-CA2B-445F-90B3-ADF440C97C90';
                $lineaProduccion = EmpaqueController::buscarLineaProduccionEmpleado($idEmpleado);

                $idLote = EmpaqueController::BuscaLotePorId($idLoteEnontrado);
                $existe = EmpaqueController::verificaSiExistenMovimientosDeLote($idLote);

            }

            $count = count($existe);
            if($count <= 0)
            {

                //verificar status del lote este como rebido, si es así cambiarlo a recibido-parcial
                if($idLote[0]->LOT_CMM_EstatusLoteId == '98344A16-D332-4282-BD71-ED4FCC468D2F')//recibido
                {

                    \DB::table('Lotes')->where('LOT_LoteId', '=', $idLote[0]->LOT_LoteId)
                        ->update(
                            array(
                                'LOT_CMM_EstatusLoteId' => '8601CEC0-3271-4EC6-B857-AE1D352208D8',
                                'LOT_EMP_ModificadoPorId' => $idEmpleado,
                                'LOT_FechaUltimaModificacion' => $hoy
                            )
                        );

                }

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

                    //verificar status del lote este como rebido, si es así cambiarlo a recibido-parcial
                    if($idLote[0]->LOT_CMM_EstatusLoteId == '98344A16-D332-4282-BD71-ED4FCC468D2F')//recibido
                    {

                        \DB::table('Lotes')->where('LOT_LoteId', '=', $idLote[0]->LOT_LoteId)
                            ->update(
                                array(
                                    'LOT_CMM_EstatusLoteId' => '8601CEC0-3271-4EC6-B857-AE1D352208D8',
                                    'LOT_EMP_ModificadoPorId' => $idEmpleado,
                                    'LOT_FechaUltimaModificacion' => $hoy
                                )
                            );

                    }

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

            $codigoLote = Lotes::where('LOT_LoteId','=',$idLote[0]->LOT_LoteId)->first()->LOT_CodigoLote;

            $mensaje = 'Se registró el Pallet con éxito.';

            \DB::commit();

            return ['Status' => 'Valido', 'Mensaje' => $mensaje, 'json' => $lotesEmpleado, 'codigoLote' => $codigoLote];

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
                LEFT JOIN LotesCajas cajasActivas ON LPA_LotePalletId = cajasActivas.LCA_LPA_LotePalletId AND ISNULL (LCA_Eliminado, 0) = 0
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

            $idEmpleado = DataBaseSession::getEmpleadoId();
            //$idEmpleado = '32423501-373E-4958-A9FE-A34922AF7FFF';
            //$idEmpleado = 'B6C2B756-CA2B-445F-90B3-ADF440C97C90';

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
                ->where('LPA_Eliminado','=',0)
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
                            'LCA_EMP_ModificadoPorId' => $idEmpleado,
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
                        'LCA_EMP_CreadoPorId' => $idEmpleado,
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

            $idEmpleado = DataBaseSession::getEmpleadoId();
            //$idEmpleado = '32423501-373E-4958-A9FE-A34922AF7FFF';
            //$idEmpleado = 'B6C2B756-CA2B-445F-90B3-ADF440C97C90';

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

                if($idCaja[0]->LCA_Eliminado == 1)
                {

                    \DB::table('LotesCajas')->where('LCA_LoteCajaId', '=', $idCaja[0]->LCA_LoteCajaId)
                        ->update(
                            array(
                                'LCA_PesoCaja' => $pesoCaja,
                                'LCA_PiezasCaja' => $piezasCaja,
                                'LCA_Eliminado' => 0,
                                'LCA_EMP_ModificadoPorId' => $idEmpleado,
                                'LCA_FechaUltimaModificacion' => $hoy
                            )
                        );

                    $bandera = 1;

                }
                else
                {

                    $bandera = 0;

                }

            }
            else
            {

                \DB::table('LotesCajas')->insert(

                    array(

                        'LCA_LPA_LotePalletId' => $UltimoRegistrado,
                        'LCA_LOT_LoteId' => $idLote[0]->LOT_LoteId,
                        'LCA_NumeroCaja' => $numCaja,
                        'LCA_PesoCaja' => $pesoCaja,
                        'LCA_EMP_CreadoPorId' => $idEmpleado,
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

            //date_default_timezone_set('America/Mexico_City');
            $hoy=date('d/m/Y H:i:s');
            $idEmpleado = DataBaseSession::getEmpleadoId();

            ///////CODIGO NUEVO - CERRAR PRE-LOTE/////
            \DB::table('LotesPreliminares')->where('LOTP_LotePreliminarId', '=', $preLoteId)
                ->update(
                    array(
                        'LOTP_CMM_EstatusLotePreliminarId' => ControlesMaestrosMultiples::$CMM_EstatusLote_Cerrado,
                        'LOTP_FechaUltimaModificacion' => $hoy,
                        'LOTP_EMP_ModificadoPorId' => $idEmpleado
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
            $idEmpleado = DataBaseSession::getEmpleadoId();

            $idLote = EmpaqueController::BuscaLotePorCodigo($codigoLote);

            \DB::table('Lotes')->where('LOT_LoteId', '=', $idLote[0]->LOT_LoteId)
                ->update(
                    array(
                        'LOT_CMM_EstatusLoteId' => ControlesMaestrosMultiples::$CMM_EstatusLote_Cerrado,
                        'LOT_FechaUltimaModificacion' => $hoy,
                        'LOT_EMP_ModificadoPorId' => $idEmpleado,
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

            $idEmpleado = DataBaseSession::getEmpleadoId();
            //$idEmpleado = '32423501-373E-4958-A9FE-A34922AF7FFF';
            //$idEmpleado = 'B6C2B756-CA2B-445F-90B3-ADF440C97C90';

            $lineaProduccion = EmpaqueController::buscarLineaProduccionEmpleado($idEmpleado);

            $idLote = EmpaqueController::BuscaLotePorCodigo($codigoLote);

            \DB::table('LotesPallets')->where('LPA_LOT_LoteId', '=', $idLote[0]->LOT_LoteId)
                ->where('LPA_LIP_LineaProduccionId','=',$lineaProduccion[0]->EMP_LIP_LineaProduccionId)
                ->where('LPA_CMM_EstatusId','=',ControlesMaestrosMultiples::$CMM_INV_EstatusPallet_Abierto)
                ->update(
                    array(
                        'LPA_CMM_EstatusId' => ControlesMaestrosMultiples::$CMM_INV_EstatusPallet_Cerrado,
                        'LPA_EMP_ModificadoPorId' => $idEmpleado,
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

        $idEmpleado = DataBaseSession::getEmpleadoId();
        //$idEmpleado = '32423501-373E-4958-A9FE-A34922AF7FFF';
        //$idEmpleado = 'B6C2B756-CA2B-445F-90B3-ADF440C97C90';

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

        $idEmpleado = DataBaseSession::getEmpleadoId();
        //$idEmpleado = '32423501-373E-4958-A9FE-A34922AF7FFF';
        //$idEmpleado = 'B6C2B756-CA2B-445F-90B3-ADF440C97C90';

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

    public static function BuscaLotePorId($idLote){

        $sub = \DB::select(

            \DB::raw(

                "SELECT LOT_LoteId,LOT_CantidadIntervencionSupervisor,LOT_LOTP_LotePreliminarId,LOT_CMM_EstatusLoteId FROM Lotes WHERE LOT_LoteId = '".$idLote."'"

            )

        );

        return $sub;

    }

    public static function BuscaLotePorCodigo($codigoLote){

        $sub = \DB::select(

            \DB::raw(

                "SELECT LOT_LoteId,LOT_CantidadIntervencionSupervisor,LOT_LOTP_LotePreliminarId,LOT_FechaCreacion,LOT_CMM_EstatusLoteId FROM Lotes WHERE LOT_CodigoLote = '".$codigoLote."'"

            )

        );

        return $sub;

    }

    public static function EliminarPallet($codigoLote,$noPallet){

        \DB::beginTransaction();

        try {

            //date_default_timezone_set('America/Mexico_City');
            $hoy=date('d/m/Y H:i:s');

            $idEmpleado = DataBaseSession::getEmpleadoId();
            //$idEmpleado = '32423501-373E-4958-A9FE-A34922AF7FFF';
            //$idEmpleado = 'B6C2B756-CA2B-445F-90B3-ADF440C97C90';

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
                            'LPA_EMP_ModificadoPorId' => $idEmpleado,
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

            $idEmpleado = DataBaseSession::getEmpleadoId();
            //$idEmpleado = '32423501-373E-4958-A9FE-A34922AF7FFF';
            //$idEmpleado = 'B6C2B756-CA2B-445F-90B3-ADF440C97C90';

            $lineaProduccion = EmpaqueController::buscarLineaProduccionEmpleado($idEmpleado);

            $idLote = EmpaqueController::BuscaLotePorCodigo($codigoLote);

            $palletId = EmpaqueController::BuscaPalletId($idLote[0]->LOT_LoteId,$lineaProduccion[0]->EMP_LIP_LineaProduccionId);

            \DB::table('LotesCajas')->where('LCA_LOT_LoteId', '=', $idLote[0]->LOT_LoteId)
                ->where('LCA_LPA_LotePalletId','=',$palletId[0]->LPA_LotePalletId)
                ->where('LCA_NumeroCaja','=',$noCaja)
                ->update(
                    array(
                        'LCA_Eliminado' => 1,
                        'LCA_EMP_ModificadoPorId' => $idEmpleado,
                        'LCA_FechaUltimaModificacion' => $hoy
                    )
                );

            \DB::table('LotesPallets')->where('LPA_LotePalletId', '=', $palletId[0]->LPA_LotePalletId)
                ->update(
                    array(
                        'LPA_CMM_EstatusId' => ControlesMaestrosMultiples::$CMM_INV_EstatusPallet_Abierto,
                        'LPA_EMP_ModificadoPorId' => $idEmpleado,
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

                "SELECT USU_EMP_EmpleadoId FROM Usuarios
                INNER JOIN Empleados ON USU_EMP_EmpleadoId = EMP_EmpleadoId
                INNER JOIN Puestos ON EMP_PUE_PuestoId = PUE_PuestoId
                INNER JOIN ControlesMaestrosMultiples ON PUE_CMM_TabuladoresPuestosId = CMM_ControlId
                WHERE USU_Nombre = '".$usuario."'
                AND USU_Contrasenia = '".$pass."'
                AND USU_Activo = 1
                AND CMM_ControlId = '91472668-0AA4-48A6-B568-5D4A9221DD8C'"

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
                WHERE LOT_LOTP_LotePreliminarId = '".$PreLoteId."' ORDER BY LOT_FechaCreacion DESC"

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

                "SELECT LOT_CodigoLote, CMM_Valor
                FROM Lotes
                INNER JOIN ControlesMaestrosMultiples ON CMM_ControlId = LOT_CMM_EstatusLoteId
                WHERE LOT_LOTP_LotePreliminarId = '".$PreLote_formateada."' ORDER BY LOT_FechaCreacion DESC"

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

                "SELECT * FROM LotesPallets WHERE LPA_LOT_LoteId = '".$LoteId[0]->LOT_LoteId."' AND LPA_Eliminado = 0 ORDER BY CAST(LPA_NumeroPallet AS Int) ASC"

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

                "SELECT * FROM LotesCajas WHERE LCA_LPA_LotePalletId = '".$PalletId[0]->LPA_LotePalletId."' AND LCA_Eliminado = 0 ORDER BY CAST(LCA_NumeroCaja AS Int) ASC"

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
                           WHERE LCA_Eliminado = 0
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

    /*comente jorge m public function consultaAutonumerico(){

        //date_default_timezone_set('America/Mexico_City');
        $hoy = date('d/m/Y H:i:s');

        //////////SACAR AUTONUMERICO///////////////////////////
        $consulta = \DB::select(\DB::raw("SELECT * FROM Autonumericos WHERE AUT_Nombre = 'CM_EMP_SiguienteBulto'"));
        $siguienteBulto = $consulta[0]->AUT_Siguiente;
        $siguienteBulto = intval($siguienteBulto) + 1;

        \DB::table('Autonumericos')->where('AUT_Nombre', '=', 'CM_EMP_SiguienteBulto')
            ->update(array('AUT_Siguiente' => $siguienteBulto, 'AUT_FechaUltimaModificacion' => $hoy));

        return $siguienteBulto;

    }*/

    public function registraBulto(){

        \DB::beginTransaction();

        try {

            //date_default_timezone_set('America/Mexico_City');
            $hoy = date('d/m/Y H:i:s');

            $empleadoId = DataBaseSession::getEmpleadoId();
            $datosTabla = $_POST['datosTabla'];
            $tipoEtiqueta = $_POST['tipoEtiqueta'] == '' ? null : $_POST['tipoEtiqueta'];
            $equis = $_POST['x'] == '' ? null : $_POST['x'];
            $ye = $_POST['y'] == '' ? null : $_POST['y'];
            $zeta = $_POST['z'] == '' ? null : $_POST['z'];
            $dobleu = $_POST['w'] == '' ? null : $_POST['w'];
            $tipoBulto = $_POST['tipoBulto'] == '' ? null : $_POST['tipoBulto'];
            $caracteristicasBulto = $_POST['caracteristicasBulto'] == '' ? null : $_POST['caracteristicasBulto'];
            $complemento = $_POST['complemento'] == '' ? null : $_POST['complemento'];
            $contenido = $_POST['contenido'] == '' ? null : $_POST['contenido'];
            $idProyecto = $_POST['idProyecto'] == '' ? null : $_POST['idProyecto'];
            $idBultoRelacion = $_POST['idBulto'] == '' ? null : $_POST['idBulto'];
            $idOtRelacion = $_POST['idOtRelacion'] == '' ? null : $_POST['idOtRelacion'];

            if($tipoBulto == "CDBBF4F2-3A62-475B-A0AB-B235496DFE7D"){//Principal
            //if($tipoEtiqueta == "artOT"){

                $idBulto = self::getNuevoId();
                //$numeroBulto = self::consultaCodigoBulto();
                //AGREGUE NUEVO AUTONUMERICO PARA ETIQUETAS
                $autonumerico_dao = new AutonumericoController();
                $autonumerico_id = self::establecerAutonumerico('CM_PRO_SiguienteBulto', null);
                $numeroBulto = $autonumerico_dao->getSiguienteAutonumericoPorId($autonumerico_id);

                $bulto = new Bultos();
                $bulto->BUL_BultoId = $idBulto;
                $bulto->BUL_NumeroBulto = $numeroBulto;
                $bulto->BUL_CMM_TipoEtiquetaId = 'A56F8B7A-D782-42D9-A37B-5FAD76921CA1';//Etiqueta Articulo de OT
                $bulto->BUL_CMM_TipoBultoId = $tipoBulto;
                $bulto->BUL_X = $equis;
                $bulto->BUL_Y = $ye;
                $bulto->BUL_Z = $zeta;
                $bulto->BUL_W = $dobleu;
                $bulto->BUL_Complemento = $complemento;
                $bulto->BUL_Contenido = $contenido;
                $bulto->BUL_EMP_CreadoPorId = $empleadoId;
                //$bulto->BUL_PRY_ProyectoId = $datosTabla[0]['idProyecto']  == '' ? null : $datosTabla[0]['idProyecto'];
                $bulto->BUL_PRY_ProyectoId = $idProyecto  == '' ? null : $idProyecto;
                $bulto->BUL_CMM_EstatusBultoId = 'A1DDDA80-4B1C-4F72-AA48-BF69C66B64BA';//Abierto
                $bulto->save();

                $cuentaTabla = count($datosTabla);
                for($x = 0; $x < $cuentaTabla; $x ++){

                    $bultoDetalle = new BultosDetalle();
                    $bultoDetalle->BULD_BUL_BultoId = $idBulto;
                    $bultoDetalle->BULD_OT_OrdenTrabajoId = $datosTabla[$x]['idOtArt']  == '' ? null : $datosTabla[$x]['idOtArt'];
                    $ot = OrdenesTrabajo::find($bultoDetalle->BULD_OT_OrdenTrabajoId);
                    $otd = \DB::select(\DB::raw("SELECT * FROM OrdenesTrabajoDetalleArticulos  INNER JOIN Articulos ON ART_ArticuloId = OTDA_ART_ArticuloId WHERE OTDA_OT_OrdenTrabajoId = '".$ot->OT_OrdenTrabajoId."'"));
                    $bultoDetalle->BULD_ART_ArticuloId = $otd[0]->OTDA_ART_ArticuloId  == '' ? null : $otd[0]->OTDA_ART_ArticuloId;
                    $bultoDetalle->BULD_Cantidad = $datosTabla[$x]['cantidad']  == '' ? null : $datosTabla[$x]['cantidad'];
                    $bultoDetalle->BULD_EMP_CreadoPorId = $empleadoId;
                    $bultoDetalle->save();

                    //CREAR LOTE POR BULTO DETALLE
                    $lote = new Lotes();
                    $lote->LOT_LoteId = self::getNuevoId();
                    $lote->LOT_NumeroLote = $ot->OT_Codigo;
                    $lote->LOT_ART_ArticuloId = $bultoDetalle->BULD_ART_ArticuloId;
                    $lote->LOT_CodigoLote = $ot->OT_Codigo.$bulto->BUL_NumeroBulto.$otd[0]->ART_CodigoArticulo;
                    $lote->LOT_FechaLote = $hoy;
                    $lote->LOT_CMM_EstatusLoteId = ControlesMaestrosMultiples::$CMM_EstatusLote_Empacado;
                    $lote->LOT_OT_OrdenTrabajoId = $bultoDetalle->BULD_OT_OrdenTrabajoId;
                    $lote->LOT_CMM_TipoRegistroId = 'B1E3A785-C34D-47B8-9EAC-337D84A15CFE';//Lote Empaque
                    $lote->save();

                }

                for($x = 0; $x < count($caracteristicasBulto); $x ++){
                    if($caracteristicasBulto[$x] != ""){
                        $bultoDetalleCaracteristica = new BultosDetalleCaracteristicas();
                        $bultoDetalleCaracteristica->BULDC_BUL_BultoId = $idBulto;
                        $bultoDetalleCaracteristica->BULDC_CMM_BultoCaracteristicaId = $caracteristicasBulto[$x];
                        $bultoDetalleCaracteristica->BULDC_EMP_CreadoPorId = $empleadoId;
                        $bultoDetalleCaracteristica->save();
                    }
                }

                //sacar consulta para etiqueta
                /*$consultaBulto = \DB::select(\DB::raw("
                                    SELECT TOP 1
                                        BUL_NumeroBulto
                                        ,BUL_X
                                        ,BUL_Y
                                        ,BUL_Z
                                        ,BUL_W
                                        ,BUL_Complemento
                                        ,BUL_Contenido
                                        ,BUL_CMM_TipoEtiquetaId
                                        ,PRY_NombreProyecto
                                        ,dbo.getOTsPorBultoId('".$idBulto."') AS BUL_OTS
                                        ,dbo.getArticulosPorBultoId('".$idBulto."') AS BUL_ARTS
                                        ,PRY_ProyectoId
                                        ,TEMP.OV_OrdenVentaId
                                        ,TEMP.OV_CodigoOV
                                        ,TEMP.OV_ReferenciaOC
                                        ,CLI_CodigoCliente
                                        ,CLI_NombreComercial
                                        ,CLI_RazonSocial
                                        ,CCON_Nombre
                                    FROM Bultos
                                    LEFT JOIN Proyectos ON PRY_ProyectoId = BUL_PRY_ProyectoId
                                    LEFT JOIN(
                                        SELECT TOP 1 OrdenesVenta.*
                                        FROM OrdenesVenta
                                        LEFT JOIN Proyectos ON PRY_ProyectoId = OV_PRO_ProyectoId
                                        LEFT JOIN Bultos ON PRY_ProyectoId = BUL_PRY_ProyectoId
                                        WHERE BUL_BultoId = '".$idBulto."'
                                    ) AS TEMP ON TEMP.OV_PRO_ProyectoId = PRY_ProyectoId
                                    LEFT JOIN Clientes ON CLI_ClienteId = TEMP.OV_CLI_ClienteId
                                    LEFT JOIN ClientesContactos ON CCON_CLI_ClienteId = CLI_ClienteId
                                    WHERE BUL_BultoId = '".$idBulto."'
                                "));*/

            }
            elseif($tipoBulto == "A00E0707-1CC9-4F59-8BA6-CD1DC4D82DD4"){//Complemento

                /*$otd = \DB::select(\DB::raw("SELECT
                            PRY_ProyectoId,OTDA_ART_ArticuloId
                        FROM OrdenesTrabajo
                        INNER JOIN OrdenesTrabajoDetalleArticulos ON OTDA_OT_OrdenTrabajoId = OT_OrdenTrabajoId
                        INNER JOIN OrdenesTrabajoReferencia ON OTRE_OT_OrdenTrabajoId = OT_OrdenTrabajoId
                        INNER JOIN OrdenesVenta ON OV_OrdenVentaId = OTRE_OV_OrdenVentaId
                        INNER JOIN Proyectos ON PRY_ProyectoId = OV_PRO_ProyectoId
                        WHERE OT_OrdenTrabajoId = '".$idOtRelacion."'"));*/

                //consultar estatus del bulto padre
                $consultaBultoPapa = \DB::select(\DB::raw("SELECT
                            BUL_CMM_EstatusBultoId
                            ,BULD_PreEmbarcado
                        FROM Bultos
                        INNER JOIN BultosDetalle ON BULD_BUL_BultoId = BUL_BultoId
                        WHERE BUL_BultoId = '".$idBultoRelacion."'"));
                            
                if($consultaBultoPapa[0]->BUL_CMM_EstatusBultoId != 'A1DDDA80-4B1C-4F72-AA48-BF69C66B64BA'){//Abierto
                    throw new \Exception("No se puede guardar el bulto, porque el bulto principal ya esta preembarcado.", 124);
                }
                if ($consultaBultoPapa[0]->BULD_PreEmbarcado == 1){//PreEmbarcado
                    throw new \Exception("No se puede guardar el bulto, porque el bulto principal ya esta preembarcado.", 124);
                }              

                $idBulto = self::getNuevoId();
                //$numeroBulto = self::consultaCodigoBulto();
                $autonumerico_dao = new AutonumericoController();
                $autonumerico_id = self::establecerAutonumerico('CM_PRO_SiguienteBulto', null);
                $numeroBulto = $autonumerico_dao->getSiguienteAutonumericoPorId($autonumerico_id);

                $bulto = new Bultos();
                $bulto->BUL_BultoId = $idBulto;
                $bulto->BUL_NumeroBulto = $numeroBulto;
                $bulto->BUL_CMM_TipoEtiquetaId = '214352CC-23AD-4668-8F75-E4F372DD2AE9';//Etiqueta Articulo Exist
                $bulto->BUL_CMM_TipoBultoId = $tipoBulto;
                $bulto->BUL_X = $equis;
                $bulto->BUL_Y = $ye;
                $bulto->BUL_Z = $zeta;
                $bulto->BUL_W = $dobleu;
                $bulto->BUL_Complemento = $complemento;
                $bulto->BUL_Contenido = $contenido;
                $bulto->BUL_EMP_CreadoPorId = $empleadoId;
                //$bulto->BUL_PRY_ProyectoId = $otd[0]->PRY_ProyectoId == '' ? null : $otd[0]->PRY_ProyectoId;
                $bulto->BUL_PRY_ProyectoId = $idProyecto  == '' ? null : $idProyecto;
                $bulto->BUL_BultoPadreId = $idBultoRelacion;
                $bulto->BUL_CMM_EstatusBultoId = 'A1DDDA80-4B1C-4F72-AA48-BF69C66B64BA';//Abierto
                $bulto->save();

                /*$bultoDetalle = new BultosDetalle();
                $bultoDetalle->BULD_BUL_BultoId = $idBulto;
                $bultoDetalle->BULD_OT_OrdenTrabajoId = $idOtRelacion;
                //$ot = OrdenesTrabajo::find($bultoDetalle->BULD_OT_OrdenTrabajoId);
                //$otd= \DB::select(\DB::raw("SELECT * FROM OrdenesTrabajoDetalleArticulos WHERE OTDA_OT_OrdenTrabajoId = '".$ot->OT_OrdenTrabajoId."'"));
                $bultoDetalle->BULD_ART_ArticuloId = $otd[0]->OTDA_ART_ArticuloId  == '' ? null : $otd[0]->OTDA_ART_ArticuloId;
                //$bultoDetalle->BULD_Cantidad = $datosTabla[$x]['cantidad']  == '' ? null : $datosTabla[$x]['cantidad'];
                $bultoDetalle->BULD_EMP_CreadoPorId = $empleadoId;
                $bultoDetalle->save();*/

                for($x = 0; $x < count($caracteristicasBulto); $x ++){
                    if($caracteristicasBulto[$x] != ""){
                        $bultoDetalleCaracteristica = new BultosDetalleCaracteristicas();
                        $bultoDetalleCaracteristica->BULDC_BUL_BultoId = $idBulto;
                        $bultoDetalleCaracteristica->BULDC_CMM_BultoCaracteristicaId = $caracteristicasBulto[$x];
                        $bultoDetalleCaracteristica->BULDC_EMP_CreadoPorId = $empleadoId;
                        $bultoDetalleCaracteristica->save();
                    }
                }

                //sacar consulta para etiqueta
                /*$consultaBulto = \DB::select(\DB::raw("
                                    SELECT
                                        PADRE.BUL_NumeroBulto AS BUL_NumeroBulto
                                        ,HIJO.BUL_Complemento AS BUL_Complemento
                                        ,HIJO.BUL_Contenido AS BUL_Contenido
                                        ,HIJO.BUL_CMM_TipoEtiquetaId AS BUL_CMM_TipoEtiquetaId
                                    FROM Bultos HIJO
                                    LEFT JOIN Bultos PADRE ON PADRE.BUL_BultoId = HIJO.BUL_BultoPadreId
                                    WHERE HIJO.BUL_BultoId = '".$idBulto."'
                                "));*/
            }

            $mensaje = 'Se registró éxito.';

            \DB::commit();

            return ['Status' => 'Valido', 'Mensaje' => $mensaje, 'idBulto' => $idBulto]; //,'consultaBulto' => $consultaBulto];

        }
        catch (\Exception $e) {

            \DB::rollback();

            return ['Status' => 'Error', 'Mensaje' => 'Ocurrió un error al realizar el proceso. <br><br><br>Error: <br>'.$e->getMessage()];

        }

    }

    /*comente public function consultaCodigoBulto(){

        //////////SACAR AUTONUMERICO///////////////////////////
        $autonumerico_dao = new AutonumericoController();
        $clienteId = null;
        $empleadoId = null;
        $codigo_OC = null;

        if($autonumerico_dao->isAutonumericoActivoPorReferenciaId('CM_PRO_SiguienteBulto', null)){
            $autonumerico_id = self::establecerAutonumerico($clienteId, $empleadoId);
            $codigo_OC = $autonumerico_dao->getSiguienteAutonumericoPorId($autonumerico_id);
        }

        return $codigo_OC;

    }

    public function establecerAutonumerico($clienteId, $empleadoId)
    {
        try {establecerAutonumerico
            $autonumerico_dao = new AutonumericoController();
            $autonumericoFicha = $autonumerico_dao->getAutonumerico($clienteId, "CM_PRO_SiguienteBulto", $empleadoId);
            return $autonumericoFicha->AUT_AutonumericoId;
        } catch (Exception $ex) {
            throw $ex;
        }
    }*/

    public function registraLoteEmpaque(){

        \DB::beginTransaction();

        try {

            //date_default_timezone_set('America/Mexico_City');
            $hoy = date('d/m/Y H:i:s');
            $dia = date('d');
            $mes = date('m');
            $empleadoId = DataBaseSession::getEmpleadoId();

            $datosTabla = $_POST['datosTabla'];

            $otCodigo = trim($datosTabla[0]['codigoOt']);
            $artCodigo = trim($datosTabla[0]['codigoArticulo']);
            $cantidad = $datosTabla[0]['cantidad'];

            $numeroLote = substr($otCodigo, -3);
            $codigoLote = $otCodigo.$artCodigo.$dia.$mes;

            //CONSULTA OT
            $consultaOT = \DB::select(\DB::raw("SELECT * FROM OrdenesTrabajo WHERE OT_Codigo = '".$otCodigo."'"));

            //CONSULTA ART
            $consultaART = \DB::select(\DB::raw("SELECT * FROM Articulos
                                INNER JOIN ControlesMaestrosUM ON CMUM_UnidadMedidaId = ART_CMUM_UMInventarioId
                                WHERE ART_CodigoArticulo = '".$artCodigo."'"));

            //INSERTA LOTE
            $lote = new Lotes();
            $lote->LOT_LoteId = self::getNuevoId();
            $lote->LOT_NumeroLote = $numeroLote;
            $lote->LOT_ART_ArticuloId = $consultaART[0]->ART_ArticuloId;
            $lote->LOT_CodigoLote = $codigoLote;
            //$lote->LOT_FechaCaducidad = $numeroLote;
            $lote->LOT_FechaLote = $hoy;
            $lote->LOT_CMM_EstatusLoteId = ControlesMaestrosMultiples::$CMM_EstatusLote_Empacado;
            //$lote->LOT_EMP_CreadoPorId = $empleadoId;
            /*$lote->LOT_CostoUnitario = $numeroLote;
            $lote->LOT_ValorIndirectoMaterial = $numeroLote;
            $lote->LOT_CostoManoObra = $numeroLote;
            $lote->LOT_CostoIndirectosFijos = $numeroLote;
            $lote->LOT_CostoIndirectosVariables = $numeroLote;*/
            $lote->LOT_OT_OrdenTrabajoId = $consultaOT[0]->OT_OrdenTrabajoId;
            $lote->LOT_CMM_TipoRegistroId = 'B1E3A785-C34D-47B8-9EAC-337D84A15CFE';//Lote Empaque
            $lote->save();

            //INSERTA LOTES PALLETS
            $lotePallet = new LotesPallets();
            $lotePallet->LPA_LotePalletId = self::getNuevoId();
            $lotePallet->LPA_LOT_LoteId = $lote->LOT_LoteId;
            $lotePallet->LPA_NumeroPallet = 1;
            $lotePallet->LPA_CMM_EstatusId = ControlesMaestrosMultiples::$CMM_INV_EstatusPallet_Abierto;
            $lotePallet->LPA_EMP_CreadoPorId = $empleadoId;
            $lotePallet->LPA_LIP_LineaProduccionId = '534DD035-227B-4781-8099-6CAFC6C2E245';//MANO DE OBRA por default
            $lotePallet->save();

            //INSERTA LOTES CAJAS
            $loteCajas = new LotesCajas();
            $loteCajas->LCA_LoteCajaId = self::getNuevoId();
            $loteCajas->LCA_LPA_LotePalletId = $lotePallet->LPA_LotePalletId;
            $loteCajas->LCA_LOT_LoteId = $lote->LOT_LoteId;
            $loteCajas->LCA_NumeroCaja = 1;
            $loteCajas->LCA_PesoCaja = 0;
            $loteCajas->LCA_EMP_CreadoPorId = $empleadoId;
            $loteCajas->LCA_PiezasCaja = floatval($cantidad);
            $loteCajas->save();

            //SACAR ALMACENES DEL USUARIO LOGIADO
            $almacen = DataBaseSession::getAlmacenes();

            $cuentaAlmacen = count($almacen);
            if ($cuentaAlmacen > 0) {

                //SACAR LOCALIDAD POR ALMACEN
                $consultaLocalidad = \DB::select(\DB::raw("SELECT * FROM Localidades
                                        WHERE LOC_ALM_AlmacenId = '" . $almacen[0] . "'
                                        AND LOC_LocalidadGeneral = 0"));

                //GUARDA TRASPASO MOVTO
                $TraspasosMovtos = new TraspasoMovto();
                $TraspasosMovtos->TRAM_ART_ArticuloId = $consultaART[0]->ART_ArticuloId;
                $TraspasosMovtos->TRAM_CantidadATraspasar = $cantidad;
                $TraspasosMovtos->TRAM_Razon = "Empaque Recorte: ".$consultaART[0]->ART_CodigoArticulo;
                $TraspasosMovtos->TRAM_Referencia = "Empaque Recorte: ".$consultaART[0]->ART_CodigoArticulo." Cantidad: ".$cantidad;
                $TraspasosMovtos->TRAM_UnidadMedidadArt = $consultaART[0]->CMUM_Nombre;
                $TraspasosMovtos->TRAM_EstatusContable = false;
                $TraspasosMovtos->TRAM_CantidadAMano = $consultaART[0]->ART_CantidadAMano;
                $TraspasosMovtos->TRAM_CantidadAManoConTraspaso = $consultaART[0]->ART_CantidadAMano + $cantidad;
                //$TraspasosMovtos->TRAM_CMM_TipoTransferenciaId = '958E6430-F874-48A0-A26E-1502F1BC39CB';//EMPAQUE RECORTE
                $TraspasosMovtos->TRAM_CMM_TipoTransferenciaId = '741B6B0A-363E-4085-8B45-5B3A4FB3E59D';//Cantidad Trabajada OT
                $TraspasosMovtos->TRAM_ReferenciaMovtoId = $consultaOT[0]->OT_OrdenTrabajoId;
                $TraspasosMovtos->TRAM_DefinidoPorUsuario1 = $loteCajas->LCA_LoteCajaId;
                //$TraspasosMovtos->save();

                //LLENA OBJETO PARA ENVIAR A PROCESADOR
                $arrayDetallesMovimiento = array();
                $dmi = new DetallesMovimientoInventario();

                $dmi->setCantidadTransferir($TraspasosMovtos->TRAM_CantidadATraspasar);
                $dmi->setIdAlmacen($almacen[0]);

                $localidad = new Localidades();
                $localidad->COL_LOCALIDAD_ID = $consultaLocalidad[0]->LOC_LocalidadId;
                $dmi->setLocalidad($localidad);

                $lotes = new Lotes();
                $lotes->COL_LOTE_ID = $lote->LOT_LoteId;
                $dmi->setLote($lotes);

                array_push($arrayDetallesMovimiento, $dmi);

                //ENVIAR INFORMACION A PROCESADOR
                ProcesadorMovimientoInventarios::registraMovimientoEnInventario($TraspasosMovtos, $arrayDetallesMovimiento, null);

                $mensaje = 'Se registró éxito.';

                \DB::commit();

                return ['Status' => 'Valido', 'Mensaje' => $mensaje];

            } else {

                \DB::rollback();

                return ['Status' => 'Error', 'Mensaje' => 'El usuario no tiene almacen registrado. Es necesario ponerle un almacen.'];

            }

        }
        catch (\Exception $e) {

            \DB::rollback();

            return ['Status' => 'Error', 'Mensaje' => 'No se registró la Caja. Ocurrió un error al realizar el proceso. <br><br><br>Error: <br>'.$e->getMessage()];

        }

    }

    public static function getNuevoId()
    {
        if (function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

    public function exportar(){

        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $dao = new DAOGeneralController();
        $nombre_empresa = $dao->getEjecutaConsulta("
                                  SELECT CMA_Valor
                                  FROM ControlesMaestros
                                  WHERE CMA_Control = 'CMA_CSVP_EmpresaRazonSocial'")[0]->CMA_Valor;

        $idBulto = $_POST ['idBulto'];
        $bulto = Bultos::find($idBulto);

        if($bulto->BUL_CMM_TipoBultoId == "A00E0707-1CC9-4F59-8BA6-CD1DC4D82DD4"){//Complemento

            //sacar consulta para etiqueta
            $consulta = "
             SELECT --TOP 1
                PPAL.BUL_NumeroBulto
                ,BUL_HIJO.BUL_NumeroBulto AS PADRE
                ,PPAL.BUL_X
                ,PPAL.BUL_Y
                ,PPAL.BUL_Z
                ,PPAL.BUL_W
                ,PPAL.BUL_Complemento
                ,PPAL.BUL_Contenido
                ,PPAL.BUL_CMM_TipoEtiquetaId
                ,PRY_CodigoEvento+ ' - ' + PRY_NombreProyecto AS PROYECTO
                ,dbo.getOTsPorBultoComplementoId('".$idBulto."') AS BUL_OTS
                ,dbo.getArticulosPorBultoComplementoId('".$idBulto."') AS BUL_ARTS
                ,PRY_ProyectoId
                ,OV_OrdenVentaId
                ,OV_CodigoOV
                ,OV_ReferenciaOC
                ,CLI_CodigoCliente
                ,CLI_NombreComercial
                ,CLI_RazonSocial
                ,CCON_Nombre
                ,CantidadTotal
                ,dbo.getGLNPorBultoComplementoId('".$idBulto."') AS ART_GLN
            FROM Bultos PPAL
            LEFT JOIN(
                SELECT
                    BULD_BUL_BultoId
                    ,ISNULL(SUM(BULD_Cantidad),0) AS CantidadTotal
                FROM BultosDetalle
                WHERE BULD_BUL_BultoId = '".$idBulto."'
                GROUP BY
                    BULD_BUL_BultoId
            ) AS BULD ON BULD.BULD_BUL_BultoId = PPAL.BUL_BultoId
            INNER JOIN Bultos BUL_HIJO ON BUL_HIJO.BUL_BultoId = PPAL.BUL_BultoPadreId
            INNER JOIN BultosDetalle BULD2 ON BULD2.BULD_BUL_BultoId = BUL_HIJO.BUL_BultoId
            --INNER JOIN Articulos ON ART_ArticuloId = BULD2.BULD_ART_ArticuloId
            --INNER JOIN OrdenesTrabajoDetalleArticulos ON OTDA_ART_ArticuloId = ART_ArticuloId
			INNER JOIN OrdenesTrabajoDetalleArticulos ON OTDA_ART_ArticuloId = BULD2.BULD_ART_ArticuloId
            --INNER JOIN OrdenesTrabajo ON OT_OrdenTrabajoId = OTDA_OT_OrdenTrabajoId
            --INNER JOIN OrdenesTrabajoReferencia ON OTRE_OT_OrdenTrabajoId = OT_OrdenTrabajoId
			INNER JOIN OrdenesTrabajoReferencia ON OTRE_OT_OrdenTrabajoId = BULD2.BULD_OT_OrdenTrabajoId
            INNER JOIN OrdenesVenta ON OV_OrdenVentaId = OTRE_OV_OrdenVentaId
            INNER JOIN Proyectos ON PRY_ProyectoId = OV_PRO_ProyectoId
            INNER JOIN Clientes ON CLI_ClienteId = OV_CLI_ClienteId
            INNER JOIN ClientesContactos ON CCON_ContactoId = OV_CCON_ContactoId
            WHERE PPAL.BUL_BultoId = '".$idBulto."'
            GROUP BY
				PPAL.BUL_NumeroBulto
                ,BUL_HIJO.BUL_NumeroBulto
                ,PPAL.BUL_X
                ,PPAL.BUL_Y
                ,PPAL.BUL_Z
                ,PPAL.BUL_W
                ,PPAL.BUL_Complemento
                ,PPAL.BUL_Contenido
                ,PPAL.BUL_CMM_TipoEtiquetaId
                ,PRY_CodigoEvento+ ' - ' + PRY_NombreProyecto
                ,PRY_ProyectoId
                ,OV_OrdenVentaId
                ,OV_CodigoOV
                ,OV_ReferenciaOC
                ,CLI_CodigoCliente
                ,CLI_NombreComercial
                ,CLI_RazonSocial
                ,CCON_Nombre
                ,CantidadTotal
            ORDER BY OV_CodigoOV
                ,OV_ReferenciaOC";

            $reportSource = public_path() . "/Reportes/Inventario/Empaque/rptEtiqueta_4.jrxml";

        }
        else{

            //sacar consulta para etiqueta
            $consulta = "
             SELECT --TOP 1
                BUL_NumeroBulto
                ,BUL_X
                ,BUL_Y
                ,BUL_Z
                ,BUL_W
                ,BUL_Complemento
                ,BUL_Contenido
                ,BUL_CMM_TipoEtiquetaId
                ,PRY_CodigoEvento+ ' - ' + PRY_NombreProyecto AS PROYECTO
                ,dbo.getOTsPorBultoId('".$idBulto."') AS BUL_OTS
                ,dbo.getArticulosPorBultoId('".$idBulto."') AS BUL_ARTS
                ,PRY_ProyectoId
                ,OV_OrdenVentaId
                ,OV_CodigoOV
                ,OV_ReferenciaOC
                ,CLI_CodigoCliente
                ,CLI_NombreComercial
                ,CLI_RazonSocial
                ,CCON_Nombre
                ,CantidadTotal
                ,dbo.getGLNPorBultoId('".$idBulto."') AS ART_GLN
            FROM Bultos
            INNER JOIN(
                SELECT
                    BULD_BUL_BultoId
                    ,ISNULL(SUM(BULD_Cantidad),0) AS CantidadTotal
                FROM BultosDetalle
                WHERE BULD_BUL_BultoId = '".$idBulto."'
                GROUP BY
                    BULD_BUL_BultoId
            ) AS BULD ON BULD.BULD_BUL_BultoId = BUL_BultoId
            INNER JOIN BultosDetalle BULD2 ON BULD2.BULD_BUL_BultoId = BUL_BultoId
            --INNER JOIN Articulos ON ART_ArticuloId = BULD_ART_ArticuloId
            --INNER JOIN OrdenesTrabajoDetalleArticulos ON OTDA_ART_ArticuloId = ART_ArticuloId
			INNER JOIN OrdenesTrabajoDetalleArticulos ON OTDA_ART_ArticuloId = BULD_ART_ArticuloId
            --INNER JOIN OrdenesTrabajo ON OT_OrdenTrabajoId = OTDA_OT_OrdenTrabajoId
            --INNER JOIN OrdenesTrabajoReferencia ON OTRE_OT_OrdenTrabajoId = OT_OrdenTrabajoId
			INNER JOIN OrdenesTrabajoReferencia ON OTRE_OT_OrdenTrabajoId = BULD_OT_OrdenTrabajoId
            INNER JOIN OrdenesVenta ON OV_OrdenVentaId = OTRE_OV_OrdenVentaId
            INNER JOIN Proyectos ON PRY_ProyectoId = OV_PRO_ProyectoId
            LEFT JOIN Clientes ON CLI_ClienteId = OV_CLI_ClienteId
            LEFT JOIN ClientesContactos ON CCON_ContactoId = OV_CCON_ContactoId
            WHERE BUL_BultoId = '".$idBulto."'
            GROUP BY
				BUL_NumeroBulto
                ,BUL_X
                ,BUL_Y
                ,BUL_Z
                ,BUL_W
                ,BUL_Complemento
                ,BUL_Contenido
                ,BUL_CMM_TipoEtiquetaId
                ,PRY_CodigoEvento+ ' - ' + PRY_NombreProyecto
                --,BUL_OTS
                --,BUL_ARTS
                ,PRY_ProyectoId
                ,OV_OrdenVentaId
                ,OV_CodigoOV
                ,OV_ReferenciaOC
                ,CLI_CodigoCliente
                ,CLI_NombreComercial
                ,CLI_RazonSocial
                ,CCON_Nombre
                ,CantidadTotal
                --,ART_GLN
            ORDER BY OV_CodigoOV
                ,OV_ReferenciaOC";

            $reportSource = public_path() . "/Reportes/Inventario/Empaque/rptEtiqueta.jrxml";

        }
        //dd($consulta);

        $Jasperphp = new \PHPJasper();
        $conexion = $Jasperphp->conexionJDBC();

        $parametros = array("LOGO_EMPRESA" => str_replace('\\', '/', public_path()) . "/img/logoEtiqueta.jpg",
            "EMPRESA" => $nombre_empresa,
            "NOMBRE_REPORTE" => "",
            "LEYENDA" => "App",
            "FECHA" => "",
            //"FILTRO" => $filtro,
            "LOGO_MULIIX" => str_replace('\\', '/', public_path()) . "/img/logo.png",
            "ENCABEZADO" => str_replace('\\', '/', public_path()) . "/Reportes/Plantillas/",
            "MOSTRAR_LOGO" => true
        );

        $Jasperphp->formatoPdf($reportSource, $consulta, $parametros, 'Empaque Etiqueta ', $conexion, true);

        $conexion->close();

    }

    public function cancelarBulto(){

        \DB::beginTransaction();

        try {

            //date_default_timezone_set('America/Mexico_City');
            $hoy=date('d-m-Y H:i:s');
            $empleadoId = DataBaseSession::getEmpleadoId();
            $idBulto = $_POST['idBulto'] == '' ? null : $_POST['idBulto'];

            $bulto = Bultos::find($idBulto);
            if($bulto->BUL_CMM_EstatusBultoId == 'F742508D-9B5B-4B8E-9F43-AE5C31ADD7DF'){//Recibo Completo

                \DB::rollback();

                return ['Status' => 'Error', 'Mensaje' => 'No se puede eliminar el Bulto porque ya esta Recibido Completo.'];

            }
            else{

                //consultar si tiene bultos complementos
                $consultaBultosComplementos = \DB::select(\DB::raw("SELECT * FROM Bultos WHERE BUL_BultoPadreId = '".$idBulto."' AND BUL_Eliminado = 0"));
//dd($consultaBultosComplementos);
                if(count($consultaBultosComplementos) > 0){

                    \DB::rollback();

                    return ['Status' => 'Error', 'Mensaje' => 'No se puede eliminar el Bulto porque tiene bultos complementos.'];

                }
                else{

                    $bulto->BUL_Eliminado = 1;
                    $bulto->BUL_FechaUltimaModificacion = $hoy;
                    $bulto->BUL_EMP_ModificadoPorId = $empleadoId;
                    $bulto->save();

                    $consultaBultosDetalle = \DB::select(\DB::raw("SELECT BULD_BultoDetalleId FROM BultosDetalle WHERE BULD_BUL_BultoId = '".$idBulto."'"));
                    $cuentaConsultaBultoDetalle = count($consultaBultosDetalle);
                    for($x = 0; $x < $cuentaConsultaBultoDetalle; $x ++){
                        $bultoDetalle = BultosDetalle::find($consultaBultosDetalle[$x]->BULD_BultoDetalleId);
                        $bultoDetalle->BULD_Eliminado = 1;
                        $bultoDetalle->BULD_FechaUltimaModificacion = $hoy;
                        $bultoDetalle->BULD_EMP_ModificadoPorId = $empleadoId;
                        $bultoDetalle->save();
                    }

                    $consultaBultosDetalleCaracteristicas = \DB::select(\DB::raw("SELECT BULDC_BultoDetalleCaracteristicaId FROM BultosDetalleCaracteristicas WHERE BULDC_BUL_BultoId = '".$idBulto."'"));
                    $cuentaConsultaBultosDetalleCaracteristicas = count($consultaBultosDetalleCaracteristicas);
                    for($x = 0; $x < $cuentaConsultaBultosDetalleCaracteristicas; $x ++){
                        $bultoDetalleCaracteristica = BultosDetalleCaracteristicas::find($consultaBultosDetalleCaracteristicas[$x]->BULDC_BultoDetalleCaracteristicaId);
                        $bultoDetalleCaracteristica->BULDC_Eliminado = 1;
                        $bultoDetalleCaracteristica->BULDC_FechaUltimaModificacion = $hoy;
                        $bultoDetalleCaracteristica->BULDC_EMP_ModificadoPorId = $empleadoId;
                        $bultoDetalleCaracteristica->save();
                    }

                    \DB::commit();

                    $ajaxData = array();
                    $ajaxData['codigo'] = 200;

                    echo json_encode($ajaxData);

                }

            }


        }
        catch (\Exception $e) {

            \DB::rollback();

            return ['Status' => 'Error', 'Mensaje' => 'No se registró la Caja. Ocurrió un error al realizar el proceso. <br><br><br>Error: <br>'.$e->getMessage()];

        }

    }

    public function establecerAutonumerico($autonumerico, $referenciaId) {
        try {
            $autonumerico_dao = new AutonumericoController();
            $autonumericoFicha = $autonumerico_dao->getAutonumericoN($autonumerico, is_null($referenciaId) ? null : "'".$referenciaId."'");
            return $autonumericoFicha->AUT_AutonumericoId;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

}
