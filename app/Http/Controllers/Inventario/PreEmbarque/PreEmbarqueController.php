<?php namespace App\Http\Controllers\Inventario\PreEmbarque;

use App\Http\Controllers\Sistema\AutonumericoController;
use App\Http\Controllers\Sistema\DataBaseSession;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Sistema\DAOGeneralController;
use Illuminate\Support\Facades\Request as NewRequest;
use App\Models\Bultos;
use App\Models\BultosDetalle;
use App\Models\CXCPagos;
use App\Models\FacturasProveedores;
use App\Models\PreembarqueBulto;
use App\Models\PreembarqueBultoDetalle;
use App\Models\ProgramasPagosCXP;
use App\Models\ProgramasPagosCXPDetalle;

class PreEmbarqueController extends Controller {

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
        //$precios_decimales = \DB::select(\DB::raw("SELECT CMA_Valor FROM ControlesMaestros WHERE CMA_Control = 'CMA_CCXC_DecimalesPrecios'"))[0]->CMA_Valor;
        $precios_decimales = 4;
        $porcentaje_decimales = \DB::select(\DB::raw("SELECT CMA_Valor FROM ControlesMaestros WHERE CMA_Control = 'CMA_CCXC_DecimalesPorcentajes'"))[0]->CMA_Valor;
        $tc_decimales = \DB::select(\DB::raw("SELECT CMA_Valor FROM ControlesMaestros WHERE CMA_Control = 'CMA_CCXC_DecimalesTipoCambio'"))[0]->CMA_Valor;

        //date_default_timezone_set('America/Mexico_City');
        $fecha = date('d/m/Y');

        return view('Inventario.PreEmbarque.buscadorPreEmbarque',compact('version','cantidad_decimales'
            ,'precios_decimales','porcentaje_decimales','tc_decimales','fecha'));

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
        //dafsd
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

    public function registros(){

        try{

            ini_set('memory_limit', '-1');
            set_time_limit(0);

            //$FechaInicio = NewRequest::input('fechaDesde');
            //$FechaFinal = NewRequest::input('fechaHasta');

            $consulta = \DB::select(
                \DB::raw(
                    "SELECT
                        OV_OrdenVentaId AS DT_RowId
                        ,OV_CodigoOV
                        --,CMM_Valor
                        ,CAST(OV_FechaOV AS DATE) AS OV_FechaOV
                        ,CLI_CodigoCliente + ' - ' + CLI_NombreComercial AS OV_Cliente
                        ,CLI_Calle + ' No. Ext. ' + CLI_NoExt + ' ' + CIU_Nombre + ', ' + EST_Nombre + ', ' + PAI_Nombre AS CLI_Direccion
                        ,CCON_Nombre
                        ,PRY_CodigoEvento + ' - ' + PRY_NombreProyecto AS PRY_NombreProyecto
                    FROM OrdenesVenta
                    INNER JOIN Clientes ON CLI_ClienteId = OV_CLI_ClienteId
                    INNER JOIN Ciudades ON CIU_CiudadId = CLI_CIU_CiudadId
                    INNER JOIN Estados ON EST_EstadoId = CLI_EST_EstadoId
                    INNER JOIN Paises ON PAI_PaisId = CLI_PAI_PaisId
                    INNER JOIN ClientesContactos ON CCON_ContactoId = OV_CCON_ContactoId
                    INNER JOIN Proyectos ON PRY_ProyectoId = OV_PRO_ProyectoId
                    INNER JOIN ControlesMaestrosMultiples ON CMM_ControlId = OV_CMM_EstadoOVId
                    WHERE OV_Eliminado = 0
                    AND OV_CMM_EstadoOVId = '3CE37D96-1E8A-49A7-96A1-2E837FA3DCF5' --Abierta"
                )
            );


            $ajaxData = array();
            $ajaxData['data'] = $consulta;
            $ajaxData['options'] = array();
            return (json_encode($ajaxData));

        } catch (\Exception $e){

            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json; charset=UTF-8');
            die(json_encode(array("mensaje" => $e->getMessage(),
                "codigo" => $e->getCode(),
                "clase" => $e->getFile(),
                "linea" => $e->getLine())));

        }

    }

