<?php namespace App\Http\Controllers\Inventario\ProgramaRutaReparto;
/**
 * Created by PhpStorm.
 * User: Juan
 * Date: 26/04/2016
 * Time: 11:48 AM
 * version: 1.0
 */

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\Sistema\AutonumericoController;
use App\Http\Controllers\Sistema\DAOGeneralController;
use App\Http\Controllers\Sistema\DataBaseSession;
use App\Models\Articulos;
use App\Models\ArticulosTipos;
use App\Models\Ciudades;
use App\Models\ControlesMaestros;
use App\Models\ControlesMaestrosDescuentos;
use App\Models\OrdenesVenta;
use App\Models\Embarques;
use App\Models\OrdenesVentaDetalle;
use App\Models\Paises;
use App\Models\ProgramaRutasReparto;
use App\Models\ProgramaRutasRepartoDestinos;
use App\Models\ProgramaRutasRepartoArticulos;
use App\Models\TraspasosDetalle;
use App\Models\PropiedadesTimbrado;
use App\Http\Controllers\CFDI\Cfdiv33;

class ProgramaRutaRepartoController extends Controller {

    private $dao;

    function __construct(){
        $this->dao = new DAOGeneralController();
    }

    public function index(){
        $cedisOrigen = array();
        $cedisDestino = array();
        $vehiculos = array();
        $operadores = array();
        $um = array();

        $version = $this->dao->nuevoId();

        return view('Inventario.ProgramaRutaReparto.ProgramaRutaReparto', compact('version', 'cedisOrigen', 'cedisDestino', 'vehiculos', 'operadores', 'um'));
    }

    public function getCadenaId($ids) {
        return "'".implode("','", $ids)."'";
    }


