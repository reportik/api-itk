<?php namespace App\Http\Controllers\Inventario\EmbarqueWeb;

use App\Http\Controllers\CFDI\EncabezadoPDF;
use App\Http\Controllers\Sistema\AutonumericoController;
use App\Http\Controllers\Sistema\DataBaseSession;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Sistema\DAOGeneralController;
use Illuminate\Support\Facades\Request as NewRequest;
use App\Mapeos\Controles\ControlesMaestrosMultiples;
use App\Models\Bultos;
use App\Models\BultosDetalle;
use App\Models\ControlesMaestrosUM;
use App\Models\CXCPagos;
use App\Models\EmbarquesBultos;
use App\Models\EmbarquesBultosDetalle;
use App\Models\ExistenciasKardex\CMMult;
use App\Models\FacturasProveedores;
use App\Models\Inventario\Articulos\Articulo;
use App\Models\Inventario\InventarioFisico\DetallesMovimientoInventario;
use App\Models\Inventario\InventarioFisico\ProcesadorMovimientoInventarios;
use App\Models\Inventario\InventarioFisico\TraspasoMovto;
use App\Models\inventario\LocalidadesArticulos;
use App\Models\Localidades;
use App\Models\Lotes;
use App\Models\LotesLocalidades;
use App\Models\PreembarqueBulto;
use App\Models\PreembarqueBultoDetalle;
use App\Models\ProgramasPagosCXP;
use App\Models\ProgramasPagosCXPDetalle;
require_once(public_path().'/plugins/PHPJasper/PHPJasper.php');

class EmbarqueWebController extends Controller {

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
        //$this->generaPDF('F7593A4F-5D9D-4330-89BE-0C7819F2CFA2');
        $cantidad_decimales = \DB::select(\DB::raw("SELECT CMA_Valor FROM ControlesMaestros WHERE CMA_Control = 'CMA_CCXC_DecimalesCantidades'"))[0]->CMA_Valor;
        //$precios_decimales = \DB::select(\DB::raw("SELECT CMA_Valor FROM ControlesMaestros WHERE CMA_Control = 'CMA_CCXC_DecimalesPrecios'"))[0]->CMA_Valor;
        $precios_decimales = 4;
        $porcentaje_decimales = \DB::select(\DB::raw("SELECT CMA_Valor FROM ControlesMaestros WHERE CMA_Control = 'CMA_CCXC_DecimalesPorcentajes'"))[0]->CMA_Valor;
        $tc_decimales = \DB::select(\DB::raw("SELECT CMA_Valor FROM ControlesMaestros WHERE CMA_Control = 'CMA_CCXC_DecimalesTipoCambio'"))[0]->CMA_Valor;

        $password = \DB::select(\DB::raw("SELECT CMM_Valor FROM ControlesMaestrosMultiples WHERE CMM_Etiqueta = 'Embarque Web'"))[0]->CMM_Valor;

        //date_default_timezone_set('America/Mexico_City');
        $fecha = date('d/m/Y');

        return view('Inventario.EmbarqueWeb.embarquesWebGuardados',compact('version','cantidad_decimales'
            ,'precios_decimales','porcentaje_decimales','tc_decimales','fecha','password'));

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