    public function consultaDatosPorOV(){

        try {

            ini_set('memory_limit', '-1');
            set_time_limit(0);

            $ovId = $_POST['ovId'];

            $consulta = \DB::select(
                \DB::raw(
                    "SELECT
                        BULD_BultoDetalleId AS DT_RowId
                        ,ART_CodigoArticulo
                        ,ART_Nombre
                        ,PRY_CodigoEvento
                        ,PRY_NombreProyecto
                        ,OT_Codigo
                        ,BULTOS1.BUL_NumeroBulto AS BUL_NumeroBulto
                        ,CMM_Valor
                        ,CASE WHEN BULD_BultoDetalleId IS NULL
                            THEN 0
                            ELSE 1
                        END AS CHECK_BOX
                        ,CMUM_Nombre
                        ,ISNULL(CantidadInventario,0) AS CantidadInventario
                        ,ISNULL(OTDA_Cantidad,0) AS Vendido
                        ,ISNULL(SUM(Embarcado.EMBD_CantidadEmbarcada),0) AS Embarcado
                        ,ISNULL(OTDA_Cantidad,0) - ISNULL(SUM(Embarcado.EMBD_CantidadEmbarcada),0) - ISNULL(BULD2.BULD_Cantidad,0) AS Proceso
                        ,ISNULL(Empacado.Cantidad_Empacado,0) AS Empacado
                        ,ISNULL(BULD.BULD_Cantidad,0) AS PreEmbarcado
                        --,OVD_OV_OrdenVentaId
                        --,OV_CodigoOV
                    FROM OrdenesVenta
                    INNER JOIN OrdenesVentaDetalle ON OVD_OV_OrdenVentaId = OV_OrdenVentaId
                    INNER JOIN OrdenesTrabajoReferencia ON OTRE_OV_OrdenVentaId = OV_OrdenVentaId
                    INNER JOIN OrdenesTrabajo ON OT_OrdenTrabajoId = OTRE_OT_OrdenTrabajoId
                    INNER JOIN OrdenesTrabajoDetalleArticulos ON OTDA_OT_OrdenTrabajoId = OT_OrdenTrabajoId
                    INNER JOIN BultosDetalle BULD ON BULD.BULD_OT_OrdenTrabajoId = OT_OrdenTrabajoId
                    INNER JOIN Bultos BULTOS1 ON BULTOS1.BUL_BultoId = BULD.BULD_BUL_BultoId
                    INNER JOIN ControlesMaestrosMultiples ON CMM_ControlId = BULTOS1.BUL_CMM_TipoBultoId
                    INNER JOIN Articulos ON ART_ArticuloId = BULD.BULD_ART_ArticuloId
                    INNER JOIN ControlesMaestrosUM ON CMUM_UnidadMedidaId = ART_CMUM_UMInventarioId
                    INNER JOIN Proyectos ON PRY_ProyectoId = OV_PRO_ProyectoId
                    LEFT JOIN(
                        SELECT
                            SUM(EMBD_CantidadEmbarcada) AS EMBD_CantidadEmbarcada
                            ,EMBD_OVD_DetalleId
                        FROM EmbarquesDetalle
                        GROUP BY
                            EMBD_OVD_DetalleId
                    ) AS Embarcado ON OVD_DetalleId = Embarcado.EMBD_OVD_DetalleId
                    LEFT JOIN(
                        SELECT
                            COUNT(BULD_BultoDetalleId) AS Cantidad_Empacado
                            ,BULD_ART_ArticuloId
                        FROM BultosDetalle
                        WHERE (BULD_PreEmbarcado IS NULL OR BULD_PreEmbarcado = 0)
                        GROUP BY
                            BULD_ART_ArticuloId
                    ) AS Empacado ON ART_ArticuloId = Empacado.BULD_ART_ArticuloId
                    LEFT JOIN(
                        SELECT
                            ISNULL(SUM(BULD_Cantidad),0) AS BULD_Cantidad
                            ,BULD_OT_OrdenTrabajoId
                        FROM BultosDetalle
                        WHERE BULD_Eliminado = 0
                        GROUP BY
                            BULD_OT_OrdenTrabajoId
                    ) AS BULD2 ON BULD2.BULD_OT_OrdenTrabajoId = OT_OrdenTrabajoId
                    LEFT JOIN(
                        SELECT
                            TRAM_ART_ArticuloId
                            ,ISNULL(SUM(TRLOT_CantidadTraspaso),0) AS CantidadInventario
                        FROM TraspasosMovtos
                        INNER JOIN TraspasosLotes ON TRLOT_TRAM_TraspasoMovtoId = TRAM_TraspasoMovtoId
                        WHERE TRAM_CMM_TipoTransferenciaId = 'A98EEB00-F422-46CA-A45D-6B6AF2DEEF2A'
                        GROUP BY
                            TRAM_ART_ArticuloId
                    ) AS INV ON ART_ArticuloId = INV.TRAM_ART_ArticuloId
                    WHERE OV_OrdenVentaId = '".$ovId."'
                    AND (BULD_Eliminado = 0 OR BULD_Eliminado IS NULL)
                    AND (BULD_PreEmbarcado = 0 OR BULD_PreEmbarcado IS NULL)
                    GROUP BY
                        BULD_BultoDetalleId
                        ,ART_CodigoArticulo
                        ,ART_Nombre
                        ,PRY_CodigoEvento
                        ,PRY_NombreProyecto
                        ,OT_Codigo
                        ,BULTOS1.BUL_NumeroBulto
                        ,CMUM_Nombre
                        ,BULD.BULD_Cantidad
                        ,OTDA_Cantidad
                        ,CMM_Valor
                        ,INV.CantidadInventario
                        ,Empacado.Cantidad_Empacado
                        ,BULD2.BULD_Cantidad
                        --,OVD_OV_OrdenVentaId
                        --,OV_CodigoOV
                    ORDER BY
                        BULD_BultoDetalleId
                    DESC"
                )
            );

            $consulta2 = \DB::select(
                \DB::raw(
                    "SELECT
                        HIJO.BUL_BultoId AS DT_RowId
                        ,1 AS CHECK_BOX
                        ,HIJO.BUL_NumeroBulto AS NumeroBultoComplemento
                        ,CMM_Valor
                        ,PADRE.BUL_NumeroBulto AS NumeroBultoPadre
                        ,PRY_CodigoEvento
                        ,PRY_NombreProyecto
                    FROM Bultos HIJO
                    INNER JOIN Bultos PADRE ON PADRE.BUL_BultoId = HIJO.BUL_BultoPadreId
                    INNER JOIN BultosDetalle ON BULD_BUL_BultoId = PADRE.BUL_BultoId
					INNER JOIN OrdenesTrabajo ON OT_OrdenTrabajoId = BULD_OT_OrdenTrabajoId
					INNER JOIN OrdenesTrabajoReferencia ON OTRE_OT_OrdenTrabajoId = OT_OrdenTrabajoId
                    INNER JOIN OrdenesVenta ON OV_OrdenVentaId = OTRE_OV_OrdenVentaId
                    INNER JOIN Proyectos ON PRY_ProyectoId = OV_PRO_ProyectoId
                    INNER JOIN ControlesMaestrosMultiples ON CMM_ControlId = HIJO.BUL_CMM_TipoBultoId
                    WHERE (HIJO.BUL_Eliminado = 0 OR HIJO.BUL_Eliminado IS NULL)
                    AND HIJO.BUL_CMM_TipoBultoId = 'A00E0707-1CC9-4F59-8BA6-CD1DC4D82DD4'
                    AND (BULD_PreEmbarcado = 0 OR BULD_PreEmbarcado IS NULL)
                    AND OV_OrdenVentaId = '".$ovId."'
                    ORDER BY
                        NumeroBultoPadre
                    asc"
                )
            );

            $ajaxData['bultos'] = $consulta;
            $ajaxData['bultosComplemento'] = $consulta2;

            $array = array();
            $array['consulta'] = json_encode($ajaxData);

            return $array;

        }
        catch (\Exception $e){

            \DB::rollback();
            return ['Status' => 'Error', 'Mensaje' => 'Ocurrió un error al realizar el proceso. Error: ' .$e->getMessage()];

        }

    }