    public function combobox() {
        try {

            $consultaCedis = "SELECT DEP_DeptoId as ID, DEP_Codigo + ' - ' + DEP_Nombre AS VALOR, DEPD_Calle + ' #'+DEPD_NoExterior + ' COL. '+ DEPD_Colonia AS DIRECCION
                , CIU_Nombre + ', '+EST_Nombre+ ', '+PAI_Nombre AS LOCALIZACION
                FROM Departamentos
                INNER JOIN DepartamentosDatos ON DEP_DeptoId = DEPD_DEP_DeptoId
                INNER JOIN Ciudades ON CIU_CiudadId = DEPD_CIU_CiudadId
                INNER JOIN Estados ON EST_EstadoId = DEPD_EST_EstadoId
                INNER JOIN Paises ON PAI_PaisId = DEPD_PAI_PaisId
                where dep_cmm_tipoDeptoID = '5845CCF9-23B9-41C7-B49F-A8495A7C4D08'
                and DEP_Eliminado = 0
                GROUP BY DEP_DeptoId, DEP_Codigo, DEP_Nombre, DEPD_Calle ,DEPD_NoExterior, DEPD_Colonia 
                , CIU_Nombre, EST_Nombre, PAI_Nombre
                order by DEP_Nombre
            ";

            $cedisOrigen = $this->dao->getEjecutaConsulta($consultaCedis);
            $cedisDestino = $cedisOrigen;

            $consultaVehiculos = "SELECT TUN_TransporteUnidadId AS ID, '('+TUN_CodigoUnidad + ') ' + MARCA.CMM_Valor + ' ' + TUL_Linea + ' '+ TUN_Modelo  + ' - '+ TUN_Placas AS VEHICULO
                , TUN_Placas AS PLACAS
                FROM TransportesUnidades
                INNER JOIN TransportesUnidadesLineas ON TUN_TUL_TransporteUnidadLineaId = TUL_TransporteUnidadLineaId
                INNER JOIN ControlesMaestrosMultiples MARCA ON MARCA.CMM_ControlId = TUL_CMM_MarcaTransporteId
                INNER JOIN ControlesMaestrosMultiples TIPO ON TIPO.CMM_ControlId = TUN_CMM_TipoPermisoId
                INNER JOIN CartaPorteConfigAutotransporte ON CPCA_ConfiguracionId = TUN_CPCA_ConfiguracionId
                WHERE TUN_Eliminado = 0 AND TUN_Poliza IS NOT NULL AND TUN_Aseguradora IS NOT NULL AND TUN_Modelo IS NOT NULL AND TUN_Placas IS NOT NULL 
                ORDER BY TUN_CodigoUnidad
            ";

            $vehiculos = $this->dao->getEjecutaConsulta($consultaVehiculos);

            $consultaEmpleados = "SELECT EMP_EmpleadoId AS ID, '('+ EMP_CodigoEmpleado+') '+ EMP_Nombre + ' ' + EMP_PrimerApellido + ' ' + EMP_SegundoApellido AS EMPLEADO
                FROM Empleados
                INNER JOIN Puestos ON PUE_PuestoId = EMP_PUE_PuestoId
                WHERE EMP_Activo = 1 AND EMP_Eliminado = 0 
                AND (PUE_NombrePuesto = 'Operador' OR PUE_NombrePuesto = 'Chofer')
                AND EMP_RFC IS NOT NULL AND EMP_RFC <> ''
                AND EMP_NumeroLicencia IS NOT NULL AND EMP_NumeroLicencia <> ''
                ORDER BY EMP_Nombre
            ";

            $operadores = $this->dao->getEjecutaConsulta($consultaEmpleados);    

            $consultaUM = "SELECT CMUM_UnidadMedidaId AS ID, CMUM_Nombre AS VALOR, CMM_DefinidoPorUsuario1 AS CLAVE
                FROM ControlesMaestrosUM
                INNER JOIN ControlesMaestrosMultiples ON CMM_ControlId = CMUM_CMM_ClaveUnidadId
                ORDER BY CMUM_Nombre
            ";

            $um = $this->dao->getEjecutaConsulta($consultaUM);    

            return compact('cedisOrigen', 'cedisDestino', 'vehiculos', 'operadores', 'um');
        } catch (\Exception $e) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json; charset=UTF-8');
            die(json_encode(array("mensaje" => $e->getMessage(),
                "codigo" => $e->getCode(),
                "clase" => $e->getFile(),
                "linea" => $e->getLine())));
        }
    }

    public function traspasos() {
        try {
            $consulta = "SELECT TRS_TraspasoSolicitudId AS DT_RowId, TRS_CodigoSolicitud AS CODIGO
                    , CAST(TRS_FechaSolicitud AS DATE) AS FECHA, ORIGEN_ALM.ALM_Nombre AS ORIGEN, DESTINO_ALM.ALM_Nombre AS DESTINO, 0 AS CHECKBOX
                    , 0 AS SELECCIONADO
                    FROM TraspasosSolicitudes
                    INNER JOIN Localidades ORIGEN ON ORIGEN.LOC_LocalidadId = TRS_LOC_LocalidadOrigenId
                    INNER JOIN Almacenes ORIGEN_ALM ON ORIGEN_ALM.ALM_AlmacenId = LOC_ALM_AlmacenId
                    INNER JOIN Localidades DESTINO ON DESTINO.LOC_LocalidadId = TRS_LOC_LocalidadDestinoId
                    INNER JOIN Almacenes DESTINO_ALM ON DESTINO_ALM.ALM_AlmacenId = DESTINO.LOC_ALM_AlmacenId
                    WHERE TRS_Eliminado = 0
                    AND TRS_CMM_EstatusSolicitudId IN( '58B06E0A-7D6F-482A-AB9D-287FA7872E7E', '9367F403-C6E8-4952-8276-F71B8E92B641')
                    AND ORIGEN.LOC_General = 1
                    --AND TRS_FechaSolicitud > (SELECT  DATEADD(DAY, -60, GETDATE()))
                    ORDER BY TRS_FechaCreacion DESC
            ";

            
            $consulta = "SELECT * FROM
                (
                    SELECT TRS_TraspasoSolicitudId AS DT_RowId, TRS_CodigoSolicitud AS CODIGO
                    , CAST(TRS_FechaSolicitud AS DATE) AS FECHA, ORIGEN_ALM.ALM_Nombre AS ORIGEN, DESTINO_ALM.ALM_Nombre AS DESTINO, 0 AS CHECKBOX
                    , 0 AS SELECCIONADO, SUM(TRAD_CantidadATraspasar) AS SUMA
                    FROM TraspasosSolicitudes
                    INNER JOIN TraspasosSolicitudesDetalle ON TRSD_TRS_TraspasoSolicitudId = TRS_TraspasoSolicitudId    
                    INNER JOIN Localidades ORIGEN ON ORIGEN.LOC_LocalidadId = TRS_LOC_LocalidadOrigenId
                    INNER JOIN Almacenes ORIGEN_ALM ON ORIGEN_ALM.ALM_AlmacenId = LOC_ALM_AlmacenId
                    INNER JOIN Localidades DESTINO ON DESTINO.LOC_LocalidadId = TRS_LOC_LocalidadDestinoId
                    INNER JOIN Almacenes DESTINO_ALM ON DESTINO_ALM.ALM_AlmacenId = DESTINO.LOC_ALM_AlmacenId
                    INNER JOIN TraspasosDetalle ON TRAD_TRSD_DetalleId = TRSD_DetalleId AND TRAD_PRP_ProgramaRutaId IS NULL
                    LEFT JOIN TraspasosRecibos ON TRAR_TRAD_TraspasoDetalleId = TRAD_TraspasoDetalleId
                    WHERE TRAR_TraspasoReciboId IS NULL
                    AND TRS_Eliminado = 0
                    AND TRSD_Eliminado = 0 AND TRAD_Eliminado = 0
                    AND TRS_CMM_EstatusSolicitudId IN( '58B06E0A-7D6F-482A-AB9D-287FA7872E7E', '9367F403-C6E8-4952-8276-F71B8E92B641')
                    AND ORIGEN.LOC_General = 1 AND DESTINO.LOC_Nombre = 'Matriz General'
                    GROUP BY TRS_TraspasoSolicitudId,TRS_CodigoSolicitud,TRS_FechaSolicitud, ORIGEN_ALM.ALM_Nombre,DESTINO_ALM.ALM_Nombre
                ) AS QUERY
                WHERE SUMA > 0	
                ORDER BY FECHA DESC
            ";

            $resultSet = $this->dao->getDataTable($consulta);

            return $resultSet;
        } catch (\Exception $e) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json; charset=UTF-8');
            die(json_encode(array("mensaje" => $e->getMessage(),
                "codigo" => $e->getCode(),
                "clase" => $e->getFile(),
                "linea" => $e->getLine())));
        }
    }

    public function programas() {
        try {

            $fechaInicio = Request::input('fechaInicio');
            $fechaFinal = Request::input('fechaFinal');

            $criterio = " WHERE PRP_FechaCreacion BETWEEN '$fechaInicio 00:00:00' AND '$fechaFinal 23:59:59'";

            $consulta = "SELECT PRP_ProgramaRutaId AS DT_RowId, PRP_Codigo AS CODIGO, PRP_FechaCreacion AS FECHA, DEP_Nombre AS ORIGEN
                    --, dbo.getCedisDestinoPorProgramaId(PRP_ProgramaRutaId) AS DESTINO,
                    , '' AS DESTINO,
                    '('+TUN_CodigoUnidad + ') ' + CMM_Valor + ' ' + TUL_Linea + ' '+ TUN_Modelo AS VEHICULO, 
                    '('+ EMP_CodigoEmpleado+') '+ EMP_Nombre + ' ' + EMP_PrimerApellido + ' ' + EMP_SegundoApellido AS OPERADOR
                    , ISNULL(PRP_Timbrado,0) AS TIMBRADO
                    , CASE WHEN ISNULL(PRP_Eliminado,0) = 1 THEN 'ELIMINADO' ELSE 'ACTIVO' END AS ESTADO
                    , RUTA_XML = (
                        SELECT SUBSTRING(PRT_Valor, 2, LEN(PRT_Valor)) AS RUTA
                        FROM PropiedadesTimbrado
                        WHERE PRT_Control = 'LOCATION_XML' )
                    , RFC = (SELECT CMA_Valor FROM ControlesMaestros WHERE CMA_Control = 'CMA_CSVP_RFC')   
                    , PRP_CartaPorte AS CARTA_PORTE         
                    , PRP_Proceso AS PROCESO     
                    FROM ProgramaRutasReparto
                    LEFT JOIN Departamentos ON DEP_DeptoId = PRP_DEP_OrigenCediId
                    LEFT JOIN TransportesUnidades ON TUN_TransporteUnidadId = PRP_TUN_TransporteUnidadId
                    LEFT JOIN TransportesUnidadesLineas ON TUL_TransporteUnidadLineaId = TUN_TUL_TransporteUnidadLineaId
                    LEFT JOIN ControlesMaestrosMultiples ON CMM_ControlId = TUL_CMM_MarcaTransporteId
                    LEFT JOIN Empleados ON EMP_EmpleadoId = PRP_EMP_OperadorId
                    $criterio
                    ORDER BY CODIGO
            ";

            $resultSet = $this->dao->getDataTable($consulta);

            return $resultSet;
        } catch (\Exception $e) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json; charset=UTF-8');
            die(json_encode(array("mensaje" => $e->getMessage(),
                "codigo" => $e->getCode(),
                "clase" => $e->getFile(),
                "linea" => $e->getLine())));
        }
    }

    public function articulos(){
        try {
            ini_set('memory_limit', '-1');
            set_time_limit(0);

            $tablaTraspasos = json_decode(Request::input('tablaTraspasos'), true);
            $tablaArticulos = json_decode(Request::input('tablaArticulos'), true);

            $idTraspasos = $this->getIdsTraspasos($tablaTraspasos);

            $consulta = "SELECT *, CAST(CANTIDAD * factorKilo AS DECIMAL(28,2)) AS PESO_KG, 0 AS AGREGADO
                        FROM
                        (	
                            SELECT ART_ArticuloId AS DT_RowId
                            , ART_CodigoArticulo AS CODIGO, ART_Nombre AS NOMBRE, CMUM_Nombre AS UM, UM_CLAVE.CMM_DefinidoPorUsuario1 AS UM_CLAVE
                            , CMUM_UnidadMedidaId AS UM_ID, CAST(SUM(TRAD_CantidadATraspasar)AS DECIMAL(28,2)) AS CANTIDAD
                            , CLAVE.CMM_DefinidoPorUsuario1 AS CLAVE, ART_CMM_ClaveProductoId AS CLAVE_ID
                            , factorKilo = ISNULL((
                                            SELECT AFC_FactorConversion
                                            FROM ArticulosFactoresConversion
                                            WHERE AFC_CMUM_UnidadMedidaId = '621D5394-CD57-4EBD-8EA9-C81C2124C6DA' AND AFC_ART_ArticuloId = ART_ArticuloId
                                            ), 0.00)
                            FROM TraspasosSolicitudesDetalle
                            INNER JOIN TraspasosDetalle ON TRAD_TRSD_DetalleId = TRSD_DetalleId
                            INNER JOIN Articulos ON ART_ArticuloId = TRSD_ART_ArticuloId
                            INNER JOIN ControlesMaestrosMultiples CLAVE ON CLAVE.CMM_ControlId = ART_CMM_ClaveProductoId
                            INNER JOIN ControlesMaestrosUM ON CMUM_UnidadMedidaId = ART_CMUM_UMInventarioId
                            INNER JOIN ControlesMaestrosMultiples UM_CLAVE ON UM_CLAVE.CMM_ControlId = CMUM_CMM_ClaveUnidadId
                            LEFT JOIN TraspasosRecibos ON TRAR_TRAD_TraspasoDetalleId = TRAD_TraspasoDetalleId
                            WHERE TRSD_TRS_TraspasoSolicitudId IN ($idTraspasos)
                            AND TRAR_TraspasoReciboId IS NULL
                            GROUP BY ART_ArticuloId,ART_CodigoArticulo,ART_Nombre, ART_CMM_ClaveProductoId,CMUM_Nombre,CLAVE.CMM_DefinidoPorUsuario1,UM_CLAVE.CMM_DefinidoPorUsuario1,CMUM_UnidadMedidaId
                        ) AS QUERY
                        WHERE CANTIDAD > 0
                        ORDER BY CODIGO 
            ";

            if(count($tablaTraspasos) > 0){

                $resultSet = $this->dao->getArrayAsociativo($consulta);
               
                if(count($tablaArticulos) > 0){
                    
                    for($x=0; $x<count($tablaArticulos); $x++){
                        array_push($resultSet, $tablaArticulos[$x]);
                    }
                }
            }
            else{
                if(count($tablaArticulos) > 0){
                    $resultSet = $tablaArticulos;
                }
                else{
                    $resultSet = array();
                }
            }

            $datos = array();
            $datos['data'] = $resultSet;
            $datos['options'] = array();

            return json_encode($datos);
        } catch (\Exception $e){
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json; charset=UTF-8');
            die(json_encode(array("mensaje" => $e->getMessage(),
                "codigo" => $e->getCode(),
                "clase" => $e->getFile(),
                "linea" => $e->getLine())));
        }
    }

    public function articulosEmbarques(){
        try {
            ini_set('memory_limit', '-1');
            set_time_limit(0);

            $tablaEmbarques = json_decode(Request::input('tablaEmbarques'), true);
            $tablaArticulos = json_decode(Request::input('tablaArticulos'), true);

            $idEmbarques = $this->getIdsTraspasos($tablaEmbarques);

            $consulta = "SELECT *, CAST(CANTIDAD * factorKilo AS DECIMAL(28,2)) AS PESO_KG, 0 AS AGREGADO
                            FROM
                            (	
                                SELECT ART_ArticuloId AS DT_RowId
                                , ART_CodigoArticulo AS CODIGO, ART_Nombre AS NOMBRE, CMUM_Nombre AS UM, UM_CLAVE.CMM_DefinidoPorUsuario1 AS UM_CLAVE
                                , CMUM_UnidadMedidaId AS UM_ID, CAST(SUM(EMBD_CantidadEmbarcada)AS DECIMAL(28,2)) AS CANTIDAD
                                , CLAVE.CMM_DefinidoPorUsuario1 AS CLAVE, ART_CMM_ClaveProductoId AS CLAVE_ID
                                , factorKilo = ISNULL((
                                                SELECT AFC_FactorConversion
                                                FROM ArticulosFactoresConversion
                                                WHERE AFC_CMUM_UnidadMedidaId = '621D5394-CD57-4EBD-8EA9-C81C2124C6DA' AND AFC_ART_ArticuloId = ART_ArticuloId
                                                ), 0.00)
                                FROM EmbarquesDetalle
                                INNER JOIN TraspasosMovtos ON TRAM_TraspasoMovtoId = EMBD_TRAM_TraspasoMovtoId
                                INNER JOIN Articulos ON ART_ArticuloId = TRAM_ART_ArticuloId
                                INNER JOIN ControlesMaestrosMultiples CLAVE ON CLAVE.CMM_ControlId = ART_CMM_ClaveProductoId
                                INNER JOIN ControlesMaestrosUM ON CMUM_UnidadMedidaId = ART_CMUM_UMInventarioId
                                INNER JOIN ControlesMaestrosMultiples UM_CLAVE ON UM_CLAVE.CMM_ControlId = CMUM_CMM_ClaveUnidadId
                                WHERE EMBD_EMB_EmbarqueId IN ($idEmbarques)
                                GROUP BY ART_ArticuloId,ART_CodigoArticulo,ART_Nombre, ART_CMM_ClaveProductoId,CMUM_Nombre,CLAVE.CMM_DefinidoPorUsuario1,UM_CLAVE.CMM_DefinidoPorUsuario1,CMUM_UnidadMedidaId
                            ) AS QUERY
                            WHERE CANTIDAD > 0
                            ORDER BY CODIGO 
            ";

            if(count($tablaEmbarques) > 0){

                $resultSet = $this->dao->getArrayAsociativo($consulta);
               
                if(count($tablaArticulos) > 0){
                    
                    for($x=0; $x<count($tablaArticulos); $x++){
                        array_push($resultSet, $tablaArticulos[$x]);
                    }
                }
            }
            else{
                if(count($tablaArticulos) > 0){
                    $resultSet = $tablaArticulos;
                }
                else{
                    $resultSet = array();
                }
            }

            $datos = array();
            $datos['data'] = $resultSet;
            $datos['options'] = array();

            return json_encode($datos);
        } catch (\Exception $e){
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json; charset=UTF-8');
            die(json_encode(array("mensaje" => $e->getMessage(),
                "codigo" => $e->getCode(),
                "clase" => $e->getFile(),
                "linea" => $e->getLine())));
        }
    }

    public function articulosRutas(){
        try {
            ini_set('memory_limit', '-1');
            set_time_limit(0);

            $idRuta = Request::input('idRuta');
            $tablaArticulos = json_decode(Request::input('tablaArticulos'), true);

            $consulta = "SELECT *, CAST(CANTIDAD * factorKilo AS DECIMAL(28,2)) AS PESO_KG, 0 AS AGREGADO
                        FROM
                        (	
                            SELECT ART_ArticuloId AS DT_RowId
                            , ART_CodigoArticulo AS CODIGO, ART_Nombre AS NOMBRE, CMUM_Nombre AS UM, UM_CLAVE.CMM_DefinidoPorUsuario1 AS UM_CLAVE
                            , CMUM_UnidadMedidaId AS UM_ID, CAST(SUM(TRLOT_CantidadTraspaso)AS DECIMAL(28,2)) AS CANTIDAD
                            , CLAVE.CMM_DefinidoPorUsuario1 AS CLAVE, ART_CMM_ClaveProductoId AS CLAVE_ID
                            , factorKilo = ISNULL((
                                            SELECT AFC_FactorConversion
                                            FROM ArticulosFactoresConversion
                                            WHERE AFC_CMUM_UnidadMedidaId = '621D5394-CD57-4EBD-8EA9-C81C2124C6DA' AND AFC_ART_ArticuloId = ART_ArticuloId
                                            ), 0.00)
                            FROM Rutas
                            INNER JOIN Localidades ON LOC_LocalidadId = RUT_LOC_LocalidadId
                            INNER JOIN LotesLocalidades ON LOTL_LOC_LocalidadId = LOC_LocalidadId
                            INNER JOIN Lotes ON LOT_LoteId = LOTL_LOT_LoteId
                            INNER JOIN TraspasosLotes ON TRLOT_LOTL_LoteLocalidadId = LOTL_LoteLocalidadId
                            INNER JOIN Articulos ON ART_ArticuloId = LOT_ART_ArticuloId
                            INNER JOIN ControlesMaestrosMultiples CLAVE ON CLAVE.CMM_ControlId = ART_CMM_ClaveProductoId
                            INNER JOIN ControlesMaestrosUM ON CMUM_UnidadMedidaId = ART_CMUM_UMInventarioId
                            INNER JOIN ControlesMaestrosMultiples UM_CLAVE ON UM_CLAVE.CMM_ControlId = CMUM_CMM_ClaveUnidadId
                            WHERE RUT_RutaId = '$idRuta'
                            GROUP BY ART_ArticuloId,ART_CodigoArticulo,ART_Nombre, ART_CMM_ClaveProductoId,CMUM_Nombre,CLAVE.CMM_DefinidoPorUsuario1,UM_CLAVE.CMM_DefinidoPorUsuario1,CMUM_UnidadMedidaId
                        ) AS QUERY
                        WHERE CANTIDAD > 0
                        ORDER BY CODIGO
            ";


            $resultSet = $this->dao->getArrayAsociativo($consulta);

            if(count($tablaArticulos) > 0){
                    
                for($x=0; $x<count($tablaArticulos); $x++){
                    array_push($resultSet, $tablaArticulos[$x]);
                }
            }


            $datos = array();
            $datos['data'] = $resultSet;
            $datos['options'] = array();

            return json_encode($datos);
        } catch (\Exception $e){
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json; charset=UTF-8');
            die(json_encode(array("mensaje" => $e->getMessage(),
                "codigo" => $e->getCode(),
                "clase" => $e->getFile(),
                "linea" => $e->getLine())));
        }
    }

    public function rutasEmbarques() {
        try {
            ini_set('memory_limit', '-1');
            set_time_limit(0);

            $deptos = DataBaseSession::getCediId();
            if($deptos == null){
                $where = "";
            }
            else{
                $deptos = DataBaseSession::getCediId();
                $where = " AND DEP_DeptoId IN ($deptos)";
            }

            $consultaRutas = "SELECT * FROM
                            (
                                SELECT RUT_RutaId AS ID,
                                '(' + RUT_CODIGO+ ') '+ RUT_NOMBRE AS VALOR 
                                , RUT_NOMBRE
                                , RUT_CODIGO
                                , CASE WHEN CDE_DireccionEmbarqueId IS NOT NULL THEN '('+CLI_CodigoCliente + ') ' + CLI_RazonSocial + ' -  (' + CDE_NumeroSucursal+') ' + CDE_Nombre 
                                    ELSE '('+CLI_CodigoCliente + ') ' + CLI_RazonSocial END AS CLIENTE
                                , ROW_NUMBER() OVER(PARTITION BY manp_rut_rutaid ORDER BY manp_fechaInicio desc) AS FILA
                                , MANP_FechaInicio
                                , CASE WHEN CDE_DireccionEmbarqueId IS NOT NULL THEN CDE_DireccionEmbarqueId ELSE CLI_ClienteId END AS ID_CLIENTE
                                , CASE WHEN CDE_DireccionEmbarqueId IS NOT NULL THEN 'tienda' ELSE 'cliente' END AS TIPO_CLIENTE
                                , ISNULL(CDE_Calle,CLI_Calle) + ' #'+ISNULL(CDE_NoExt, CLI_NoExt) + ' COL. ' + CIUC_Nombre AS DIRECCION
                                , CIU_Nombre + ', '+EST_Nombre+ ', '+PAI_Nombre AS LOCALIZACION
                                from Rutas
                                INNER JOIN Departamentos ON DEP_DeptoId = RUT_DEP_DeptoId
                                INNER JOIN MantenimientoPrev ON RUT_RutaId = MANP_RUT_RutaId
                                LEFT JOIN Clientes ON CLI_ClienteId = MANP_CLI_ClienteId
                                LEFT JOIN ClientesCriteriosAdmon ON CCA_CLI_ClienteId = CLI_CLienteId
                                LEFT JOIN ClientesDireccionesEmbarques ON CDE_DireccionEmbarqueId = MANP_CDE_DireccionEmbarqueId
                                LEFT JOIN CiudadesColonias ON ISNULL(CDE_CIUC_ColoniaId, CLI_CIUC_ColoniaId) = CIUC_ColoniaId
                                LEFT JOIN Ciudades ON CIU_CiudadId = ISNULL(CDE_CIU_CiudadId, CLI_CIU_CiudadId)
                                LEFT JOIN Estados ON EST_EstadoId = ISNULL(CDE_EST_EstadoId,CLI_EST_EstadoId)
                                LEFT JOIN Paises ON PAI_PaisId = ISNULL(CDE_PAI_PaisId, CLI_PAI_PaisId)
                                WHERE RUT_Eliminado = 0 
                                AND MANP_Eliminado = 0
                                --AND CAST(MANP_FechaInicio AS DATE) = '20211025'
                                AND CAST(MANP_FechaInicio AS DATE) = CAST(GETDATE() AS DATE)
                                $where
                            ) AS X
                            WHERE FILA = 1
                            ORDER BY RUT_NOMBRE,RUT_CODIGO
            ";

            $rutas = $this->dao->getEjecutaConsulta($consultaRutas);    

            return compact('rutas');
        } catch (\Exception $e) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json; charset=UTF-8');
            die(json_encode(array("mensaje" => $e->getMessage(),
                "codigo" => $e->getCode(),
                "clase" => $e->getFile(),
                "linea" => $e->getLine())));
        }
    }

    public function clientesEmbarques() {
        try {
            ini_set('memory_limit', '-1');
            set_time_limit(0);

            $deptos = DataBaseSession::getCediId();
            if($deptos == null){
                $where = "";
            }
            else{
                $deptos = DataBaseSession::getCediId();
                $where = " AND DEP_DeptoId IN ($deptos)";
            }

            $rutaId = Request::input('rutaId');

            if($rutaId != ""){
               $whereRuta =  " AND RUT_RutaId = '$rutaId'";
            }
            else{
                $whereRuta =  "";
            }

            $consultaClientes = "SELECT * FROM
                                (
                                    SELECT 
                                        CLI_ClienteId AS ID,
                                        '('+CLI_CodigoCliente + ') ' + CLI_RazonSocial AS VALOR,
                                        CLI_RazonSocial,
                                        CLI_Calle + ' #'+CLI_NoExt + ' COL. ' + CIUC_Nombre AS DIRECCION
                                        , CIU_Nombre + ', '+EST_Nombre+ ', '+PAI_Nombre AS LOCALIZACION
                                        , 'AUTOSERVICIO' AS TIPO
                                        from Clientes 
                                        inner join clientesCriteriosAdmon on CCA_CLI_ClienteId = CLI_CLienteId
                                        inner join controlesmaestrosMultiples on cmm_controlid = CCA_CMM_TipoClienteId
                                        INNER JOIN ClientesDireccionesEmbarques ON CDE_CLI_ClienteId = CLI_ClienteId
                                        INNER JOIN Rutas ON RUT_RutaId = CDE_RUT_RutaId
                                        INNER JOIN Departamentos ON DEP_DeptoId = RUT_DEP_DeptoId
                                        INNER JOIN CiudadesColonias ON CLI_CIUC_ColoniaId = CIUC_ColoniaId
                                        INNER JOIN Ciudades ON CIU_CiudadId = CLI_CIU_CiudadId
                                        INNER JOIN Estados ON EST_EstadoId = CLI_EST_EstadoId
                                        INNER JOIN Paises ON PAI_PaisId = CLI_PAI_PaisId
                                        where CLI_Eliminado = 0 and CLI_Activo = 1
                                        and CCA_CMM_TipoClienteId = '0470AA58-D26E-47CA-BC29-2C0B1BA57F92'
                                        AND CDE_Eliminado = 0 AND RUT_Eliminado = 0
                                        $whereRuta
                                        $where
                                        GROUP BY CLI_ClienteId, CLI_CodigoCliente, CLI_RazonSocial,CLI_Calle,CLI_NoExt, CIU_Nombre,EST_Nombre, PAI_Nombre,CIUC_Nombre
                                        
                                    UNION ALL
                                
                                    SELECT 
                                        CLI_ClienteId AS ID,
                                        '('+CLI_CodigoCliente + ') ' + CLI_RazonSocial AS VALOR,
                                        CLI_RazonSocial,
                                        CLI_Calle + ' #'+CLI_NoExt + ' COL. '+CIUC_Nombre AS DIRECCION
                                        , CIU_Nombre + ', '+EST_Nombre+ ', '+PAI_Nombre AS LOCALIZACION
                                        , 'DETALLE' AS TIPO
                                        from Clientes 
                                        inner join clientesCriteriosAdmon on CCA_CLI_ClienteId = CLI_CLienteId
                                        INNER JOIN Rutas ON RUT_RutaId = CCA_RUT_RutaId
                                        INNER JOIN Departamentos ON DEP_DeptoId = RUT_DEP_DeptoId
                                        INNER JOIN CiudadesColonias ON CLI_CIUC_ColoniaId = CIUC_ColoniaId
                                        INNER JOIN Ciudades ON CIU_CiudadId = CLI_CIU_CiudadId
                                        INNER JOIN Estados ON EST_EstadoId = CLI_EST_EstadoId
                                        INNER JOIN Paises ON PAI_PaisId = CLI_PAI_PaisId
                                        where CLI_Eliminado = 0 and CLI_Activo = 1
                                        AND RUT_Eliminado = 0
                                        $whereRuta
                                        $where
                                        GROUP BY CLI_ClienteId, CLI_CodigoCliente, CLI_RazonSocial,CLI_Calle,CLI_NoExt, CIU_Nombre,EST_Nombre, PAI_Nombre,CIUC_Nombre
                                ) AS X   
                                ORDER BY CLI_RazonSocial
                ";

            $clientes = $this->dao->getEjecutaConsulta($consultaClientes);     

            return compact('clientes');
        } catch (\Exception $e) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json; charset=UTF-8');
            die(json_encode(array("mensaje" => $e->getMessage(),
                "codigo" => $e->getCode(),
                "clase" => $e->getFile(),
                "linea" => $e->getLine())));
        }
    }

    public function tiendasEmbarques() {
        try {
            ini_set('memory_limit', '-1');
            set_time_limit(0);
            
            if(Request::input('clientes') != null){
                $clientes = json_decode(Request::input('clientes'));

                $long = count($clientes);
                $idsClientes = "";

                for($x=0;$x<$long;$x++){

                    $id = $clientes[$x];
                    $idsClientes .= ($idsClientes == "" ? "" : ",") . "'$id'";
                
                }

                if($long > 0){
                $where =  " AND CDE_CLI_ClienteId IN ($idsClientes)";
                }
                else{
                    $where =  "";
                }

                $deptos = DataBaseSession::getCediId();

                if($deptos == null){
                    $whereDeptos = "";
                }
                else{
                    $deptos = DataBaseSession::getCediId();
                    $whereDeptos = " AND DEP_DeptoId IN ($deptos)";
                }

                $consultaTiendas = "SELECT CDE_DireccionEmbarqueId AS ID,
                    '('+CLI_CodigoCliente + ') ' + CLI_RazonSocial + ' -  (' + CDE_NumeroSucursal+') ' + CDE_Nombre AS VALOR ,
                    CDE_Calle + ' #'+CDE_NoExt + ' COL. '+CIUC_Nombre AS DIRECCION
                    , CIU_Nombre + ', '+EST_Nombre+ ', '+PAI_Nombre AS LOCALIZACION
                    FROM ClientesDireccionesEmbarques 
                    INNER JOIN Clientes ON CLI_ClienteId = CDE_CLI_ClienteId
                    INNER JOIN Rutas ON RUT_RutaId = CDE_RUT_RutaId
                    INNER JOIN Departamentos ON DEP_DeptoId = RUT_DEP_DeptoId
                    INNER JOIN CiudadesColonias ON CLI_CIUC_ColoniaId = CIUC_ColoniaId
                    INNER JOIN Ciudades ON CIU_CiudadId = CDE_CIU_CiudadId
                    INNER JOIN Estados ON EST_EstadoId = CDE_EST_EstadoId
                    INNER JOIN Paises ON PAI_PaisId = CDE_PAI_PaisId
                    WHERE CDE_Eliminado = 0
                    $where
                    $whereDeptos
                    ORDER BY CLI_RazonSocial ,CDE_Nombre 
                ";

                if($long > 0){
                    $tiendas = $this->dao->getEjecutaConsulta($consultaTiendas);        
                }
                else{
                    $tiendas = array();
                }
            }
            else{
                $tiendas = array();
            }

            return compact('tiendas');
        } catch (\Exception $e) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json; charset=UTF-8');
            die(json_encode(array("mensaje" => $e->getMessage(),
                "codigo" => $e->getCode(),
                "clase" => $e->getFile(),
                "linea" => $e->getLine())));
        }
    }

    public function rutas() {
        try {
            ini_set('memory_limit', '-1');
            set_time_limit(0);
            
            if(DataBaseSession::isPermisoCorporativo()){
                $whereDeptos = "";
            }
            else{
                $deptos = DataBaseSession::getCediId();
                $whereDeptos = " AND DEP_DeptoId IN ($deptos)";
            }

            $consultaTiendas = "SELECT RUT_RutaId AS ID,
                                '(' + RUT_CODIGO+ ') '+ RUT_NOMBRE AS VALOR 
                                from Rutas
                                INNER JOIN Localidades ON LOC_LocalidadId = RUT_LOC_LocalidadId
                                INNER JOIN Departamentos ON DEP_DeptoId = RUT_DEP_DeptoId
                                WHERE RUT_Eliminado = 0 AND LOC_Eliminado = 0 
                                $whereDeptos
                                ORDER BY RUT_NOMBRE,RUT_CODIGO
            ";

            $consultaRutas = "SELECT * FROM
                                (
                                    SELECT RUT_RutaId AS ID,
                                    '(' + RUT_CODIGO+ ') '+ RUT_NOMBRE AS VALOR 
                                    , RUT_NOMBRE
                                    , RUT_CODIGO
                                    , CASE WHEN CDE_DireccionEmbarqueId IS NOT NULL THEN '('+CLI_CodigoCliente + ') ' + CLI_RazonSocial + ' -  (' + CDE_NumeroSucursal+') ' + CDE_Nombre 
                                        ELSE '('+CLI_CodigoCliente + ') ' + CLI_RazonSocial END AS CLIENTE
                                    , ROW_NUMBER() OVER(PARTITION BY manp_rut_rutaid ORDER BY manp_fechaInicio desc) AS FILA
                                    , MANP_FechaInicio
                                    , CASE WHEN CDE_DireccionEmbarqueId IS NOT NULL THEN CDE_DireccionEmbarqueId ELSE CLI_ClienteId END AS ID_CLIENTE
                                    , CASE WHEN CDE_DireccionEmbarqueId IS NOT NULL THEN 'tienda' ELSE 'cliente' END AS TIPO_CLIENTE
                                    , ISNULL(CDE_Calle,CLI_Calle) + ' #'+ISNULL(CDE_NoExt, CLI_NoExt) + ' COL. ' + CIUC_Nombre AS DIRECCION
                                    , CIU_Nombre + ', '+EST_Nombre+ ', '+PAI_Nombre AS LOCALIZACION
                                    from Rutas
                                    INNER JOIN Departamentos ON DEP_DeptoId = RUT_DEP_DeptoId
                                    INNER JOIN MantenimientoPrev ON RUT_RutaId = MANP_RUT_RutaId
                                    LEFT JOIN Clientes ON CLI_ClienteId = MANP_CLI_ClienteId
                                    LEFT JOIN ClientesCriteriosAdmon ON CCA_CLI_ClienteId = CLI_CLienteId
                                    LEFT JOIN ClientesDireccionesEmbarques ON CDE_DireccionEmbarqueId = MANP_CDE_DireccionEmbarqueId
                                    LEFT JOIN CiudadesColonias ON ISNULL(CDE_CIUC_ColoniaId, CLI_CIUC_ColoniaId) = CIUC_ColoniaId
                                    LEFT JOIN Ciudades ON CIU_CiudadId = ISNULL(CDE_CIU_CiudadId, CLI_CIU_CiudadId)
                                    LEFT JOIN Estados ON EST_EstadoId = ISNULL(CDE_EST_EstadoId,CLI_EST_EstadoId)
                                    LEFT JOIN Paises ON PAI_PaisId = ISNULL(CDE_PAI_PaisId, CLI_PAI_PaisId)
                                    WHERE RUT_Eliminado = 0 
                                    AND MANP_Eliminado = 0
                                    --AND CAST(MANP_FechaInicio AS DATE) = '20211025'
                                    AND CAST(MANP_FechaInicio AS DATE) = CAST(GETDATE() AS DATE)
                                    $whereDeptos
                                ) AS X
                                WHERE FILA = 1
                                ORDER BY RUT_NOMBRE,RUT_CODIGO
            ";

            $rutas = $this->dao->getEjecutaConsulta($consultaRutas);        

            return compact('rutas');
        } catch (\Exception $e) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json; charset=UTF-8');
            die(json_encode(array("mensaje" => $e->getMessage(),
                "codigo" => $e->getCode(),
                "clase" => $e->getFile(),
                "linea" => $e->getLine())));
        }
    }

    public function embarques() {
        try {
            ini_set('memory_limit', '-1');
            set_time_limit(0);

            $rutaId = Request::input('rutaId');

            $tiendas = json_decode(Request::input('tiendas'));
            $embarquesSeleccionados = json_decode(Request::input('embarquesSeleccionados'));
            $long = count($tiendas);
            $idsTiendas = "";

            $clientes = json_decode(Request::input('clientes'));
            $longCli = count($clientes);
            $idsClientes = "";

            for($x=0;$x<$long;$x++){

                $id = $tiendas[$x];
                $idsTiendas .= ($idsTiendas == "" ? "" : ",") . "'$id'";
            
            }

            if($long > 0){
               $where =  " AND CDE_DireccionEmbarqueId IN ($idsTiendas)";
            }
            else{
                $where =  "";
            }

            for($y=0;$y<$longCli;$y++){

                $id = $clientes[$y];
                $idsClientes .= ($idsClientes == "" ? "" : ",") . "'$id'";
            
            }

            if($longCli > 0){
               $whereCli =  " AND CLI_ClienteId IN ($idsClientes)";
            }
            else{
                $whereCli =  "";
            }

            if($whereCli != "" && $where != "" ){
                $consulta = "SELECT DT_RowId, CODIGO, FECHA, CLIENTE, TIENDA , 0 AS CHECKBOX, 0 AS SELECCIONADO
                            FROM
                            (
                                SELECT EMB_EmbarqueId AS DT_RowId
                                , EMB_CodigoEmbarque AS CODIGO
                                , CAST(EMB_FechaEmbarque AS DATE) AS FECHA
                                , '('+CLI_CodigoCliente + ') ' + CLI_RazonSocial AS CLIENTE
                                , '(' + CDE_NumeroSucursal+') ' + CDE_Nombre AS TIENDA
                                , SUM(EMBD_CantidadEmbarcada) AS SUMA
                                FROM Embarques
                                INNER JOIN EmbarquesDetalle ON EMBD_EMB_EmbarqueId = EMB_EmbarqueId
                                INNER JOIN ClientesDireccionesEmbarques ON EMB_CDE_DireccionEmbarqueId = CDE_DireccionEmbarqueId
                                INNER JOIN Clientes ON CLI_ClienteId = CDE_CLI_ClienteId
                                WHERE EMB_PRP_ProgramaRutaId IS NULL                                                       
                                AND EMB_FechaEmbarque > (SELECT  DATEADD(DAY, -5, GETDATE()))
                                $where
                                GROUP BY EMBD_OVD_DetalleId, EMB_EmbarqueId, EMB_CodigoEmbarque, EMB_FechaEmbarque, CLI_CodigoCliente
                                ,CLI_RazonSocial,CDE_NumeroSucursal, CDE_Nombre
                            ) AS X
                            WHERE SUMA > 0
                            GROUP BY DT_RowId, CODIGO, FECHA, CLIENTE, TIENDA
                            
                            UNION ALL
                            
                            SELECT DT_RowId, CODIGO, FECHA, CLIENTE, TIENDA , 0 AS CHECKBOX, 0 AS SELECCIONADO
                            FROM
                            (
                                SELECT EMB_EmbarqueId AS DT_RowId
                                , EMB_CodigoEmbarque AS CODIGO
                                , CAST(EMB_FechaEmbarque AS DATE) AS FECHA
                                , '('+CLI_CodigoCliente + ') ' + CLI_RazonSocial AS CLIENTE
                                , NULL AS TIENDA
                                , SUM(EMBD_CantidadEmbarcada) AS SUMA
                                FROM Embarques
                                INNER JOIN EmbarquesDetalle ON EMBD_EMB_EmbarqueId = EMB_EmbarqueId
                                INNER JOIN Clientes ON CLI_ClienteId = EMB_CLI_ClienteId
                                WHERE EMB_PRP_ProgramaRutaId IS NULL    
                                AND EMB_CDE_DireccionEmbarqueId IS NULL                                                   
                                AND EMB_FechaEmbarque > (SELECT  DATEADD(DAY, -5, GETDATE()))
                                $whereCli
                                GROUP BY EMBD_OVD_DetalleId, EMB_EmbarqueId, EMB_CodigoEmbarque, EMB_FechaEmbarque, CLI_CodigoCliente
                                ,CLI_RazonSocial
                            ) AS X
                            WHERE SUMA > 0
                            GROUP BY DT_RowId, CODIGO, FECHA, CLIENTE, TIENDA            
                            ORDER BY CODIGO DESC
                ";
            }
            elseif ($whereCli != "") {
                $consulta = "SELECT DT_RowId, CODIGO, FECHA, CLIENTE, TIENDA , 0 AS CHECKBOX, 0 AS SELECCIONADO
                                FROM
                                (
                                    SELECT EMB_EmbarqueId AS DT_RowId
                                    , EMB_CodigoEmbarque AS CODIGO
                                    , CAST(EMB_FechaEmbarque AS DATE) AS FECHA
                                    , '('+CLI_CodigoCliente + ') ' + CLI_RazonSocial AS CLIENTE
                                    , NULL AS TIENDA
                                    , SUM(EMBD_CantidadEmbarcada) AS SUMA
                                    FROM Embarques
                                    INNER JOIN EmbarquesDetalle ON EMBD_EMB_EmbarqueId = EMB_EmbarqueId
                                    INNER JOIN Clientes ON CLI_ClienteId = EMB_CLI_ClienteId
                                    WHERE EMB_PRP_ProgramaRutaId IS NULL    
                                    AND EMB_CDE_DireccionEmbarqueId IS NULL                                                   
                                    AND EMB_FechaEmbarque > (SELECT  DATEADD(DAY, -5, GETDATE()))
                                    $whereCli
                                    GROUP BY EMBD_OVD_DetalleId, EMB_EmbarqueId, EMB_CodigoEmbarque, EMB_FechaEmbarque, CLI_CodigoCliente
                                    ,CLI_RazonSocial
                                ) AS X
                                WHERE SUMA > 0
                                GROUP BY DT_RowId, CODIGO, FECHA, CLIENTE, TIENDA            
                                ORDER BY CODIGO DESC";
            }
            elseif ($where != "") {
                $consulta = "SELECT DT_RowId, CODIGO, FECHA, CLIENTE, TIENDA , 0 AS CHECKBOX, 0 AS SELECCIONADO
                            FROM
                            (
                                SELECT EMB_EmbarqueId AS DT_RowId
                                , EMB_CodigoEmbarque AS CODIGO
                                , CAST(EMB_FechaEmbarque AS DATE) AS FECHA
                                , '('+CLI_CodigoCliente + ') ' + CLI_RazonSocial AS CLIENTE
                                , '(' + CDE_NumeroSucursal+') ' + CDE_Nombre AS TIENDA
                                , SUM(EMBD_CantidadEmbarcada) AS SUMA
                                FROM Embarques
                                INNER JOIN EmbarquesDetalle ON EMBD_EMB_EmbarqueId = EMB_EmbarqueId
                                INNER JOIN ClientesDireccionesEmbarques ON EMB_CDE_DireccionEmbarqueId = CDE_DireccionEmbarqueId
                                INNER JOIN Clientes ON CLI_ClienteId = CDE_CLI_ClienteId
                                WHERE EMB_PRP_ProgramaRutaId IS NULL                                                       
                                AND EMB_FechaEmbarque > (SELECT  DATEADD(DAY, -5, GETDATE()))
                                $where
                                GROUP BY EMBD_OVD_DetalleId, EMB_EmbarqueId, EMB_CodigoEmbarque, EMB_FechaEmbarque, CLI_CodigoCliente
                                ,CLI_RazonSocial,CDE_NumeroSucursal, CDE_Nombre
                            ) AS X
                            WHERE SUMA > 0
                            GROUP BY DT_RowId, CODIGO, FECHA, CLIENTE, TIENDA
                            ORDER BY CODIGO DESC
                ";
            }
            elseif ($rutaId != "") {
                $consulta = "SELECT DT_RowId, CODIGO, FECHA, CLIENTE, TIENDA , 0 AS CHECKBOX, 0 AS SELECCIONADO
                            FROM
                            (
                                SELECT EMB_EmbarqueId AS DT_RowId
                                , EMB_CodigoEmbarque AS CODIGO
                                , CAST(EMB_FechaEmbarque AS DATE) AS FECHA
                                , '('+CLI_CodigoCliente + ') ' + CLI_RazonSocial AS CLIENTE
                                , '(' + CDE_NumeroSucursal+') ' + CDE_Nombre AS TIENDA
                                , SUM(EMBD_CantidadEmbarcada) AS SUMA
                                FROM Embarques
                                INNER JOIN EmbarquesDetalle ON EMBD_EMB_EmbarqueId = EMB_EmbarqueId
                                INNER JOIN ClientesDireccionesEmbarques ON EMB_CDE_DireccionEmbarqueId = CDE_DireccionEmbarqueId
                                INNER JOIN Clientes ON CLI_ClienteId = CDE_CLI_ClienteId
                                WHERE EMB_PRP_ProgramaRutaId IS NULL                                                       
                                AND EMB_FechaEmbarque > (SELECT  DATEADD(DAY, -5, GETDATE()))
                                AND CDE_RUT_RutaId = '$rutaId'
                                GROUP BY EMBD_OVD_DetalleId, EMB_EmbarqueId, EMB_CodigoEmbarque, EMB_FechaEmbarque, CLI_CodigoCliente
                                ,CLI_RazonSocial,CDE_NumeroSucursal, CDE_Nombre
                            ) AS X
                            WHERE SUMA > 0
                            GROUP BY DT_RowId, CODIGO, FECHA, CLIENTE, TIENDA
                            
                            UNION ALL
                            
                            SELECT DT_RowId, CODIGO, FECHA, CLIENTE, TIENDA , 0 AS CHECKBOX, 0 AS SELECCIONADO
                            FROM
                            (
                                SELECT EMB_EmbarqueId AS DT_RowId
                                , EMB_CodigoEmbarque AS CODIGO
                                , CAST(EMB_FechaEmbarque AS DATE) AS FECHA
                                , '('+CLI_CodigoCliente + ') ' + CLI_RazonSocial AS CLIENTE
                                , NULL AS TIENDA
                                , SUM(EMBD_CantidadEmbarcada) AS SUMA
                                FROM Embarques
                                INNER JOIN EmbarquesDetalle ON EMBD_EMB_EmbarqueId = EMB_EmbarqueId
                                INNER JOIN Clientes ON CLI_ClienteId = EMB_CLI_ClienteId
                                INNER JOIN ClientesCriteriosAdmon ON CCA_CLI_ClienteId = CLI_CLienteId
                                WHERE EMB_PRP_ProgramaRutaId IS NULL    
                                AND EMB_CDE_DireccionEmbarqueId IS NULL                                                   
                                AND EMB_FechaEmbarque > (SELECT  DATEADD(DAY, -5, GETDATE()))
                                AND CCA_RUT_RutaId = '$rutaId'
                                GROUP BY EMBD_OVD_DetalleId, EMB_EmbarqueId, EMB_CodigoEmbarque, EMB_FechaEmbarque, CLI_CodigoCliente
                                ,CLI_RazonSocial
                            ) AS X
                            WHERE SUMA > 0
                            GROUP BY DT_RowId, CODIGO, FECHA, CLIENTE, TIENDA 
                            ORDER BY CODIGO DESC
                ";
            }
            else{
                $datos = array();
                $datos['data'] = array();
                $datos['options'] = array();
                return $datos;
            }                        

            $resultSet = $this->dao->getDataTable($consulta);
            $result = json_decode($resultSet);

            if(count($result->data) > 0){

                if(count($embarquesSeleccionados) > 0){

                    $numEmbarquesSelect = count($embarquesSeleccionados);
                    $numEmbarques = count($result->data);

                    for($x=0;$x<$numEmbarquesSelect;$x++){
                        $idSelect = $embarquesSeleccionados[$x];

                        for($y=0;$y<$numEmbarques;$y++){
                            if($result->data[$y]->DT_RowId == $idSelect){
                                $result->data[$y]->CHECKBOX = 1;
                                $result->data[$y]->SELECCIONADO = 1;
                            }
                        }
                    }

                }

            }

            return json_encode($result);
        } catch (\Exception $e) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json; charset=UTF-8');
            die(json_encode(array("mensaje" => $e->getMessage(),
                "codigo" => $e->getCode(),
                "clase" => $e->getFile(),
                "linea" => $e->getLine())));
        }
    }

    private function getIdsTraspasos($arrayTabla){

        $long = count($arrayTabla);
        $idsTraspasos = "";

        for($x=0;$x<$long;$x++){

            $traspasoId = $arrayTabla[$x]['DT_RowId'];

            $idsTraspasos .= ($idsTraspasos == "" ? "" : ",") . "'$traspasoId'";
        
        }

        return $idsTraspasos;

    }

    private function getIdsTraspasosNuevo($arrayTabla){

        $long = count($arrayTabla);
        $idsTraspasos = "";

        for($x=0;$x<$long;$x++){

            $traspasoId = $arrayTabla[$x]->DT_RowId;

            $idsTraspasos .= ($idsTraspasos == "" ? "" : ",") . "'$traspasoId'";
        
        }

        return $idsTraspasos;

    }


    private function getIdsTraspasosDetalle($ids){

        $consulta = "SELECT TRAD_TraspasoDetalleId AS DT_RowId
            , ART_CodigoArticulo AS CODIGO, ART_Nombre AS NOMBRE, CMUM_Nombre AS UM, CMM_DefinidoPorUsuario1 AS UM_CLAVE
            , CMUM_UnidadMedidaId AS UM_ID, TRAD_CantidadATraspasar--CAST(SUM(TRAD_CantidadATraspasar)AS DECIMAL(28,2)) AS CANTIDAD                           
            FROM TraspasosSolicitudesDetalle
            INNER JOIN TraspasosDetalle ON TRAD_TRSD_DetalleId = TRSD_DetalleId
            INNER JOIN Articulos ON ART_ArticuloId = TRSD_ART_ArticuloId
            INNER JOIN ControlesMaestrosUM ON CMUM_UnidadMedidaId = ART_CMUM_UMInventarioId
            INNER JOIN ControlesMaestrosMultiples ON CMM_ControlId = CMUM_CMM_ClaveUnidadId
            LEFT JOIN TraspasosRecibos ON TRAR_TRAD_TraspasoDetalleId = TRAD_TraspasoDetalleId
            WHERE TRSD_TRS_TraspasoSolicitudId IN ($ids)
            AND TRAR_TraspasoReciboId IS NULL
            GROUP BY TRAD_TraspasoDetalleId,TRAD_CantidadATraspasar,ART_ArticuloId,ART_CodigoArticulo,ART_Nombre,CMUM_Nombre,CMM_DefinidoPorUsuario1,CMUM_UnidadMedidaId                     
            ORDER BY CODIGO 
        ";


        $result = $this->dao->getArrayAsociativo($consulta);

        $long = count($result);
        $idsTraspasos = "";

        for($x=0;$x<$long;$x++){

            $traspasoId = $result[$x]['DT_RowId'];

            $idsTraspasos .= ($idsTraspasos == "" ? "" : ",") . "'$traspasoId'";
        
        }

        return $idsTraspasos;

    }

    function guardar(){
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        \DB::beginTransaction();
        try{
            $tblCabecera = json_decode(Request::input('cabecera'));
            $tblTabla = json_decode(Request::input('tabla'));
            $tblArticulos = json_decode(Request::input('tablaArticulos'));
            $isCartaPorte = json_decode(Request::input('isCartaPorte'));
            $proceso = json_decode(Request::input('proceso'));
            $modificadoPorId = DataBaseSession::getEmpleadoId();

            if($isCartaPorte == 1){
                $tblCabecera->fecha =  $tblCabecera->fecha.' '.self::getHoraServidor();
            }

            $codigo = null;

            $dao = new DAOGeneralController();

            $autonumerico_dao = new AutonumericoController();

            if ($autonumerico_dao->isAutonumericoActivoPorReferenciaId('CM_SiguienteProgramaRuta', null)) {
                $autonumerico_id = self::establecerAutonumerico(null, $modificadoPorId, 'CM_SiguienteProgramaRuta');
                $codigo = $autonumerico_dao->getSiguienteAutonumericoPorId($autonumerico_id);
            }

            $id = self::getNuevoId();
            self::procesaNuevo($tblCabecera, $tblTabla, $codigo, $modificadoPorId, $id, $tblArticulos, $isCartaPorte, $proceso);
  
            \DB::commit();

            $ajaxData = array();
            $ajaxData['respuesta'] = $codigo;
            $ajaxData['codigo'] = 200;
            $ajaxData['id'] = $id;
            echo json_encode($ajaxData);
        }catch (\Exception $e){
            \DB::rollback();

            if($e->getCode() == 20){
                header('HTTP/1.1 500 Internal Server Error');
                header('Content-Type: application/json; charset=UTF-8');
                die(json_encode(array("mensaje" => "No hay tipo de cambio para la fecha seleccionada.",
                    "codigo" => '',
                    "clase" => '',
                    "linea" => '')));
            } else if($e->getCode() == 30){
                header('HTTP/1.1 500 Internal Server Error');
                header('Content-Type: application/json; charset=UTF-8');
                die(json_encode(array("mensaje" => $e->getMessage(),
                    "codigo" => '',
                    "clase" => '',
                    "linea" => '')));
            } else if($e->getCode() == 22){
                header('HTTP/1.1 500 Internal Server Error');
                header('Content-Type: application/json; charset=UTF-8');
                die(json_encode(array("mensaje" => $e->getMessage(),
                    "codigo" => '',
                    "clase" => '',
                    "linea" => '')));
            } else {
                header('HTTP/1.1 500 Internal Server Error');
                header('Content-Type: application/json; charset=UTF-8');
                die(json_encode(array("mensaje" => $e->getMessage(),
                    "codigo" => $e->getCode(),
                    "clase" => $e->getFile(),
                    "linea" => $e->getLine())));
            }
        }
    }

    function procesaNuevo($tblCabecera, $tblTabla, $codigo, $modificadoPorId, $id, $tblArticulos, $isCartaPorte, $proceso){
        try{
            $programa = new ProgramaRutasReparto();
            $programa->PRP_ProgramaRutaId = $id;
            $programa->PRP_Codigo = $codigo;            
            $programa->PRP_EMP_CreadoPorId = $modificadoPorId;
            $programa->PRP_CartaPorte = $isCartaPorte;
            if($isCartaPorte == 1){
                $programa->PRP_DEP_OrigenCediId = $tblCabecera->origenId;
                $programa->PRP_Fecha =  $tblCabecera->fecha;
                $programa->PRP_TUN_TransporteUnidadId = $tblCabecera->vehiculoId;
                $programa->PRP_EMP_OperadorId = $tblCabecera->operadorId;
                $programa->PRP_FechaEstimadaSalida = $tblCabecera->fechaSalida;
                $programa->PRP_FechaEstimadaLlegada = $tblCabecera->fechaLlegada;
                $programa->PRP_DistanciaRecorrida = $tblCabecera->distancia;
            }
            //TRASPASOS
            if($proceso == 1){
                $programa->PRP_Proceso = "Traspasos";
            }
            //EMBARQUES
            else if($proceso == 2){            
                $programa->PRP_Proceso = "Embarques";
                if($isCartaPorte == 1){
                    $tipoDestino = $tblCabecera->tipoDestino;
                    if($tipoDestino == "cliente"){
                        $programa->PRP_CLI_DestinoClienteId = $tblCabecera->destinoEmbarque;
                    }
                    else{
                        $programa->PRP_CDE_DestinoTiendaId = $tblCabecera->destinoEmbarque;
                    }
                }
            }
            //RUTAS
            else if($proceso == 3){
                $programa->PRP_Proceso = "Rutas Detalle";
                if($isCartaPorte == 1){
                    $tipoDestino = $tblCabecera->tipoDestino;
                    if($tipoDestino == "cliente"){
                        $programa->PRP_CLI_DestinoClienteId = $tblCabecera->destinoEmbarque;
                    }
                    else{
                        $programa->PRP_CDE_DestinoTiendaId = $tblCabecera->destinoEmbarque;
                    }
                }
            }

            $programa->save();

            if($isCartaPorte == 1){
                
                if($proceso == 1){
                    $tblDestinos = $tblCabecera->destinoId;

                    $rowCount = count($tblDestinos);
                    for ($i = 0; $i < $rowCount; $i++) {
                        $destino = new ProgramaRutasRepartoDestinos();
                        $destino->PRPD_PRP_ProgramaRutaId = $id;
                        $destino->PRPD_DEP_DestinoCediId = $tblDestinos[$i];
                        $destino->save();
                    }
                }
            }

            //TRASPASOS
            if($proceso == 1){
                if(count($tblTabla) > 0){
                    $idsTraspasos = $this->getIdsTraspasosNuevo($tblTabla);        
                    $idsTraspasosDetalle = $this->getIdsTraspasosDetalle($idsTraspasos);
    
                    TraspasosDetalle::whereRaw('TRAD_TraspasoDetalleId IN (' . $idsTraspasosDetalle . ')')
                    ->update(['TRAD_PRP_ProgramaRutaId' => $id]);
                }
            }
            //EMBARQUES
            else if($proceso == 2){
                if(count($tblTabla) > 0){
                    $idsEmbarques = $this->getIdsTraspasosNuevo($tblTabla);        

                    Embarques::whereRaw('EMB_EmbarqueId IN (' . $idsEmbarques . ')')
                    ->update(['EMB_PRP_ProgramaRutaId' => $id]);
                }
            }
            
                
            $rowCount = count($tblArticulos);
            for ($i = 0; $i < $rowCount; $i++) {
                
                $programaArticulo = new ProgramaRutasRepartoArticulos();
                $programaArticulo->PRPA_PRP_ProgramaRutaId = $id;
                $programaArticulo->PRPA_Producto = $tblArticulos[$i]->NOMBRE;
                $programaArticulo->PRPA_ART_ArticuloId = $tblArticulos[$i]->DT_RowId  == '' ? null : $tblArticulos[$i]->DT_RowId;
                $programaArticulo->PRPA_CMUM_UnidadMedidaId = $tblArticulos[$i]->UM_ID;
                $programaArticulo->PRPA_Cantidad = $tblArticulos[$i]->CANTIDAD;
                $programaArticulo->PRPA_PesoKg = $tblArticulos[$i]->PESO_KG;
                $programaArticulo->PRPA_CMM_ClaveProductoId = $tblArticulos[$i]->CLAVE_ID;
                $programaArticulo->save();
            }

        }catch (\Exception $e){
            throw $e;
        }
    }

    public function timbrar(){
        try {
            $id = Request::input('id');

            $programa = ProgramaRutasReparto::find($id);

            \DB::beginTransaction();
            $cfdi = new Cfdiv33();
            $cfdi->timbrarCartaPorte($id);

            \DB::commit();

            $codigo = $programa->PRP_Codigo;
            $rfc = $this->dao->getArrayAsociativo("SELECT CMA_Valor FROM ControlesMaestros WHERE CMA_Control = 'CMA_CSVP_RFC'")[0]['CMA_Valor'];
            $ruta = PropiedadesTimbrado::buscaPorControl(Cfdiv33::LOCATION_XML);

            return compact('codigo', 'rfc', 'ruta');
        } catch (\Exception $e){
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json; charset=UTF-8');
            die(json_encode(array("mensaje" => $e->getMessage(),
                "codigo" => $e->getCode(),
                "clase" => $e->getFile(),
                "linea" => $e->getLine())));
        }
    }

    public function cancelar(){
        \DB::beginTransaction();
        try{
            require_once public_path().'/plugins/tcpdf/tcpdf.php';
            require_once public_path().'/plugins/fpdi/fpdi.php';

            $programaId = $_POST['programaId'];
            $timbrado = $_POST['timbrado'];

            $programaRuta = ProgramaRutasReparto::find($programaId);
            $proceso = $programaRuta->PRP_Proceso;

            if($proceso == "Traspasos"){
                TraspasosDetalle::where('TRAD_PRP_ProgramaRutaId', '=', $programaId)
                    ->update(['TRAD_PRP_ProgramaRutaId' => null]);
            }
            else if($proceso == "Embarques"){
                Embarques::where('EMB_PRP_ProgramaRutaId', '=', $programaId)
                    ->update(['EMB_PRP_ProgramaRutaId' => null]);
            }
        

            $empleadoId = DataBaseSession::getEmpleadoId();

            $programaRuta->PRP_Eliminado = 1;
            $programaRuta->PRP_EMP_ModificadoPorId = $empleadoId;
            $programaRuta->save();

            if($timbrado != 0){
                $cfdi = new Cfdiv33();
                $cfdi->cancelaCartaPorte($programaId);
            }

            \DB::commit();
            return json_encode(array());
        } catch(\Exception $e){
            \DB::rollback();

            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json; charset=UTF-8');
            die(json_encode(array("mensaje" => $e->getMessage(),
                "codigo" => $e->getCode(),
                "clase" => $e->getFile(),
                "linea" => $e->getLine())));
        } catch(\SoapFault $e){
            \DB::rollback();

            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json; charset=UTF-8');
            die(json_encode(array("mensaje" => $e->getMessage(),
                "codigo" => $e->getCode(),
                "clase" => $e->getFile(),
                "linea" => $e->getLine())));
        } catch(\FileException $e){
            \DB::rollback();

            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json; charset=UTF-8');
            die(json_encode(array("mensaje" => $e->getMessage(),
                "codigo" => $e->getCode(),
                "clase" => $e->getFile(),
                "linea" => $e->getLine())));
        }
    }

    public static function getNuevoId()
    {
        if (function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

    public function getFechaHoraServidorANSI()
    {
        $dao = new DAOGeneralController();

        $fecha = $dao->getEjecutaConsulta(" SELECT CONVERT(CHAR(10), GETDATE(), 112) + CONVERT(VARCHAR(8), GETDATE(), 108) AS FECHA_HORA_ANSI ");
        return $fecha[0]->FECHA_HORA_ANSI;
    }

    public function establecerAutonumerico($clienteId, $empleadoId, $autonumerico)
    {
        try {
            $autonumerico_dao = new AutonumericoController();
            $autonumericoFicha = $autonumerico_dao->getAutonumericoN($autonumerico, DataBaseSession::getCediId());
            return $autonumericoFicha->AUT_AutonumericoId;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public function formatearFecha($fecha) {
        $date = new \DateTime($fecha);
        $date = explode('+', date('c', $date->getTimestamp()))[0];
        return $date;
    }

    public function getHoraServidor(){
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        try{
            $dao = new DAOGeneralController();
            $resultSet = $dao->getEjecutaConsulta("SELECT CONVERT(varchar, GETDATE(), 108) AS HORA_SERVIDOR");

            return $resultSet[0]->HORA_SERVIDOR;
        } catch (\Exception $e){
            throw $e;
        }
    }


}