    public function registrosEmbarqueWeb(){

        try{

            ini_set('memory_limit', '-1');
            set_time_limit(0);

            $FechaInicio = NewRequest::input('fechaDesde');
            $FechaFinal = NewRequest::input('fechaHasta');

            $consulta = \DB::select(
                \DB::raw(
                    "SELECT
                        EMBB_EmbarqueBultoId AS DT_RowId
                        ,EMBB_CodigoEmbarqueBulto
                        ,EMBB_FechaCreacion
                        ,EMP_CodigoEmpleado + ' - ' + EMP_Nombre + ' ' + EMP_PrimerApellido + ' ' + EMP_SegundoApellido AS EMP_Nombre
                        ,PREB_CodigoPreembarqueBulto
                        ,OV_CodigoOV
                        ,CAST(OV_FechaOV AS DATE) AS OV_FechaOV
                        ,CLI_CodigoCliente + ' - ' + CLI_NombreComercial AS OV_Cliente
                        ,CLI_Calle + ' No. Ext. ' + CLI_NoExt + ' ' + CIU_Nombre + ', ' + EST_Nombre + ', ' + PAI_Nombre AS CLI_Direccion
                        ,CCON_Nombre
                        ,PRY_CodigoEvento + ' - ' + PRY_NombreProyecto AS PRY_NombreProyecto
                    FROM EmbarquesBultos
                    INNER JOIN EmbarquesBultosDetalle ON EMBBD_EMBB_EmbarqueBultoId = EMBB_EmbarqueBultoId
                    INNER JOIN PreembarqueBultoDetalle ON PREBD_PreembarqueBultoDetalleId = EMBBD_PREBD_PreembarqueBultoDetalleId
                    INNER JOIN PreembarqueBulto ON PREB_PreembarqueBultoId = PREBD_PREB_PreembarqueBultoId
                    INNER JOIN Empleados ON EMP_EmpleadoId = EMBB_EMP_CreadoPorId
                    INNER JOIN BultosDetalle ON BULD_BultoDetalleId = PREBD_BULD_BultoDetalleId
                    INNER JOIN OrdenesTrabajo ON OT_OrdenTrabajoId = BULD_OT_OrdenTrabajoId
                    INNER JOIN OrdenesTrabajoReferencia ON OTRE_OT_OrdenTrabajoId = OT_OrdenTrabajoId
                    INNER JOIN OrdenesVenta ON OV_OrdenVentaId = OTRE_OV_OrdenVentaId
                    INNER JOIN Proyectos ON PRY_ProyectoId = OV_PRO_ProyectoId
                    INNER JOIN Clientes ON CLI_ClienteId = OV_CLI_ClienteId
                    INNER JOIN Ciudades ON CIU_CiudadId = CLI_CIU_CiudadId
                    INNER JOIN Estados ON EST_EstadoId = CLI_EST_EstadoId
                    INNER JOIN Paises ON PAI_PaisId = CLI_PAI_PaisId
                    INNER JOIN ClientesContactos ON CCON_ContactoId = OV_CCON_ContactoId
                    WHERE EMBB_Eliminado = 0
                    AND CAST(EMBB_FechaCreacion AS DATE) BETWEEN '".$FechaInicio."' AND '".$FechaFinal."'
                    GROUP BY
                        EMBB_EmbarqueBultoId
                        ,EMBB_CodigoEmbarqueBulto
                        ,EMBB_FechaCreacion
                        ,EMP_CodigoEmpleado + ' - ' + EMP_Nombre + ' ' + EMP_PrimerApellido + ' ' + EMP_SegundoApellido
                        ,PREB_CodigoPreembarqueBulto
                        ,OV_CodigoOV
                        ,OV_FechaOV
                        ,CLI_CodigoCliente + ' - ' + CLI_NombreComercial
                        ,CLI_Calle + ' No. Ext. ' + CLI_NoExt + ' ' + CIU_Nombre + ', ' + EST_Nombre + ', ' + PAI_Nombre
                        ,CCON_Nombre
                        ,PRY_CodigoEvento + ' - ' + PRY_NombreProyecto"
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

    public function registros(){

        try{

            ini_set('memory_limit', '-1');
            set_time_limit(0);

            $FechaInicio = NewRequest::input('fechaDesde');
            $FechaFinal = NewRequest::input('fechaHasta');

            $consulta = \DB::select(
                \DB::raw(
                    "SELECT
                        PREB_PreembarqueBultoId AS DT_RowId
                        ,PREB_CodigoPreembarqueBulto
                        ,CAST(PREB_FechaCreacion AS DATE) AS PREB_FechaCreacion
                        ,EMP_Nombre + ' ' + EMP_PrimerApellido + ' ' + EMP_SegundoApellido AS EMP_Nombre
                        ,OV_CodigoOV
                        ,CAST(OV_FechaOV AS DATE) AS OV_FechaOV
                        ,CLI_CodigoCliente + ' - ' + CLI_NombreComercial AS OV_Cliente
                        ,CLI_Calle + ' No. Ext. ' + CLI_NoExt + ' ' + CIU_Nombre + ', ' + EST_Nombre + ', ' + PAI_Nombre AS CLI_Direccion
                        ,CCON_Nombre
                        ,PRY_CodigoEvento + ' - ' + PRY_NombreProyecto AS PRY_NombreProyecto
                        ,0 AS CHECK_BOX
                        ,PREBD_Turno
                        --,PREBD_Embarcado
                    FROM PreembarqueBulto
                    INNER JOIN Empleados ON EMP_EmpleadoId = PREB_EMP_CreadoPorId
                    INNER JOIN(
                        SELECT
                            COUNT(PREBD_Embarcado) AS PREBD_Embarcado
                            ,PREBD_PREB_PreembarqueBultoId
                            , BULD_BultoDetalleId AS PREBD_BULD_BultoDetalleId
                        FROM PreembarqueBulto
                        INNER JOIN PreembarqueBultoDetalle on PREBD_PREB_PreembarqueBultoId = PREB_PreembarqueBultoId
                        LEFT JOIN Bultos ON BUL_BultoId = PREBD_BUL_BultoId
                        LEFT JOIN BultosDetalle on BULD_BultoDetalleId = PREBD_BULD_BultoDetalleId--ISNULL(PREBD_BULD_BultoDetalleId, (SELECT BULD_BultoDetalleId FROM BultosDetalle WHERE BULD_BUL_BultoId = BUL_BultoPadreId))
                        WHERE PREBD_Embarcado = 0
                        AND PREBD_Eliminado = 0
                        --AND PREBD_PREB_PreembarqueBultoId = '113B88CB-CFBE-40A1-BEF7-3B11547A2F43'
                        GROUP BY
                            PREBD_PREB_PreembarqueBultoId
                            ,BULD_BultoDetalleId
                    ) AS TEMP ON TEMP.PREBD_PREB_PreembarqueBultoId = PREB_PreembarqueBultoId
                    INNER JOIN BultosDetalle ON BULD_BultoDetalleId = TEMP.PREBD_BULD_BultoDetalleId
                    INNER JOIN OrdenesTrabajo ON OT_OrdenTrabajoId = BULD_OT_OrdenTrabajoId
                    INNER JOIN OrdenesTrabajoReferencia ON OTRE_OT_OrdenTrabajoId = OT_OrdenTrabajoId
                    INNER JOIN OrdenesVenta ON OV_OrdenVentaId = OTRE_OV_OrdenVentaId
                    INNER JOIN Proyectos ON PRY_ProyectoId = OV_PRO_ProyectoId
                    INNER JOIN Clientes ON CLI_ClienteId = OV_CLI_ClienteId
                    INNER JOIN Ciudades ON CIU_CiudadId = CLI_CIU_CiudadId
                    INNER JOIN Estados ON EST_EstadoId = CLI_EST_EstadoId
                    INNER JOIN Paises ON PAI_PaisId = CLI_PAI_PaisId
                    INNER JOIN ClientesContactos ON CCON_ContactoId = OV_CCON_ContactoId
                    WHERE PREB_Eliminado = 0
                    AND TEMP.PREBD_Embarcado > 0
                    AND CAST(PREB_FechaCreacion AS DATE) BETWEEN '".$FechaInicio."' AND '".$FechaFinal."'
                    --AND PREB_CodigoPreembarqueBulto = 'PREB00318'
                    GROUP BY
                        PREB_PreembarqueBultoId
                        ,PREB_CodigoPreembarqueBulto
                        ,PREB_FechaCreacion
                        ,EMP_Nombre + ' ' + EMP_PrimerApellido + ' ' + EMP_SegundoApellido
                        ,OV_CodigoOV
                        ,OV_FechaOV
                        ,CLI_CodigoCliente + ' - ' + CLI_NombreComercial
                        ,CLI_Calle + ' No. Ext. ' + CLI_NoExt + ' ' + CIU_Nombre + ', ' + EST_Nombre + ', ' + PAI_Nombre
                        ,CCON_Nombre
                        ,PRY_CodigoEvento + ' - ' + PRY_NombreProyecto
                        ,PREBD_Turno"
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

    public function consultaDatosPorId(){

        try {

            ini_set('memory_limit', '-1');
            set_time_limit(0);

            $preEmbarqueId = $_POST['preEmbarqueId'];

            $consulta = \DB::select(
                \DB::raw(
                    "SELECT
                        PREBD_PreembarqueBultoDetalleId AS DT_RowId
                        ,ART_CodigoArticulo
                        ,ART_Nombre
                        ,BUL_NumeroBulto
                        ,PREBD_Cantidad
                        ,0 AS CHECK_BOX
                        ,BUL_BultoId
                        ,TIPO.CMM_Valor
                        ,'' AS BultoPadre
                        ,ART_ArticuloId
                        ,ART_CMUM_UMInventarioId
                        ,PREBD_Embarcado
                        ,BULD_OT_OrdenTrabajoId
                        ,BUL_CMM_TipoBultoId
                        ,ART_CantidadAMano
                        ,CMUM_Nombre
                        --,BOR_TRAM_TraspasoMovtoId as traspasoMovtoId
                        ,null as traspasoMovtoId
                        ,EST.CMM_Valor AS BUL_Estatus
                    FROM PreembarqueBultoDetalle
                    INNER JOIN BultosDetalle ON BULD_BultoDetalleId = PREBD_BULD_BultoDetalleId AND PREBD_Eliminado = 0 AND PREBD_Embarcado = 0
                    INNER JOIN Bultos ON BUL_BultoId = BULD_BUL_BultoId
                    INNER JOIN Articulos ON ART_ArticuloId = BULD_ART_ArticuloId
                    INNER JOIN ControlesMaestrosUM ON ART_CMUM_UMInventarioId = CMUM_UnidadMedidaId
                    INNER JOIN ControlesMaestrosMultiples TIPO ON TIPO.CMM_ControlId = BUL_CMM_TipoBultoId
                    INNER JOIN ControlesMaestrosMultiples EST ON EST.CMM_ControlId = BUL_CMM_EstatusBultoId
                    LEFT JOIN BultoOTRecibo ON BOR_BUL_BultoId = BUL_BultoId
                    WHERE PREBD_PREB_PreembarqueBultoId = '".$preEmbarqueId."'
                    GROUP BY
                        PREBD_PreembarqueBultoDetalleId
                        ,ART_CodigoArticulo
                        ,ART_Nombre
                        ,BUL_NumeroBulto
                        ,PREBD_Cantidad                        
                        ,BUL_BultoId
                        ,TIPO.CMM_Valor                        
                        ,ART_ArticuloId
                        ,ART_CMUM_UMInventarioId
                        ,PREBD_Embarcado
                        ,BULD_OT_OrdenTrabajoId
                        ,BUL_CMM_TipoBultoId
                        ,ART_CantidadAMano
                        ,CMUM_Nombre                                                
                        ,EST.CMM_Valor

                    UNION ALL

                    SELECT
                        PREBD_PreembarqueBultoDetalleId AS DT_RowId
                        ,ART_CodigoArticulo
                        ,ART_Nombre
                        ,HIJO.BUL_NumeroBulto
                        ,1 AS PREBD_Cantidad
                        ,0 AS CHECK_BOX
                        ,HIJO.BUL_BultoId
                        ,TIPO.CMM_Valor
                        ,PADRE.BUL_NumeroBulto AS BultoPadre
                        ,ART_ArticuloId
                        ,ART_CMUM_UMInventarioId
                        ,PREBD_Embarcado
                        ,BULD_OT_OrdenTrabajoId
                        ,HIJO.BUL_CMM_TipoBultoId AS BUL_CMM_TipoBultoId
                        ,ART_CantidadAMano
                        ,CMUM_Nombre
                        --,BOR_TRAM_TraspasoMovtoId as traspasoMovtoId
                        ,null as traspasoMovtoId
                        ,EST.CMM_Valor AS BUL_Estatus
                    FROM PreembarqueBultoDetalle
                    INNER join Bultos HIJO on HIJO.BUL_BultoId = PREBD_BUL_BultoId AND PREBD_Eliminado = 0 AND PREBD_Embarcado = 0
                    left join Bultos PADRE ON PADRE.BUL_BultoId = HIJO.BUL_BultoPadreId
                    LEFT JOIN BultosDetalle on BULD_BUL_BultoId = PADRE.BUL_BultoId
                    left JOIN Articulos ON ART_ArticuloId = BULD_ART_ArticuloId
                    left JOIN ControlesMaestrosUM ON ART_CMUM_UMInventarioId = CMUM_UnidadMedidaId
                    left JOIN ControlesMaestrosMultiples TIPO ON TIPO.CMM_ControlId = HIJO.BUL_CMM_TipoBultoId
                    LEFT JOIN BultoOTRecibo ON BOR_BUL_BultoId = HIJO.BUL_BultoId
                    LEFT JOIN ControlesMaestrosMultiples EST ON EST.CMM_ControlId = HIJO.BUL_CMM_EstatusBultoId
                    WHERE PREBD_PREB_PreembarqueBultoId = '$preEmbarqueId'
                    AND PREBD_Eliminado = 0
                    GROUP BY 
                        PREBD_PreembarqueBultoDetalleId
                        ,ART_CodigoArticulo
                        ,ART_Nombre
                        ,HIJO.BUL_NumeroBulto
                        ,PREBD_Cantidad                        
                        ,HIJO.BUL_BultoId
                        ,TIPO.CMM_Valor
                        ,PADRE.BUL_NumeroBulto
                        ,ART_ArticuloId
                        ,ART_CMUM_UMInventarioId
                        ,PREBD_Embarcado
                        ,BULD_OT_OrdenTrabajoId
                        ,HIJO.BUL_CMM_TipoBultoId
                        ,ART_CantidadAMano
                        ,CMUM_Nombre                        
                        ,EST.CMM_Valor
                    order by 
                        bul_numerobulto
                    "                    
                )
            );

            $ajaxData['detalle'] = $consulta;

            $array = array();
            $array['consulta'] = json_encode($ajaxData);

            return $array;

        }
        catch (\Exception $e){

            \DB::rollback();
            return ['Status' => 'Error', 'Mensaje' => 'Ocurrió un error al realizar el proceso. Error: ' .$e->getMessage()];

        }

    }

    public function registraEmbarqueWeb(){

        \DB::beginTransaction();

        try{

            ini_set('memory_limit', '-1');
            set_time_limit(0);

            //date_default_timezone_set('America/Mexico_City');
            //$dia=date('d-m-Y');
            $hoy=date('d-m-Y H:i:s');
            $dia = date('d');
            $mes = date('m');

            $chofer = $_POST['chofer'] == '' ? null : $_POST['chofer'];
            $liciencia = $_POST['liciencia'] == '' ? null : $_POST['liciencia'];
            $celular = $_POST['celular'] == '' ? null : $_POST['celular'];
            $lineaTrasporte = $_POST['lineaTrasporte'] == '' ? null : $_POST['lineaTrasporte'];
            $placasTractor = $_POST['placasTractor'] == '' ? null : $_POST['placasTractor'];
            $placasCaja = $_POST['placasCaja'] == '' ? null : $_POST['placasCaja'];
            $TablaDetalle = isset($_POST['TablaDetalle']) ? json_decode($_POST['TablaDetalle'], true) : array();
            $empleadoId = DataBaseSession::getEmpleadoId();
            $idEmbarqueBulto = self::getNuevoId();
            $codigoEmbarqueBulto = self::consultaCodigoEmbarqueBulto();

            //INSERTA NUEVO EMBARQUE BULTO
            $embb = new EmbarquesBultos();
            $embb->EMBB_EmbarqueBultoId = $idEmbarqueBulto;
            $embb->EMBB_CodigoEmbarqueBulto = $codigoEmbarqueBulto;
            $embb->EMBB_Chofer = $chofer;
            $embb->EMBB_Licencia = $liciencia;
            $embb->EMBB_Celular = $celular;
            $embb->EMBB_LineaTransporte = $lineaTrasporte;
            $embb->EMBB_PlacasTractor = $placasTractor;
            $embb->EMBB_PlacasCaja = $placasCaja;
            $embb->EMBB_EMP_CreadoPorId = $empleadoId;
            $embb->save();

            //ACTUALIZA BANDERA BULTOS DETALLE
            $cuentaTablaDetalle = count($TablaDetalle);
            for($x = 0; $x < $cuentaTablaDetalle; $x ++){

                //VALIDA PREBD_Embarcado
                if($TablaDetalle[$x]['PREBD_Embarcado'] == 1){
                    throw new \Exception("No puedes embarcar el bulto ".$TablaDetalle[$x]['BUL_NumeroBulto']." porque ya ha sido embarcado.", 140);
                }

                //ACTUALIZA BULTO
                \DB::table('Bultos')
                    ->where('BUL_BultoId', '=', $TablaDetalle[$x]['bultoId'])
                    ->update(
                        array(
                            'BUL_CMM_EstatusBultoId' => '2E47CE88-247A-43B3-89D8-71928C35B8EC',//PRE-EMBARCADO
                            'BUL_FechaUltimaModificacion' => $hoy,
                            'BUL_EMP_ModificadoPorId' => $empleadoId
                        )
                    );

                //GUArDA EmbarquesBultosDetalle
                $embbdetalle = self::getNuevoId();

                $embbd = new EmbarquesBultosDetalle();
                $embbd->EMBBD_EmbarqueBultoDetalleId = $embbdetalle;
                $embbd->EMBBD_EMBB_EmbarqueBultoId = $idEmbarqueBulto;
                $embbd->EMBBD_PREBD_PreembarqueBultoDetalleId = $TablaDetalle[$x]['detalleId'];
                $embbd->EMBBD_Cantidad = floatval($TablaDetalle[$x]['cantidad']);
                $embbd->EMBBD_EMP_CreadoPorId = $empleadoId;
                $embbd->save();

                //ACTUALIZA PreembarqueBultoDetalle
                \DB::table('PreembarqueBultoDetalle')
                    ->where('PREBD_PreembarqueBultoDetalleId', '=', $TablaDetalle[$x]['detalleId'])
                    ->update(
                        array(
                            'PREBD_Embarcado' => 1,
                            'PREBD_FechaUltimaModificacion' => $hoy,
                            'PREBD_EMP_ModificadoPorId' => $empleadoId
                        )
                    );

                //VALIDA BUL_CMM_TipoBultoId
                if($TablaDetalle[$x]['BUL_CMM_TipoBultoId'] == 'CDBBF4F2-3A62-475B-A0AB-B235496DFE7D') {//PRINCIPAL

                    $consulta = "SELECT
                                    SUM(LOTL_Cantidad) AS LOTL_Cantidad
                                FROM
                                (
                                SELECT
                                    --ISNULL(SUM(LOTL_Cantidad), 0.0) AS EXISTENCIA
                                    LOTL_LOT_LoteId,LOTL_LOC_LocalidadId,LOTL_LoteLocalidadId AS TRLOT_LOTL_LoteLocalidadId,ISNULL(SUM(LOTL_Cantidad), 0.0) AS LOTL_Cantidad,LOC_ALM_AlmacenId
                                FROM LotesLocalidades
                                INNER JOIN Lotes ON LOTL_LOT_LoteId = LOT_LoteId
                                INNER JOIN Localidades ON LOC_LocalidadId = LOTL_LOC_LocalidadId
                                WHERE LOT_ART_ArticuloId = '".$TablaDetalle[$x]['articuloId']."'
                                    AND LOTL_Eliminado = 0
                                    AND LOTL_Cantidad > 0
                                GROUP BY
                                    LOTL_LOT_LoteId
                                    ,LOTL_LOC_LocalidadId
                                    ,LOTL_LoteLocalidadId
                                    ,LOTL_Cantidad
                                    ,LOC_ALM_AlmacenId
                                ) AS TEMP";

                    $existenciaActual = \DB::select(\DB::raw($consulta));
                    if(count($existenciaActual) > 0) {
                        $cantidadExistencia = round($existenciaActual[0]->LOTL_Cantidad, 6, PHP_ROUND_HALF_UP);
                        if($cantidadExistencia < floatval($TablaDetalle[$x]['cantidad'])) {
                            throw new \Exception("El artículo ".$TablaDetalle[$x]['ART_CodigoArticulo']." tiene una existencia actual de ".$cantidadExistencia." y quieres hacer un movimiento en el inventario de ".floatval($TablaDetalle[$x]['cantidad']).". No puedes dejar el inventario en negativo.", 140);
                        }
                    } else {
                        throw new \Exception("No puedes embarcar el bulto ".$TablaDetalle[$x]['BUL_NumeroBulto']." porque no ha sido recibido.", 140);
                        //throw new \Exception("El artículo ".$articulo->ART_CodigoArticulo." tiene una existencia actual de 0 y quieres hacer un movimiento en el inventario de ".floatval($TablaDetalle[$x]['cantidad']).". No puedes dejar el inventario en negativo.", 140);
                    }

                    $consulta2 = "SELECT
                                    --ISNULL(SUM(LOTL_Cantidad), 0.0) AS EXISTENCIA
                                    LOTL_LOT_LoteId,LOTL_LOC_LocalidadId,LOTL_LoteLocalidadId AS TRLOT_LOTL_LoteLocalidadId,ISNULL(SUM(LOTL_Cantidad), 0.0) AS LOTL_Cantidad,LOC_ALM_AlmacenId
                                FROM LotesLocalidades
                                INNER JOIN Lotes ON LOTL_LOT_LoteId = LOT_LoteId
                                INNER JOIN Localidades ON LOC_LocalidadId = LOTL_LOC_LocalidadId
                                WHERE LOT_ART_ArticuloId = '".$TablaDetalle[$x]['articuloId']."'
                                    AND LOTL_Eliminado = 0
                                    AND LOTL_Cantidad > 0
                                GROUP BY
                                    LOTL_LOT_LoteId
                                    ,LOTL_LOC_LocalidadId
                                    ,LOTL_LoteLocalidadId
                                    ,LOTL_Cantidad
                                    ,LOC_ALM_AlmacenId";
                    $existenciaActual2 = \DB::select(\DB::raw($consulta2));
                    $cantidadAguardar = floatval($TablaDetalle[$x]['cantidad']);
                    for($y = 0; $y < count($existenciaActual2); $y ++){
                        $cantidadExistencia2 = round($existenciaActual2[$y]->LOTL_Cantidad, 6, PHP_ROUND_HALF_UP);
                        if($cantidadAguardar <= $cantidadExistencia2){

                            //GUARDA TRASPASO MOVTO
                            $TraspasosMovtos = new TraspasoMovto();
                            $TraspasosMovtos->TRAM_ART_ArticuloId = $TablaDetalle[$x]['articuloId'];
                            //$TraspasosMovtos->TRAM_CantidadATraspasar = (-1 * floatval($TablaDetalle[$x]['cantidad']));
                            $TraspasosMovtos->TRAM_CantidadATraspasar = (-1 * $cantidadAguardar);
                            $TraspasosMovtos->TRAM_Razon = "Embarque web: ".$TablaDetalle[$x]['ART_CodigoArticulo'];
                            //$TraspasosMovtos->TRAM_Referencia = "Embarque web: ".$TablaDetalle[$x]['ART_CodigoArticulo']." Cantidad: ".floatval($TablaDetalle[$x]['cantidad']);
                            $TraspasosMovtos->TRAM_Referencia = "Embarque web: ".$TablaDetalle[$x]['ART_CodigoArticulo']." Cantidad: ".$cantidadAguardar;
                            $TraspasosMovtos->TRAM_UnidadMedidadArt = $TablaDetalle[$x]['CMUM_Nombre'];
                            $TraspasosMovtos->TRAM_EstatusContable = false;
                            $TraspasosMovtos->TRAM_CantidadAMano = $TablaDetalle[$x]['ART_CantidadAMano'];
                            //$TraspasosMovtos->TRAM_CantidadAManoConTraspaso = $TablaDetalle[$x]['ART_CantidadAMano'] + floatval($TablaDetalle[$x]['cantidad']);
                            $TraspasosMovtos->TRAM_CantidadAManoConTraspaso = $TablaDetalle[$x]['ART_CantidadAMano'] + $cantidadAguardar;
                            $TraspasosMovtos->TRAM_CMM_TipoTransferenciaId = 'FB9DD40D-14AB-4AD4-AB2E-AD887C80FDE3';//EMABARQUE
                            $TraspasosMovtos->TRAM_ReferenciaMovtoId = $embbd->EMBBD_EmbarqueBultoDetalleId;
                            $TraspasosMovtos->TRAM_DefinidoPorUsuario1 = $TablaDetalle[$x]['BULD_OT_OrdenTrabajoId'];
                            //$TraspasosMovtos->save();

                            //LLENA OBJETO PARA ENVIAR A PROCESADOR
                            $arrayDetallesMovimiento = array();
                            $dmi = new DetallesMovimientoInventario();

                            $dmi->setCantidadTransferir($TraspasosMovtos->TRAM_CantidadATraspasar);
                            $dmi->setIdAlmacen($existenciaActual2[$y]->LOC_ALM_AlmacenId);

                            $localidad = new Localidades();
                            $localidad->COL_LOCALIDAD_ID = $existenciaActual2[$y]->LOTL_LOC_LocalidadId;
                            //$localidad->COL_LOCALIDAD_ID = '62EAAF01-1020-4C75-9503-D58B07FFC6EF';//L301_TERMINADO
                            $dmi->setLocalidad($localidad);

                            $lotes = new Lotes();
                            $lotes->COL_LOTE_ID = $existenciaActual2[$y]->LOTL_LOT_LoteId;
                            $dmi->setLote($lotes);

                            array_push($arrayDetallesMovimiento, $dmi);

                            //ENVIAR INFORMACION A PROCESADOR
                            ProcesadorMovimientoInventarios::registraMovimientoEnInventario($TraspasosMovtos, $arrayDetallesMovimiento, null);

                            break;

                        }
                        else{

                            //GUARDA TRASPASO MOVTO
                            $TraspasosMovtos = new TraspasoMovto();
                            $TraspasosMovtos->TRAM_ART_ArticuloId = $TablaDetalle[$x]['articuloId'];
                            //$TraspasosMovtos->TRAM_CantidadATraspasar = (-1 * floatval($TablaDetalle[$x]['cantidad']));
                            $TraspasosMovtos->TRAM_CantidadATraspasar = (-1 * $cantidadExistencia2);
                            $TraspasosMovtos->TRAM_Razon = "Embarque web: ".$TablaDetalle[$x]['ART_CodigoArticulo'];
                            //$TraspasosMovtos->TRAM_Referencia = "Embarque web: ".$TablaDetalle[$x]['ART_CodigoArticulo']." Cantidad: ".floatval($TablaDetalle[$x]['cantidad']);
                            $TraspasosMovtos->TRAM_Referencia = "Embarque web: ".$TablaDetalle[$x]['ART_CodigoArticulo']." Cantidad: ".$cantidadExistencia2;
                            $TraspasosMovtos->TRAM_UnidadMedidadArt = $TablaDetalle[$x]['CMUM_Nombre'];
                            $TraspasosMovtos->TRAM_EstatusContable = false;
                            $TraspasosMovtos->TRAM_CantidadAMano = $TablaDetalle[$x]['ART_CantidadAMano'];
                            //$TraspasosMovtos->TRAM_CantidadAManoConTraspaso = $TablaDetalle[$x]['ART_CantidadAMano'] + floatval($TablaDetalle[$x]['cantidad']);
                            $TraspasosMovtos->TRAM_CantidadAManoConTraspaso = $TablaDetalle[$x]['ART_CantidadAMano'] + $cantidadExistencia2;
                            $TraspasosMovtos->TRAM_CMM_TipoTransferenciaId = 'FB9DD40D-14AB-4AD4-AB2E-AD887C80FDE3';//EMABARQUE
                            $TraspasosMovtos->TRAM_ReferenciaMovtoId = $embbd->EMBBD_EmbarqueBultoDetalleId;
                            $TraspasosMovtos->TRAM_DefinidoPorUsuario1 = $TablaDetalle[$x]['BULD_OT_OrdenTrabajoId'];
                            //$TraspasosMovtos->save();

                            //LLENA OBJETO PARA ENVIAR A PROCESADOR
                            $arrayDetallesMovimiento = array();
                            $dmi = new DetallesMovimientoInventario();

                            $dmi->setCantidadTransferir($TraspasosMovtos->TRAM_CantidadATraspasar);
                            $dmi->setIdAlmacen($existenciaActual2[$y]->LOC_ALM_AlmacenId);

                            $localidad = new Localidades();
                            $localidad->COL_LOCALIDAD_ID = $existenciaActual2[$y]->LOTL_LOC_LocalidadId;
                            //$localidad->COL_LOCALIDAD_ID = '62EAAF01-1020-4C75-9503-D58B07FFC6EF';//L301_TERMINADO
                            $dmi->setLocalidad($localidad);

                            $lotes = new Lotes();
                            $lotes->COL_LOTE_ID = $existenciaActual2[$y]->LOTL_LOT_LoteId;
                            $dmi->setLote($lotes);

                            array_push($arrayDetallesMovimiento, $dmi);

                            //ENVIAR INFORMACION A PROCESADOR
                            ProcesadorMovimientoInventarios::registraMovimientoEnInventario($TraspasosMovtos, $arrayDetallesMovimiento, null);

                            $cantidadAguardar = $cantidadAguardar - $cantidadExistencia2;

                        }

                    }

                }

            }

            //GENERA PDF
            $this->generaPDF($idEmbarqueBulto);

            $response = array("action" => "success");

            \DB::commit();

            return ['Status' => 'Valido', 'respuesta' => $response, 'codigoEmbarqueBulto' => $codigoEmbarqueBulto];

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

    public function consultaCodigoEmbarqueBulto(){

        //////////SACAR AUTONUMERICO///////////////////////////
        $autonumerico_dao = new AutonumericoController();
        $clienteId = null;
        $empleadoId = null;
        $codigo_OC = null;

        if($autonumerico_dao->isAutonumericoActivoPorReferenciaId('CM_INV_SiguienteEmbarqueBulto', null)){
            $autonumerico_id = self::establecerAutonumerico($clienteId, $empleadoId);
            $codigo_OC = $autonumerico_dao->getSiguienteAutonumericoPorId($autonumerico_id);
        }

        return $codigo_OC;

    }

    public function establecerAutonumerico($clienteId, $empleadoId)
    {
        try {
            $autonumerico_dao = new AutonumericoController();
            $autonumericoFicha = $autonumerico_dao->getAutonumerico($clienteId, "CM_INV_SiguienteEmbarqueBulto", $empleadoId);
            return $autonumericoFicha->AUT_AutonumericoId;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public function regresarBultoOV(){

        ini_set('memory_limit', '-1');
        set_time_limit(0);

        \DB::beginTransaction();

        try{

            //date_default_timezone_set('America/Mexico_City');
            $hoy=date('d-m-Y H:i:s');
            $idEmpleado = DataBaseSession::getEmpleadoId();
            $PREBD_PreembarqueBultoDetalleId = $_POST['PREBD_PreembarqueBultoDetalleId'];

            $preEBD = PreembarqueBultoDetalle::find($PREBD_PreembarqueBultoDetalleId);

            if($preEBD->PREBD_BULD_BultoDetalleId != null){

                $bultoDet = BultosDetalle::find($preEBD->PREBD_BULD_BultoDetalleId);
                $bultoDet->BULD_PreEmbarcado = 0;
                $bultoDet->BULD_FechaUltimaModificacion = $hoy;
                $bultoDet->BULD_EMP_ModificadoPorId = $idEmpleado;
                $bultoDet->save();

                $preEBD->PREBD_Eliminado = 1;
                $preEBD->PREBD_FechaUltimaModificacion = $hoy;
                $preEBD->PREBD_EMP_ModificadoPorId = $idEmpleado;
                $preEBD->save();

                /////////////////////////////////////ELIMINAR HERMANOS E HIJO//////////////////////////////
                $consulta = "SELECT
                                BUL_BultoId
                            FROM Bultos
                            INNER JOIN BultosDetalle ON BULD_BUL_BultoId = BUL_BultoId
                            WHERE BULD_BultoDetalleId =  '".$preEBD->PREBD_BULD_BultoDetalleId."'";
                $bultoPadre = \DB::select(\DB::raw($consulta));

                $consulta = "SELECT
                              PREBD_PreembarqueBultoDetalleId
                            FROM Bultos
                            INNER JOIN PreembarqueBultoDetalle ON PREBD_BUL_BultoId = BUL_BultoId
                            WHERE BUL_BultoPadreId = '".$bultoPadre[0]->BUL_BultoId."'
                            AND PREBD_Eliminado = 0";
                $bultoHijos = \DB::select(\DB::raw($consulta));

                for($x = 0; $x < count($bultoHijos); $x++){

                    $preEBD3 = PreembarqueBultoDetalle::find($bultoHijos[$x]->PREBD_PreembarqueBultoDetalleId);
                    $preEBD3->PREBD_Eliminado = 1;
                    $preEBD3->PREBD_FechaUltimaModificacion = $hoy;
                    $preEBD3->PREBD_EMP_ModificadoPorId = $idEmpleado;
                    $preEBD3->save();

                }
                /////////////////////////////////////FIN ELIMINAR HERMANOS E HIJO//////////////////////////////

            }
            else{

                /////////////////////////////////////ELIMINAR PADRE//////////////////////////////
                //busca bulto padre
                $bul = Bultos::find($preEBD->PREBD_BUL_BultoId);

                //buscamos bulto y su detalle
                $bulp = Bultos::find($bul->BUL_BultoPadreId);

                $consulta = "SELECT * FROM BultosDetalle WHERE BULD_BUL_BultoId = '".$bulp->BUL_BultoId."'";
                $bultoDetallePadre = \DB::select(\DB::raw($consulta));

                //actulaiza bulto detalle
                $bultoDet = BultosDetalle::find($bultoDetallePadre[0]->BULD_BultoDetalleId);
                $bultoDet->BULD_PreEmbarcado = 0;
                $bultoDet->BULD_FechaUltimaModificacion = $hoy;
                $bultoDet->BULD_EMP_ModificadoPorId = $idEmpleado;
                $bultoDet->save();

                $consulta = "SELECT * FROM PreembarqueBultoDetalle WHERE PREBD_BULD_BultoDetalleId = '".$bultoDetallePadre[0]->BULD_BultoDetalleId."'";
                $bultoDetallePadre2 = \DB::select(\DB::raw($consulta));

                //actualiza preembarquedetalle
                $preEBD2 = PreembarqueBultoDetalle::find($bultoDetallePadre2[0]->PREBD_PreembarqueBultoDetalleId);
                $preEBD2->PREBD_Eliminado = 1;
                $preEBD2->PREBD_FechaUltimaModificacion = $hoy;
                $preEBD2->PREBD_EMP_ModificadoPorId = $idEmpleado;
                $preEBD2->save();
                /////////////////////////////////////FIN ELIMINAR PADRE//////////////////////////////



                /////////////////////////////////////ELIMINAR HERMANOS E HIJO//////////////////////////////
                $consulta = "SELECT
                              PREBD_PreembarqueBultoDetalleId
                            FROM Bultos
                            INNER JOIN PreembarqueBultoDetalle ON PREBD_BUL_BultoId = BUL_BultoId
                            WHERE BUL_BultoPadreId = '".$bul->BUL_BultoPadreId."'
                            AND PREBD_Eliminado = 0";
                $bultoHijos = \DB::select(\DB::raw($consulta));

                for($x = 0; $x < count($bultoHijos); $x++){

                    $preEBD3 = PreembarqueBultoDetalle::find($bultoHijos[$x]->PREBD_PreembarqueBultoDetalleId);
                    $preEBD3->PREBD_Eliminado = 1;
                    $preEBD3->PREBD_FechaUltimaModificacion = $hoy;
                    $preEBD3->PREBD_EMP_ModificadoPorId = $idEmpleado;
                    $preEBD3->save();

                }
                /////////////////////////////////////FIN ELIMINAR HERMANOS E HIJO//////////////////////////////

            }

            \DB::commit();

            $ajaxData = array();
            $ajaxData['codigo'] = 200;

            echo json_encode($ajaxData);

        }
        catch (\Exception $e){

            \DB::rollback();

            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json; charset=UTF-8');
            die(json_encode(array("mensaje" => $e->getMessage(),
                "codigo" => $e->getCode(),
                "clase" => $e->getFile(),
                "linea" => $e->getLine())));

        }

    }

     public function exportar()
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $dao = new DAOGeneralController();
        $nombre_empresa = $dao->getEjecutaConsulta("
                                  SELECT CMA_Valor
                                  FROM ControlesMaestros
                                  WHERE CMA_Control = 'CMA_CSVP_EmpresaRazonSocial'")[0]->CMA_Valor;


        //$tipoReporte = $_POST['tipoReporte'];
        $tipoFormato = $_POST['tipoFormato'];
        $isChkMostrarLogo = $_POST['isChkMostrarLogo'];
        //$nombreReporte="";
        $isChkPaginar = $_POST['isChkPaginar'];
        $rptEmbarqueId = $_POST ['rptEmbarqueId'];
        $criterio = '';
        //$criterio2 = '';
        $reportSource = '';
        $filtro = '';
        $orderBy = '';


        if (!empty($rptEmbarqueId)) {
            $criterio .= "WHERE EMBB_EmbarqueBultoId = '$rptEmbarqueId'";
        }

        $nombreReporte = "Embarque";
        $reportSource = public_path() . "/Reportes/Inventario/EmbarqueWeb/rptEmbarqueWeb.jrxml";

        $consulta = "SELECT
ROW_NUMBER() OVER(order by (EMBB_CodigoEmbarqueBulto)) as NL,
EMBB_CodigoEmbarqueBulto,
       EMBB_FechaCreacion,
       OV_CodigoOV,
       OV_ReferenciaOC,
       CLI_RazonSocial,
       CCON_Nombre,
       PROYECTO,
       COMPLEMENTO,
       IDENTIFICADOR,
       ARTICULO,
       ART_GLN,
       BULD_Cantidad,
       EMBBD_Cantidad,
       EMBB_Chofer,
       EMBB_Licencia,
       EMBB_Celular,
       EMBB_LineaTransporte,
       EMBB_PlacasTractor,
       EMBB_PlacasCaja
            FROM EmbarquesBultos
            INNER JOIN EmbarquesBultosDetalle ON  EMBB_EmbarqueBultoId = EMBBD_EMBB_EmbarqueBultoId AND EMBBD_Eliminado = 0
            --WHERE  EMBB_CodigoEmbarqueBulto = 'EMBB00008'
            INNER JOIN(
            SELECT PREBD_PreembarqueBultoDetalleId, PREB_CodigoPreembarqueBulto, CAST(PREB_FechaCreacion AS DATE) AS PREB_FechaCreacion, COMPLEMENTO.BUL_NumeroBulto COMPLEMENTO, BULTO_PADRE.BUL_NumeroBulto PADRE, 'Complemento' AS IDENTIFICADOR
                        ,'COMPLEMENTO:' + ' ' + COMPLEMENTO.BUL_Contenido AS ARTICULO
                        ,NULL AS ART_GLN
                        ,NULL AS BULD_Cantidad
                        ,COMPLEMENTO.BUL_X
                        ,COMPLEMENTO.BUL_Y
                        ,COMPLEMENTO.BUL_Z
                        ,COMPLEMENTO.BUL_X * COMPLEMENTO.BUL_Y * COMPLEMENTO.BUL_Z AS MM
                        ,COMPLEMENTO.BUL_W
                        ,NULL AS PROYECTO
                        ,PREBD_Embarcado
                        ,NULL AS OV_CodigoOV
                        ,NULL AS OV_FechaOV
                        ,NULL AS OV_ReferenciaOC
                        ,NULL AS CLI_RazonSocial
                        ,NULL AS CCON_Nombre
                       FROM PreembarqueBulto
                        INNER JOIN PreembarqueBultoDetalle ON PREBD_PREB_PreembarqueBultoId = PREB_PreembarqueBultoId AND PREBD_Eliminado = 0 AND PREBD_Embarcado = 1
                        INNER JOIN Bultos COMPLEMENTO ON PREBD_BUL_BultoId = COMPLEMENTO.BUL_BultoId
                        INNER JOIN Bultos BULTO_PADRE ON COMPLEMENTO.BUL_BultoPadreId = BULTO_PADRE.BUL_BultoId
                        WHERE PREB_Eliminado = 0
                        AND PREBD_BULD_BultoDetalleId IS NULL
                        union all
                        SELECT PREBD_PreembarqueBultoDetalleId,PREB_CodigoPreembarqueBulto, CAST(PREB_FechaCreacion AS DATE) AS PREB_FechaCreacion, BUL_NumeroBulto, BUL_NumeroBulto, 'Bulto' IDENTIFICADOR
                        ,ART_CodigoArticulo + ' - ' + ART_Nombre AS ARTICULO
                        ,ART_GLN
                        ,BULD_Cantidad
                        ,BUL_X
                        ,BUL_Y
                        ,BUL_Z
                        ,BUL_X * BUL_Y * BUL_Z AS MM
                        ,BUL_W
                        ,PRY_CodigoEvento + ' - ' + PRY_NombreProyecto AS PROYECTO
                        ,PREBD_Embarcado
                        ,OV_CodigoOV
                        ,CAST(OV_FechaOV AS DATE) AS OV_FechaOV
                        ,OV_ReferenciaOC
                        ,CLI_RazonSocial
                        ,CCON_Nombre
                        FROM PreembarqueBulto
                        INNER JOIN PreembarqueBultoDetalle ON PREBD_PREB_PreembarqueBultoId = PREB_PreembarqueBultoId AND PREBD_Eliminado = 0 AND PREBD_Embarcado = 1
                        INNER JOIN BultosDetalle ON BULD_BultoDetalleId = PREBD_BULD_BultoDetalleId
                        INNER JOIN Bultos ON BUL_BultoId = BULD_BUL_BultoId
                        LEFT JOIN Articulos ON ART_ArticuloId = BULD_ART_ArticuloId
                        INNER JOIN ControlesMaestrosUM ON CMUM_UnidadMedidaId = ART_CMUM_UMInventarioId
                        INNER JOIN OrdenesTrabajo ON OT_OrdenTrabajoId = BULD_OT_OrdenTrabajoId
                        INNER JOIN OrdenesTrabajoReferencia ON OTRE_OT_OrdenTrabajoId = OT_OrdenTrabajoId
                        INNER JOIN OrdenesVenta ON OV_OrdenVentaId = OTRE_OV_OrdenVentaId
                        INNER JOIN Clientes ON OV_CLI_ClienteId = CLI_ClienteId
                        LEFT JOIN ClientesContactos ON OV_CCON_ContactoId = CCON_ContactoId
                        INNER JOIN Proyectos ON PRY_ProyectoId = OV_PRO_ProyectoId
                        WHERE PREB_Eliminado = 0
                        AND PREBD_BULD_BultoDetalleId IS NOT NULL
            ) AS UNIRCONeMBARQUES ON EMBBD_PREBD_PreembarqueBultoDetalleId = PREBD_PreembarqueBultoDetalleId
            $criterio
			GROUP BY 
EMBB_CodigoEmbarqueBulto,
       EMBB_FechaCreacion,
       OV_CodigoOV,
       OV_ReferenciaOC,
       CLI_RazonSocial,
       CCON_Nombre,
       PROYECTO,
       COMPLEMENTO,
       IDENTIFICADOR,
       ARTICULO,
       ART_GLN,
       BULD_Cantidad,
       EMBBD_Cantidad,
       EMBB_Chofer,
       EMBB_Licencia,
       EMBB_Celular,
       EMBB_LineaTransporte,
       EMBB_PlacasTractor,
       EMBB_PlacasCaja,
	   PREB_CodigoPreembarqueBulto,
	   PADRE
            --WHERE EMBB_CodigoEmbarqueBulto = 'EMBB00011'
            ORDER BY PREB_CodigoPreembarqueBulto, PADRE,IDENTIFICADOR";
        //dd($consulta);
        //dd($isChkCedi);


        //dd($consulta);
        //dd($isChkFaltantes);
        //$rangofecha = 'De ' . self::obtenerFechaEnLetraDesde($fechaDesde) . ' A ' . self::obtenerFechaEnLetra($fecha);

        $Jasperphp = new \PHPJasper();
        $conexion = $Jasperphp->conexionJDBC();
        //dd($conexion);
        $parametros = array("LOGO_EMPRESA" => str_replace('\\', '/', public_path()) . "/img/logocorona.jpg",
            "EMPRESA" => $nombre_empresa,
            "NOMBRE_REPORTE" => $nombreReporte,
            "LEYENDA" => "App",
            "FECHA" => "",
            "FILTRO" => $filtro,
            "LOGO_MULIIX" => str_replace('\\', '/', public_path()) . "/img/logo.png",
            "ENCABEZADO" => str_replace('\\', '/', public_path()) . "/Reportes/Plantillas/",
            "MOSTRAR_LOGO" => $isChkMostrarLogo
        );
        //dd($reportSource);
            $Jasperphp->formatoPdf($reportSource, $consulta, $parametros, 'rpt-embarque', $conexion, true);

        $conexion->close();
    }

    public function asignarTurnoPreembarque(){

        ini_set('memory_limit', '-1');
        set_time_limit(0);

        \DB::beginTransaction();

        try{

            //date_default_timezone_set('America/Mexico_City');
            $hoy=date('d-m-Y H:i:s');
            $idEmpleado = DataBaseSession::getEmpleadoId();
            $datostablePreEmbarque = isset($_POST['datostablePreEmbarque']) ? json_decode($_POST['datostablePreEmbarque'], true) : array();
            $turno = self::consultaCodigoTurno();
            $cuentaTabla = count($datostablePreEmbarque);
            for($x = 0; $x < $cuentaTabla; $x ++){

                if($datostablePreEmbarque[$x]['CHECK_BOX'] == 1){

                    $embb = PreembarqueBulto::find($datostablePreEmbarque[$x]['PREB_PreembarqueBultoId']);
                    if($embb->PREBD_Turno != null){
                        throw new \Exception("No se puede guardar porque el preembarque ".$embb->PREB_CodigoPreembarqueBulto." ya tiene asignado turno.", 140);
                    }
                    $embb->PREBD_Turno = $turno;
                    $embb->save();

                }

            }

            \DB::commit();

            $ajaxData = array();
            $ajaxData['codigo'] = 200;

            echo json_encode($ajaxData);

        }
        catch (\Exception $e){

            \DB::rollback();

            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json; charset=UTF-8');
            die(json_encode(array("mensaje" => $e->getMessage(),
                "codigo" => $e->getCode(),
                "clase" => $e->getFile(),
                "linea" => $e->getLine())));

        }

    }

    public function quitarTurnoPreembarque(){

        ini_set('memory_limit', '-1');
        set_time_limit(0);

        \DB::beginTransaction();

        try{

            //date_default_timezone_set('America/Mexico_City');
            $hoy=date('d-m-Y H:i:s');
            $idEmpleado = DataBaseSession::getEmpleadoId();
            $datostablePreEmbarque = isset($_POST['datostablePreEmbarque']) ? json_decode($_POST['datostablePreEmbarque'], true) : array();

            $cuentaTabla = count($datostablePreEmbarque);
            for($x = 0; $x < $cuentaTabla; $x ++){

                if($datostablePreEmbarque[$x]['CHECK_BOX'] == 1){

                    $embb = PreembarqueBulto::find($datostablePreEmbarque[$x]['PREB_PreembarqueBultoId']);
                    $embb->PREBD_Turno = null;
                    $embb->save();

                }

            }

            \DB::commit();

            $ajaxData = array();
            $ajaxData['codigo'] = 200;

            echo json_encode($ajaxData);

        }
        catch (\Exception $e){

            \DB::rollback();

            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json; charset=UTF-8');
            die(json_encode(array("mensaje" => $e->getMessage(),
                "codigo" => $e->getCode(),
                "clase" => $e->getFile(),
                "linea" => $e->getLine())));

        }

    }

    public function consultaCodigoTurno(){

        //////////SACAR AUTONUMERICO///////////////////////////
        $autonumerico_dao = new AutonumericoController();
        $clienteId = null;
        $empleadoId = null;
        $codigo_OC = null;

        if($autonumerico_dao->isAutonumericoActivoPorReferenciaId('CM_INV_SiguienteTurnoPreEmbarqueBulto', null)){
            $autonumerico_id = self::establecerAutonumerico2($clienteId, $empleadoId);
            $codigo_OC = $autonumerico_dao->getSiguienteAutonumericoPorId($autonumerico_id);
        }

        return $codigo_OC;

    }

    public function establecerAutonumerico2($clienteId, $empleadoId)
    {
        try {
            $autonumerico_dao = new AutonumericoController();
            $autonumericoFicha = $autonumerico_dao->getAutonumerico($clienteId, "CM_INV_SiguienteTurnoPreEmbarqueBulto", $empleadoId);
            return $autonumericoFicha->AUT_AutonumericoId;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public function generaPDF ($referenciaId) {

        try {
            require_once '../public/plugins/tcpdf/tcpdf.php';
            $emb = self::getDatosPdf($referenciaId);
            $pdf_archivo = public_path()."/archivosOV/".$emb[0]->EMBB_CodigoEmbarqueBulto;

            $pdf = new EncabezadoPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $pdf->setCodigo($emb[0]->EMBB_CodigoEmbarqueBulto);
            $pdf->setFecha($emb[0]->EMBB_FechaCreacion);
            $pdf->setTipoDocumento('EMBARQUE WEB');
            $pdf->SetTopMargin(38);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->AddPage('P', 'A4');

            // DatosCliente
            $pdf->SetFont('times', '', 8, '', 'false');
            $tbl =
                '<table cellpadding="1" cellspacing="0" border="0">
                    <hr size="2"/>
                    <tr bgcolor="#d4d4d4">
                    <td width="267" style="font-size: 5px"><b>DATOS DEL CLIENTE</b></td>
                    <td width="180" style="font-size: 5px"><b>DATOS EMBARQUE</b></td>
                    <td width="86" style="font-size: 5px"><b></b></td>
                    </tr>
                    <tr>
                    <td width="267" style="font-style: oblique; font-size: 9px">CLIENTE: <b>'.$emb[0]->CLI_RazonSocial.'</b></td>
                    <td width="180" style="font-style: oblique; font-size: 9px">OV: <b>'.$emb[0]->OV_CodigoOV.'</b></td>
                    </tr>
                    <tr>
                    <td width="267">CONTACTO: <b>'.$emb[0]->CCON_Nombre.'</b></td>
                    <td width="266">ORDEN DE COMPRA: <b>'.$emb[0]->OV_ReferenciaOC.'</b></td>
                    </tr>
                    <tr>
                    <td width="267">PROYECTO: <b>'.$emb[0]->PROYECTO.'</b></td>
                    <td width="266"></td>
                    </tr>
                    </table>';

            $pdf->writeHTML($tbl, true, false, true, false, '');

            // Detalle de la factura (Artículos) y totales.
            $pdf->SetFont('times', '', 7, '', 'false');
            $tbl = '<table cellpadding="1" cellspacing="1" border="0">
                    <hr size="2"/>
                    <tr>
                    <td width="15" align="left"><b>#N</b></td>
                    <td width="40" align="left"><b>Bulto</b></td>
                    <td width="400" align="left"><b>Codigo Articulo / Descripcion</b></td>
                    <td width="35" align="right"><b>Piezas</b></td>
                    <td width="35" align="left"><b>Insumo</b></td>
                    </tr>
                    <hr size="2"/>
                    </table>';
            $tbl .= '<table cellpadding="1" cellspacing="1" border="0">';

            for($i = 0; $i < count($emb); $i++) {
                //if(($isMontosApp && isset($montosApp['Detalles'][$datos[$i]->Factura])) || !$isMontosApp) {
                $tbl .= '<tr>
                    <td width="15" align="left">' . $this->limpiar($emb[$i]->NL) . '</td>
                    <td width="40" align="left">' . $this->limpiar($emb[$i]->COMPLEMENTO) . '</td>
                    <td width="400" align="left">' . $this->limpiar($emb[$i]->ARTICULO) . '</td>
                    <td width="35" align="right">' . number_format($emb[$i]->EMBBD_Cantidad, 2, '.', ',') . '</td>
                    <td width="35" align="left">' . $this->limpiar($emb[$i]->ART_GLN) . '</td>
                    </tr>';
                //}
            }
            $pdf->writeHTML($tbl, 1, false, false, false, '');

            // Datos complemento recepción de pagos
            $pdf->SetFont('times', '', 8, '', 'false');
            $tbl =
                '<table cellpadding="1" cellspacing="0" border="0">
                    <hr size="2"/>
                    <tr bgcolor="#d4d4d4">
                    <td style="font-size: 6px" colspan="2"> <b></b></td>
                    </tr>

                    <tr>
                    <td width="267" style="font-size: 6px">Nombre y firma de recibido: <b></b></td>
                    </tr>

                    <tr>
                    <td width="267" style="font-size: 6px">Nombre de chofer: <b>'.$emb[0]->EMBB_Chofer.'</b></td>
                    </tr>

                    <tr>
                    <td width="267" style="font-size: 6px">Numero de licencia: <b>'.$emb[0]->EMBB_Licencia.'</b></td>
                    </tr>

                    <tr>
                    <td width="267" style="font-size: 6px">Celular No: <b>'.$emb[0]->EMBB_Celular.'</b></td>
                    </tr>

                    <tr>
                    <td width="267" style="font-size: 6px" colspan="2">Linea Transporte: <b>'.$emb[0]->EMBB_LineaTransporte.'</b></td>
                    </tr>

                    <tr>
                    <td width="267" style="font-size: 6px" colspan="2">Placas Tractor: <b>'.$emb[0]->EMBB_PlacasTractor.'</b></td>
                    </tr>

                    <tr>
                    <td width="267" style="font-size: 6px" colspan="2">Placas Caja: <b>'.$emb[0]->EMBB_PlacasCaja.'</b></td>
                    </tr>

                </table>
                    ';

            $pdf->writeHTML($tbl, true, false, true, false, '');

            $pdf->Output($pdf_archivo.'.pdf', 'F');

        }catch (\FileException $e) {
            file_put_contents("timbrar.txt", 'ERROR try catch genera pdf '.date("Y-m-d | h:i:sa")." -->  ".$e->getMessage()."\r\n",FILE_APPEND);
            throw $e;
        }

    }

    public function getDatosPdf ($referenciaId){
        $datos = \DB::select( \DB::raw(
            "SELECT
ROW_NUMBER() OVER(order by (EMBB_CodigoEmbarqueBulto)) as NL,
EMBB_CodigoEmbarqueBulto,
       CAST(EMBB_FechaCreacion AS DATE) AS EMBB_FechaCreacion,
       OV_CodigoOV,
       OV_ReferenciaOC,
       CLI_RazonSocial,
       CCON_Nombre,
       PROYECTO,
       COMPLEMENTO,
       IDENTIFICADOR,
       ARTICULO,
       ART_GLN,
       BULD_Cantidad,
       EMBBD_Cantidad,
       EMBB_Chofer,
       EMBB_Licencia,
       EMBB_Celular,
       EMBB_LineaTransporte,
       EMBB_PlacasTractor,
       EMBB_PlacasCaja
            FROM EmbarquesBultos
            INNER JOIN EmbarquesBultosDetalle ON  EMBB_EmbarqueBultoId = EMBBD_EMBB_EmbarqueBultoId AND EMBBD_Eliminado = 0
            --WHERE  EMBB_CodigoEmbarqueBulto = 'EMBB00008'
            INNER JOIN(
            SELECT PREBD_PreembarqueBultoDetalleId, PREB_CodigoPreembarqueBulto, CAST(PREB_FechaCreacion AS DATE) AS PREB_FechaCreacion, COMPLEMENTO.BUL_NumeroBulto COMPLEMENTO, BULTO_PADRE.BUL_NumeroBulto PADRE, 'Complemento' AS IDENTIFICADOR
                        ,'COMPLEMENTO:' + ' ' + COMPLEMENTO.BUL_Contenido AS ARTICULO
                        ,NULL AS ART_GLN
                        ,NULL AS BULD_Cantidad
                        ,COMPLEMENTO.BUL_X
                        ,COMPLEMENTO.BUL_Y
                        ,COMPLEMENTO.BUL_Z
                        ,COMPLEMENTO.BUL_X * COMPLEMENTO.BUL_Y * COMPLEMENTO.BUL_Z AS MM
                        ,COMPLEMENTO.BUL_W
                        ,NULL AS PROYECTO
                        ,PREBD_Embarcado
                        ,NULL AS OV_CodigoOV
                        ,NULL AS OV_FechaOV
                        ,NULL AS OV_ReferenciaOC
                        ,NULL AS CLI_RazonSocial
                        ,NULL AS CCON_Nombre
                       FROM PreembarqueBulto
                        INNER JOIN PreembarqueBultoDetalle ON PREBD_PREB_PreembarqueBultoId = PREB_PreembarqueBultoId AND PREBD_Eliminado = 0 AND PREBD_Embarcado = 1
                        INNER JOIN Bultos COMPLEMENTO ON PREBD_BUL_BultoId = COMPLEMENTO.BUL_BultoId
                        INNER JOIN Bultos BULTO_PADRE ON COMPLEMENTO.BUL_BultoPadreId = BULTO_PADRE.BUL_BultoId
                        WHERE PREB_Eliminado = 0
                        AND PREBD_BULD_BultoDetalleId IS NULL
                        union all
                        SELECT PREBD_PreembarqueBultoDetalleId,PREB_CodigoPreembarqueBulto, CAST(PREB_FechaCreacion AS DATE) AS PREB_FechaCreacion, BUL_NumeroBulto, BUL_NumeroBulto, 'Bulto' IDENTIFICADOR
                        ,ART_CodigoArticulo + ' - ' + ART_Nombre AS ARTICULO
                        ,ART_GLN
                        ,BULD_Cantidad
                        ,BUL_X
                        ,BUL_Y
                        ,BUL_Z
                        ,BUL_X * BUL_Y * BUL_Z AS MM
                        ,BUL_W
                        ,PRY_CodigoEvento + ' - ' + PRY_NombreProyecto AS PROYECTO
                        ,PREBD_Embarcado
                        ,OV_CodigoOV
                        ,CAST(OV_FechaOV AS DATE) AS OV_FechaOV
                        ,OV_ReferenciaOC
                        ,CLI_RazonSocial
                        ,CCON_Nombre
                        FROM PreembarqueBulto
                        INNER JOIN PreembarqueBultoDetalle ON PREBD_PREB_PreembarqueBultoId = PREB_PreembarqueBultoId AND PREBD_Eliminado = 0 AND PREBD_Embarcado = 1
                        INNER JOIN BultosDetalle ON BULD_BultoDetalleId = PREBD_BULD_BultoDetalleId
                        INNER JOIN Bultos ON BUL_BultoId = BULD_BUL_BultoId
                        LEFT JOIN Articulos ON ART_ArticuloId = BULD_ART_ArticuloId
                        INNER JOIN ControlesMaestrosUM ON CMUM_UnidadMedidaId = ART_CMUM_UMInventarioId
                        INNER JOIN OrdenesTrabajo ON OT_OrdenTrabajoId = BULD_OT_OrdenTrabajoId
                        INNER JOIN OrdenesTrabajoReferencia ON OTRE_OT_OrdenTrabajoId = OT_OrdenTrabajoId
                        INNER JOIN OrdenesVenta ON OV_OrdenVentaId = OTRE_OV_OrdenVentaId
                        INNER JOIN Clientes ON OV_CLI_ClienteId = CLI_ClienteId
                        LEFT JOIN ClientesContactos ON OV_CCON_ContactoId = CCON_ContactoId
                        INNER JOIN Proyectos ON PRY_ProyectoId = OV_PRO_ProyectoId
                        WHERE PREB_Eliminado = 0
                        AND PREBD_BULD_BultoDetalleId IS NOT NULL
            ) AS UNIRCONeMBARQUES ON EMBBD_PREBD_PreembarqueBultoDetalleId = PREBD_PreembarqueBultoDetalleId
            WHERE EMBB_EmbarqueBultoId = '".$referenciaId."'
			GROUP BY 
            EMBB_CodigoEmbarqueBulto,
                   EMBB_FechaCreacion,
                   OV_CodigoOV,
                   OV_ReferenciaOC,
                   CLI_RazonSocial,
                   CCON_Nombre,
                   PROYECTO,
                   COMPLEMENTO,
                   IDENTIFICADOR,
                   ARTICULO,
                   ART_GLN,
                   BULD_Cantidad,
                   EMBBD_Cantidad,
                   EMBB_Chofer,
                   EMBB_Licencia,
                   EMBB_Celular,
                   EMBB_LineaTransporte,
                   EMBB_PlacasTractor,
                   EMBB_PlacasCaja,
                   PREB_CodigoPreembarqueBulto,
                   PADRE
            --WHERE EMBB_CodigoEmbarqueBulto = 'EMBB00199'
            ORDER BY PREB_CodigoPreembarqueBulto, PADRE,IDENTIFICADOR"
        ));

        return $datos;
    }

    function limpiar($String){
        $String = str_replace(array('á','à','â','ã','ª','ä'),"a",$String);
        $String = str_replace(array('Á','À','Â','Ã','Ä'),"A",$String);
        $String = str_replace(array('Í','Ì','Î','Ï'),"I",$String);
        $String = str_replace(array('í','ì','î','ï'),"i",$String);
        $String = str_replace(array('é','è','ê','ë'),"e",$String);
        $String = str_replace(array('É','È','Ê','Ë'),"E",$String);
        $String = str_replace(array('ó','ò','ô','õ','ö','º'),"o",$String);
        $String = str_replace(array('Ó','Ò','Ô','Õ','Ö'),"O",$String);
        $String = str_replace(array('ú','ù','û','ü'),"u",$String);
        $String = str_replace(array('Ú','Ù','Û','Ü'),"U",$String);
        $String = str_replace(array('[','^','´','`','¨','~',']','(',')'),"",$String);
        $String = str_replace("ç","c",$String);
        $String = str_replace("Ç","C",$String);
        $String = str_replace("ñ","n",$String);
        $String = str_replace("Ñ","N",$String);
        $String = str_replace("Ý","Y",$String);
        $String = str_replace("ý","y",$String);

        $String = str_replace("&aacute;","a",$String);
        $String = str_replace("&Aacute;","A",$String);
        $String = str_replace("&eacute;","e",$String);
        $String = str_replace("&Eacute;","E",$String);
        $String = str_replace("&iacute;","i",$String);
        $String = str_replace("&Iacute;","I",$String);
        $String = str_replace("&oacute;","o",$String);
        $String = str_replace("&Oacute;","O",$String);
        $String = str_replace("&uacute;","u",$String);
        $String = str_replace("&Uacute;","U",$String);
        return $String;
    }

}