    public function registraPreEmbarque(){

        \DB::beginTransaction();

        try{

            //date_default_timezone_set('America/Mexico_City');
            $dia=date('d-m-Y');
            $hoy=date('d-m-Y H:i:s');

            $TablaBultos = isset($_POST['TablaBultos']) ? json_decode($_POST['TablaBultos'], true) : array();
            $TablaBultosComplemento = isset($_POST['TablaBultosComplemento']) ? json_decode($_POST['TablaBultosComplemento'], true) : array();
            $empleadoId = DataBaseSession::getEmpleadoId();

            //ACTUALIZA BANDERA BULTOS DETALLE
            $cuentaTablaBultos = count($TablaBultos);
            for($x = 0; $x < $cuentaTablaBultos; $x ++){

                $bultoDetalle = BultosDetalle::find($TablaBultos[$x]['bultoDetalleId']);
                $bultoDetalle->BULD_PreEmbarcado = 1;
                $bultoDetalle->BULD_FechaUltimaModificacion = $hoy;
                $bultoDetalle->BULD_EMP_ModificadoPorId = $empleadoId;
                $bultoDetalle->save();

            }

            $idPreEmbarqueBulto = self::getNuevoId();
            $codigoPreembarqueBulto = self::consultaCodigoPreEmbarqueBulto();

            //REGISTRA TABLA PRE EMBARQUES BULTO
            $preEmbarqueBulto = new PreembarqueBulto();
            $preEmbarqueBulto->PREB_PreembarqueBultoId = $idPreEmbarqueBulto;
            $preEmbarqueBulto->PREB_CodigoPreembarqueBulto = $codigoPreembarqueBulto;
            $preEmbarqueBulto->PREB_EMP_CreadoPorId = $empleadoId;
            $preEmbarqueBulto->save();

            //REGISTRA TABLA PRE EMBARQUES BULTO DETALLE
            for($x = 0; $x < $cuentaTablaBultos; $x ++){

                $preEmbarqueBultoDetalle = new PreembarqueBultoDetalle();
                $preEmbarqueBultoDetalle->PREBD_PREB_PreembarqueBultoId = $idPreEmbarqueBulto;
                $preEmbarqueBultoDetalle->PREBD_BULD_BultoDetalleId = $TablaBultos[$x]['bultoDetalleId'];
                $preEmbarqueBultoDetalle->PREBD_Cantidad = floatval($TablaBultos[$x]['cantidad']);
                $preEmbarqueBultoDetalle->PREBD_EMP_CreadoPorId = $empleadoId;
                $preEmbarqueBultoDetalle->PREBD_Embarcado = 0;
                $preEmbarqueBultoDetalle->save();

            }

            //REGISTRA TABLA PRE EMBARQUES BULTO COMPLEMENTO DETALLE
            $cuentaTablaBultosComplemento = count($TablaBultosComplemento);
            for($x = 0; $x < $cuentaTablaBultosComplemento; $x ++){

                $preEmbarqueBultoDetalle = new PreembarqueBultoDetalle();
                $preEmbarqueBultoDetalle->PREBD_PREB_PreembarqueBultoId = $idPreEmbarqueBulto;
                //$preEmbarqueBultoDetalle->PREBD_BULD_BultoDetalleId = null;
                //$preEmbarqueBultoDetalle->PREBD_Cantidad = null;
                $preEmbarqueBultoDetalle->PREBD_EMP_CreadoPorId = $empleadoId;
                $preEmbarqueBultoDetalle->PREBD_BUL_BultoId = $TablaBultosComplemento[$x]['bultoId'];
                $preEmbarqueBultoDetalle->PREBD_Embarcado = 0;
                $preEmbarqueBultoDetalle->save();

                $bulto = Bultos::find($TablaBultosComplemento[$x]['bultoId']);
                $bulto->BUL_CMM_EstatusBultoId = '2E47CE88-247A-43B3-89D8-71928C35B8EC';//PreEmbarcado
                $bulto->BUL_FechaUltimaModificacion = $hoy;
                $bulto->BUL_EMP_ModificadoPodId = $empleadoId;

            }

            $response = array("action" => "success");

            \DB::commit();

            return ['Status' => 'Valido', 'respuesta' => $response, 'codigoPreEmbarque' => $codigoPreembarqueBulto];

        }
        catch (\Exception $e){

            \DB::rollback();
            return ['Status' => 'Error', 'Mensaje' => 'Ocurrió un error al realizar el proceso. Error: ' .$e->getMessage()];

        }

    }

    public static function getNuevoId()
    {
        if (function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

    public function consultaCodigoPreEmbarqueBulto(){

        //////////SACAR AUTONUMERICO///////////////////////////
        $autonumerico_dao = new AutonumericoController();
        $clienteId = null;
        $empleadoId = null;
        $codigo_OC = null;

        if($autonumerico_dao->isAutonumericoActivoPorReferenciaId('CM_INV_SiguientePreEmbarqueBulto', null)){
            $autonumerico_id = self::establecerAutonumerico($clienteId, $empleadoId);
            $codigo_OC = $autonumerico_dao->getSiguienteAutonumericoPorId($autonumerico_id);
        }

        return $codigo_OC;

    }

    public function establecerAutonumerico($clienteId, $empleadoId)
    {
        try {
            $autonumerico_dao = new AutonumericoController();
            $autonumericoFicha = $autonumerico_dao->getAutonumerico($clienteId, "CM_INV_SiguientePreEmbarqueBulto", $empleadoId);
            return $autonumericoFicha->AUT_AutonumericoId;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

}
