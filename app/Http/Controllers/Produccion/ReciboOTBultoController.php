<?php namespace App\Http\Controllers\Produccion;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Sistema\DAOGeneralController;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\Sistema\DataBaseSession;

use App\Models\Articulos;
use App\Models\Inventario\InventarioFisico\DetallesMovimientoInventario;
use App\Models\Inventario\InventarioFisico\ProcesadorMovimientoInventarios;
use App\Models\Localidades;
use App\Models\Lotes;
use App\Models\TraspasosMovtos;
use App\Models\BultoOTRecibo;
use App\Models\Bultos;
use App\Models\BultosDetalle;
use App\Models\OrdenesTrabajo;

class ReciboOTBultoController extends Controller {

    const RECIBO_OT = 'A98EEB00-F422-46CA-A45D-6B6AF2DEEF2A';
    const RECIBO_COMPLETO = 'F742508D-9B5B-4B8E-9F43-AE5C31ADD7DF';
    const RECIBO_PARCIAL = 'EB967196-EF77-49A5-82B8-57DAC0ABD632';
    private $dao;

    public function __construct() {
        $this->dao = new DAOGeneralController();
    }

    public function index() {
        $version = $this->dao->nuevoId();

        return view('ReciboOTBulto.ReciboOTBulto', compact('version'));
    }

    public function bultosRegistros(){
        try{
            ini_set('memory_limit', '-1');
            set_time_limit(0);

            $consulta = "
                SELECT 
                      DT_RowId
                    , CHK
                    , BUL_NumeroBulto
                    , ALM_CodigoAlmacen
                    , ALM_Nombre
                    , LOC_CodigoLocalidad
                    , LOC_Nombre
                    , ALM_AlmacenId
                    , LOC_LocalidadId
                    , COMPLEMENTO
                FROM (
                        SELECT 
                              BUL_BultoId AS DT_RowId
                            , CAST(0 AS BIT) AS CHK  
                            , BUL_NumeroBulto
                            , ALM_CodigoAlmacen
                            , ALM_Nombre
                            , LOC_CodigoLocalidad
                            , LOC_Nombre
                            , ALM_AlmacenId
                            , LOC_LocalidadId
                            , ROW_NUMBER() OVER(PARTITION BY BUL_BultoId ORDER BY BUL_NumeroBulto) AS FILA
                            , CAST(0 AS BIT) AS COMPLEMENTO
                        FROM Bultos
                        INNER JOIN BultosDetalle ON BUL_BultoId = BULD_BUL_BultoId AND BULD_Eliminado = 0
                        INNER JOIN Articulos ON BULD_ART_ArticuloId = ART_ArticuloId
                        INNER JOIN Localidades ON ART_LOC_LocPredEntradasId = LOC_LocalidadId
                        INNER JOIN Almacenes ON LOC_ALM_AlmacenId = ALM_AlmacenId
                        WHERE BUL_Eliminado = 0 
                              AND BUL_CMM_EstatusBultoId = 'A1DDDA80-4B1C-4F72-AA48-BF69C66B64BA' -- Abierta
                        UNION ALL
                        SELECT 
                              BUL_BultoId AS DT_RowId
                            , CAST(0 AS BIT) AS CHK  
                            , BUL_NumeroBulto
                            , '' AS ALM_CodigoAlmacen
                            , '' AS ALM_Nombre
                            , '' AS LOC_CodigoLocalidad
                            , '' AS LOC_Nombre
                            , CAST(NULL AS UNIQUEIDENTIFIER) AS ALM_AlmacenId
                            , CAST(NULL AS UNIQUEIDENTIFIER) AS LOC_LocalidadId
                            , ROW_NUMBER() OVER(PARTITION BY BUL_BultoId ORDER BY BUL_NumeroBulto) AS FILA
                            , CAST(1 AS BIT) AS COMPLEMENTO
                        FROM Bultos
                        WHERE BUL_Eliminado = 0 AND BUL_BultoPadreId IS NOT NULL
                              AND BUL_CMM_EstatusBultoId = 'A1DDDA80-4B1C-4F72-AA48-BF69C66B64BA' -- Abierta
                    ) AS TEMP
                WHERE FILA = 1
                ORDER BY 
                      BUL_NumeroBulto DESC
            ";

            $result = $this->dao->getEjecutaConsulta($consulta);

            return $result;
        } 
        catch (\Exception $e){
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json; charset=UTF-8');
            die(json_encode(array("mensaje" => $e->getMessage(),
                "codigo" => $e->getCode(),
                "clase" => $e->getFile(),
                "linea" => $e->getLine())));
        }
    }

    public function almacen(){
        try{
            ini_set('memory_limit', '-1');
            set_time_limit(0);

            $consulta = "
                        SELECT 
                                ALM_AlmacenId AS DT_RowId
                            , ALM_CodigoAlmacen 
                            , ALM_Nombre
                        FROM Almacenes
                        WHERE ALM_Eliminado = 0
                        ORDER BY
                                ALM_CodigoAlmacen ";

            $resultSet = $this->dao->getDataTable($consulta);

            return $resultSet;
        } 
        catch (\Exception $e){
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json; charset=UTF-8');
            die(json_encode(array("mensaje" => $e->getMessage(),
                "codigo" => $e->getCode(),
                "clase" => $e->getFile(),
                "linea" => $e->getLine())));
        }
    }

    public function localidad(){
        try{
            ini_set('memory_limit', '-1');
            set_time_limit(0);

            $almacenId = Request::input('almacenId');

            $consulta = "
                        SELECT
                              LOC_LocalidadId AS DT_RowId
                            , LOC_CodigoLocalidad
                            , LOC_Nombre
                        FROM Localidades
                        WHERE LOC_Eliminado = 0 AND LOC_ALM_AlmacenId = '$almacenId'
                        ORDER BY 
                              LOC_CodigoLocalidad ";

            $resultSet = $this->dao->getDataTable($consulta);

            return $resultSet;
        } 
        catch (\Exception $e){
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json; charset=UTF-8');
            die(json_encode(array("mensaje" => $e->getMessage(),
                "codigo" => $e->getCode(),
                "clase" => $e->getFile(),
                "linea" => $e->getLine())));
        }
    }

    public function guardar(){
        \DB::beginTransaction();
        try{
            ini_set('memory_limit', '-1');
            set_time_limit(0);

            $tabla = json_decode(Request::input('tabla'), true);
            $fecha = Request::input('fechaRecibo');
            $fecha = explode('/', $fecha);
            $fecha = $fecha[2].$fecha[1].$fecha[0];
            
            for ($i = 0; $i < count($tabla); $i++) {
                $bultoId = $tabla[$i]['DT_RowId'];
                $numeroBulto = $tabla[$i]['BUL_NumeroBulto'];
                $almacenId = $tabla[$i]['ALM_AlmacenId'];
                $localidadId = $tabla[$i]['LOC_LocalidadId'];
                $complemento = $tabla[$i]['COMPLEMENTO'];

                if($complemento == 0) {
                    $consulta = "
                        SELECT 
                              BULD_BUL_BultoId
                            , BULD_OT_OrdenTrabajoId
                            , BULD_ART_ArticuloId
                            , BULD_Cantidad - ISNULL(BOR_Cantidad, 0.0) AS CANTIDAD
                            , LOT_LoteId
                            , OT_Codigo
                            , BUL_NumeroBulto
                            , '(' + ART_CodigoArticulo + ') ' + ART_Nombre AS ARTICULO
                            , ABS(CHECKSUM(NEWID())) AS CodigoLote
                        FROM Bultos
                        INNER JOIN BultosDetalle ON BUL_BultoId = BULD_BUL_BultoId
                        INNER JOIN OrdenesTrabajo ON BULD_OT_OrdenTrabajoId = OT_OrdenTrabajoId
                        INNER JOIN Articulos ON BULD_ART_ArticuloId = ART_ArticuloId
                        LEFT  JOIN (
                                    SELECT 
                                          BOR_BUL_BultoId
                                        , BOR_OT_OrdenTrabajoId
                                        , BOR_ART_ArticuloId
                                        , SUM(BOR_Cantidad) AS BOR_Cantidad
                                    FROM BultoOTRecibo
                                    GROUP BY 
                                          BOR_BUL_BultoId
                                        , BOR_OT_OrdenTrabajoId
                                        , BOR_ART_ArticuloId
                                   ) AS BultoOTRecibo ON BULD_BUL_BultoId = BOR_BUL_BultoId 
                                        AND BULD_OT_OrdenTrabajoId = BOR_OT_OrdenTrabajoId 
                                        AND BULD_ART_ArticuloId = BOR_ART_ArticuloId
                        LEFT  JOIN (
                                    SELECT 
                                          LOT_LoteId
                                        , LOT_ART_ArticuloId
                                        , ROW_NUMBER() OVER(PARTITION BY LOT_ART_ArticuloId ORDER BY LOT_LoteId, LOT_FechaCreacion DESC) AS FILA
                                    FROM Lotes
                                    WHERE LOT_Eliminado = 0
                                    ) AS Lotes ON BULD_ART_ArticuloId = LOT_ART_ArticuloId AND FILA = 1
                        WHERE BULD_Eliminado = 0
                              AND BULD_BUL_BultoId = '$bultoId'
                        GROUP BY 
                              BULD_BUL_BultoId
                            , BULD_OT_OrdenTrabajoId
                            , BULD_ART_ArticuloId
                            , BULD_Cantidad
                            , BOR_Cantidad
                            , LOT_LoteId
                            , OT_Codigo
                            , BUL_NumeroBulto
                            , ART_CodigoArticulo
                            , ART_Nombre
                        HAVING BULD_Cantidad - ISNULL(BOR_Cantidad, 0.0) > 0 ";

                    $bulto = \DB::select(\DB::raw($consulta));

                    for ($x = 0; $x < count($bulto); $x++) {
                        $movimientos = new \stdClass();
                        $movimientos->ALMACEN_ID = $almacenId;
                        $movimientos->LOCALIDAD_ID = $localidadId;
                        $movimientos->LOTE_ID = $bulto[$x]->LOT_LoteId;
                        $movimientos->CANTIDAD = $bulto[$x]->CANTIDAD;

                        $ot = $bulto[$x]->OT_Codigo;
                        $articulo = $bulto[$x]->ARTICULO;
                        $ordenTrabajoId = $bulto[$x]->BULD_OT_OrdenTrabajoId;

                        if(is_null($movimientos->LOTE_ID)){
                            $lote = new Lotes();
                            $lote->LOT_LoteId = $this->dao->nuevoId();
                            $lote->LOT_NumeroLote = '';
                            $lote->LOT_ART_ArticuloId = $bulto[$x]->BULD_ART_ArticuloId;
                            $lote->LOT_CodigoLote = $bulto[$x]->CodigoLote;
                            $lote->LOT_FechaCreacion = $this->dao->getFechaHoraServidorANSI();
                            $lote->LOT_FechaUltimaModificacion = $this->dao->getFechaHoraServidorANSI();
                            $lote->LOT_Eliminado = 0;
                            $lote->LOT_EMP_ModificadoPorId = DataBaseSession::getEmpleadoId();
                            $lote->LOT_CMM_EstatusLoteId = '362B0AC5-85A1-4DB1-A725-DA1C64702E7D';
                            $lote->LOT_FechaLote = $this->dao->getFechaHoraServidorANSI();
                            $lote->LOT_LoteManual = 0;
                            $lote->LOT_Cerrado = 0;
                            $lote->LOT_MateriaPrima = 0;
                            $lote->LOT_OT_OrdenTrabajoId = $ordenTrabajoId;
                            $lote->save();

                            $movimientos->LOTE_ID = $lote->LOT_LoteId;
                            //throw new \Exception("No se encontró ningún lote para el artículo $articulo del bulto $numeroBulto", 124);
                        }

                        $traspasoMovtoId = self::guardaTransferenciaMovto($movimientos, $bulto[$x]);

                        $bor = new BultoOTRecibo();
                        $bor->BOR_BultoOTReciboId = $this->dao->nuevoId();
                        $bor->BOR_OT_OrdenTrabajoId = $ordenTrabajoId;
                        $bor->BOR_BUL_BultoId = $bultoId;
                        $bor->BOR_ART_ArticuloId = $bulto[$x]->BULD_ART_ArticuloId;
                        $bor->BOR_FechaRecibo = $fecha;
                        $bor->BOR_Cantidad = $bulto[$x]->CANTIDAD;
                        $bor->BOR_EMP_CreadoPorId = DataBaseSession::getEmpleadoId();
                        $bor->BOR_EMP_ModificadoPorId = DataBaseSession::getEmpleadoId();
                        $bor->BOR_TRAM_TraspasoMovtoId = $traspasoMovtoId;
                        $bor->save();

                        $consulta = "
                            SELECT OT_OrdenTrabajoId, OTDA_ART_ArticuloId, OTDA_Cantidad
                            FROM OrdenesTrabajo
                            INNER JOIN OrdenesTrabajoDetalleArticulos ON OT_OrdenTrabajoId = OTDA_OT_OrdenTrabajoId
                            LEFT  JOIN (
                                        SELECT 
                                            BOR_OT_OrdenTrabajoId
                                            , BOR_ART_ArticuloId
                                            , SUM(BOR_Cantidad) AS BOR_Cantidad
                                        FROM BultoOTRecibo
                                        GROUP BY 
                                            BOR_OT_OrdenTrabajoId
                                            , BOR_ART_ArticuloId
                                        ) AS BultoOTRecibo ON OT_OrdenTrabajoId = BOR_OT_OrdenTrabajoId 
                                                            AND OTDA_ART_ArticuloId = BOR_ART_ArticuloId
                            WHERE OT_OrdenTrabajoId = '$ordenTrabajoId'
                            GROUP BY OT_OrdenTrabajoId, OTDA_ART_ArticuloId, OTDA_Cantidad, BOR_Cantidad
                            HAVING OTDA_Cantidad - ISNULL(BOR_Cantidad, 0.0) > 0
                        ";

                        $pendiente = \DB::select(\DB::raw($consulta));

                        $ot = OrdenesTrabajo::find($ordenTrabajoId);
                        if(count($pendiente) == 0) {
                            $ot->OT_CMM_Estatus = 'F860806C-B1EC-4047-AA95-EDAD406DE10E'; // Recibo Completo
                        } else {
                            $ot->OT_CMM_Estatus = '213ED3B9-12B3-41C9-8C6E-230DC86BBF90'; // Recibo Parcial
                        }
                        $ot->save();
                    }
          
                }

                $bul = Bultos::find($bultoId);
                $bul->BUL_CMM_EstatusBultoId = self::RECIBO_COMPLETO;
                $bul->BUL_ALM_AlmacenId = $almacenId;
                $bul->BUL_LOC_LocalidadId = $localidadId;
                $bul->save();
            }

            \DB::commit();

            $ajaxData = array();
            $ajaxData['codigo'] = 200;
            return json_encode($ajaxData);
        } 
        catch (\Exception $e){
            \DB::rollback();

            if($e->getCode() == '124') {
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

    public function guardaTransferenciaMovto($movimientos, $tabla)
    {
        $traspasoMovto = new TraspasosMovtos();
        $traspasoMovto->TRAM_ART_ArticuloId = $tabla->BULD_ART_ArticuloId;
        $traspasoMovto->TRAM_CantidadATraspasar = $movimientos->CANTIDAD;
        $traspasoMovto->TRAM_CMM_TipoTransferenciaId = self::RECIBO_OT;
        $traspasoMovto->TRAM_Razon = $tabla->BUL_NumeroBulto;
        $traspasoMovto->TRAM_Referencia = $tabla->BULD_BUL_BultoId;
        $traspasoMovto->TRAM_ReferenciaMovtoId = $tabla->BULD_OT_OrdenTrabajoId;

        $cantidadPartida = $movimientos->CANTIDAD;
        $almacenID = $movimientos->ALMACEN_ID;
        $localidadID = $movimientos->LOCALIDAD_ID;
        $loteID = $movimientos->LOTE_ID;

        if ($cantidadPartida > 0) {
            $dmi = array();
            array_push($dmi, new DetallesMovimientoInventario());

            $dmi[0]->setCantidadTransferir($movimientos->CANTIDAD);
            $dmi[0]->setIdAlmacen($almacenID);

            $articulo = Articulos::find($tabla->BULD_ART_ArticuloId);
            $codigoArticulo = $articulo->ART_CodigoArticulo;
            $nombreArticulo = $articulo->ART_Nombre;

            if($articulo->ART_Eliminado == 0 && $articulo->ART_Activo == 0){
                throw new \Exception("El artículo ($codigoArticulo) $nombreArticulo esta inactivo.");
            }

            if($articulo->ART_Eliminado == 1 && $articulo->ART_Activo == 0){
                throw new \Exception("El artículo ($codigoArticulo) $nombreArticulo esta eliminado.");
            }

            if ($articulo->ART_SeguimientoLocMult == 1) {
                $localidad = new Localidades();
                $localidad->COL_LOCALIDAD_ID = $localidadID;
                $dmi[0]->setLocalidad($localidad);
            } else {
                throw new \Exception("El artículo ($codigoArticulo) $nombreArticulo no tiene seguimiento a localidades multiples");
            }

            if ($articulo->ART_SeguimientoLotMult == 1) {
                $lote = new Lotes();
                $lote->COL_LOTE_ID = $loteID;
                $dmi[0]->setLote($lote);
            } else {
                throw new \Exception("El artículo ($codigoArticulo) $nombreArticulo no tiene seguimiento a lotes multiples");
            }
        }

        return ProcesadorMovimientoInventarios::registraMovimientoEnInventario($traspasoMovto, $dmi, null);
    }

    public function reciboBultoMovil(){
        \DB::beginTransaction();
        
        //$jsonRecibo = json_decode(Request::input('reciboBulto'), true);

        //$jsonRecibo = json_decode('{"Bulto":" 2905","AlmacenId":"0B2FFBB7-44A4-485F-A2D4-792E281591E5","LocalidadId":"365B6847-1A80-4112-8182-D88315FC0873","EmpleadoId":"D117CCA7-7114-4B55-9EEB-9F8553BF6179"}', true);

        //date_default_timezone_set('America/Mexico_City');

        //file_put_contents("logs/reciboBulto.txt", date("Y-m-d | h:i:sa")." -->  ".Request::input('reciboBulto')."\r\n",FILE_APPEND);

        try{
            ini_set('memory_limit', '-1');
            set_time_limit(0);

            //date_default_timezone_set('America/Mexico_City');
            $fecha = date('Ymd H:i:s');
            //$bultoId =  trim($jsonRecibo['BultoId']);
            //$complemento = $jsonRecibo['complemento'];
            $numeroBulto = Request::input('BultoNum'); //trim($jsonRecibo['BultoNum']);
            $almacenId = "0B2FFBB7-44A4-485F-A2D4-792E281591E5";//$jsonRecibo['AlmacenId'];
            $localidadId = "62EAAF01-1020-4C75-9503-D58B07FFC6EF";//$jsonRecibo['LocalidadId'];
            //$empleadoId = $jsonRecibo['EmpleadoId'];
           // $userdata = auth()->user();
            $empleadoId = DataBaseSession::getEmpleadoId();
           
            if(empty($numeroBulto)) {
                return response()->json(['error' => 'El número del bulto no puede estar vacío.'], 401);
                //throw new \Exception("El número del bulto no puede estar vacío.");
            }

            // if(empty($almacenId)) {
            //     throw new \Exception("El almacén no puede estar vacío.");
            // }

            // if(empty($localidadId)) {
            //     throw new \Exception("La localidad no puede estar vacío.");
            // }

            
            $bul = Bultos::where('BUL_NumeroBulto', '=', $numeroBulto)->join("ControlesMaestrosMultiples", "BUL_CMM_EstatusBultoId", "=", "CMM_ControlId")->first();
            if(is_null($bul)) {
                return response()->json(['error' => 'No se encontró el bulto.'], 401);
                //throw new \Exception("No se encontró el bulto $numeroBulto");
            }
            else{
                if($bul->CMM_Valor != "Abierto"){
                    return response()->json(['error' => "El bulto $numeroBulto es " . $bul->CMM_Valor], 401);
                    //throw new \Exception("El bulto $numeroBulto es ". $bul->CMM_Valor);
                }
            }
            
            //$bultoDetalle = BultosDetalle::where('BULD_BUL_BultoId', '=', $bul->BUL_BultoId)->get();
            $bultoDetalle = BultosDetalle::select('BULD_BUL_BultoId')->where('BULD_BUL_BultoId', '=', $bul->BUL_BultoId)->count();
            //$complemento = count($bultoDetalle) == 0 ? 1 : 0;
            $complemento = $bultoDetalle == 0 ? 1 : 0;

            $bultoId = $bul->BUL_BultoId;
            
            if($complemento == 0) {
                $consulta = "
                    SELECT 
                          BULD_BUL_BultoId
                        , BULD_OT_OrdenTrabajoId
                        , BULD_ART_ArticuloId
                        , BULD_Cantidad - ISNULL(BOR_Cantidad, 0.0) AS CANTIDAD
                        , LOT_LoteId
                        , OT_Codigo
                        , BUL_NumeroBulto
                        , '(' + ART_CodigoArticulo + ') ' + ART_Nombre AS ARTICULO
                        , ABS(CHECKSUM(NEWID())) AS CodigoLote
                    FROM Bultos
                    INNER JOIN BultosDetalle ON BUL_BultoId = BULD_BUL_BultoId
                    INNER JOIN OrdenesTrabajo ON BULD_OT_OrdenTrabajoId = OT_OrdenTrabajoId
                    INNER JOIN Articulos ON BULD_ART_ArticuloId = ART_ArticuloId
                    LEFT  JOIN (
                                SELECT 
                                      BOR_BUL_BultoId
                                    , BOR_OT_OrdenTrabajoId
                                    , BOR_ART_ArticuloId
                                    , SUM(BOR_Cantidad) AS BOR_Cantidad
                                FROM BultoOTRecibo
                                GROUP BY 
                                      BOR_BUL_BultoId
                                    , BOR_OT_OrdenTrabajoId
                                    , BOR_ART_ArticuloId
                               ) AS BultoOTRecibo ON BULD_BUL_BultoId = BOR_BUL_BultoId 
                                    AND BULD_OT_OrdenTrabajoId = BOR_OT_OrdenTrabajoId 
                                    AND BULD_ART_ArticuloId = BOR_ART_ArticuloId
                    LEFT  JOIN (
                                SELECT 
                                      LOT_LoteId
                                    , LOT_ART_ArticuloId
                                    , ROW_NUMBER() OVER(PARTITION BY LOT_ART_ArticuloId ORDER BY LOT_LoteId, LOT_FechaCreacion DESC) AS FILA
                                FROM Lotes
                                WHERE LOT_Eliminado = 0
                                ) AS Lotes ON BULD_ART_ArticuloId = LOT_ART_ArticuloId AND FILA = 1
                    WHERE BULD_Eliminado = 0
                          AND BULD_BUL_BultoId = '$bultoId'
                    GROUP BY 
                          BULD_BUL_BultoId
                        , BULD_OT_OrdenTrabajoId
                        , BULD_ART_ArticuloId
                        , BULD_Cantidad
                        , BOR_Cantidad
                        , LOT_LoteId
                        , OT_Codigo
                        , BUL_NumeroBulto
                        , ART_CodigoArticulo
                        , ART_Nombre
                    HAVING BULD_Cantidad - ISNULL(BOR_Cantidad, 0.0) > 0 ";

                $bulto = \DB::select($consulta);

                for ($x = 0; $x < count($bulto); $x++) {
                    $movimientos = new \stdClass();
                    $movimientos->ALMACEN_ID = $almacenId;
                    $movimientos->LOCALIDAD_ID = $localidadId;
                    $movimientos->LOTE_ID = $bulto[$x]->LOT_LoteId;
                    $movimientos->CANTIDAD = $bulto[$x]->CANTIDAD;

                    $ot = $bulto[$x]->OT_Codigo;
                    $ordenTrabajoId = $bulto[$x]->BULD_OT_OrdenTrabajoId;

                    if(is_null($movimientos->LOTE_ID)){
                        $lote = new Lotes();
                        $lote->LOT_LoteId = $this->dao->nuevoId();
                        $lote->LOT_NumeroLote = '';
                        $lote->LOT_ART_ArticuloId = $bulto[$x]->BULD_ART_ArticuloId;
                        $lote->LOT_CodigoLote = $bulto[$x]->CodigoLote;
                        $lote->LOT_FechaCreacion = $fecha;
                        $lote->LOT_FechaUltimaModificacion = $fecha;
                        $lote->LOT_Eliminado = 0;
                        $lote->LOT_EMP_ModificadoPorId = $empleadoId;
                        $lote->LOT_CMM_EstatusLoteId = '362B0AC5-85A1-4DB1-A725-DA1C64702E7D';
                        $lote->LOT_FechaLote = $fecha;
                        $lote->LOT_LoteManual = 0;
                        $lote->LOT_Cerrado = 0;
                        $lote->LOT_MateriaPrima = 0;
                        $lote->LOT_OT_OrdenTrabajoId = $ordenTrabajoId;
                        $lote->save();

                        $movimientos->LOTE_ID = $lote->LOT_LoteId;
                    }

                    $traspasoMovtoId = self::guardaTransferenciaMovto($movimientos, $bulto[$x]);

                    $bor = new BultoOTRecibo();
                    $bor->BOR_BultoOTReciboId = $this->dao->nuevoId();
                    $bor->BOR_OT_OrdenTrabajoId = $ordenTrabajoId;
                    $bor->BOR_BUL_BultoId = $bultoId;
                    $bor->BOR_ART_ArticuloId = $bulto[$x]->BULD_ART_ArticuloId;
                    $bor->BOR_FechaRecibo = $fecha;
                    $bor->BOR_Cantidad = $bulto[$x]->CANTIDAD;
                    $bor->BOR_EMP_CreadoPorId = $empleadoId;
                    $bor->BOR_EMP_ModificadoPorId = $empleadoId;
                    $bor->BOR_TRAM_TraspasoMovtoId = $traspasoMovtoId;
                    $bor->save();

                    $consulta = "
                        SELECT OT_OrdenTrabajoId, OTDA_ART_ArticuloId, OTDA_Cantidad
                        FROM OrdenesTrabajo
                        INNER JOIN OrdenesTrabajoDetalleArticulos ON OT_OrdenTrabajoId = OTDA_OT_OrdenTrabajoId
                        LEFT  JOIN (
                                    SELECT 
                                        BOR_OT_OrdenTrabajoId
                                        , BOR_ART_ArticuloId
                                        , SUM(BOR_Cantidad) AS BOR_Cantidad
                                    FROM BultoOTRecibo
                                    GROUP BY 
                                        BOR_OT_OrdenTrabajoId
                                        , BOR_ART_ArticuloId
                                    ) AS BultoOTRecibo ON OT_OrdenTrabajoId = BOR_OT_OrdenTrabajoId 
                                                        AND OTDA_ART_ArticuloId = BOR_ART_ArticuloId
                        WHERE OT_OrdenTrabajoId = '$ordenTrabajoId'
                        GROUP BY OT_OrdenTrabajoId, OTDA_ART_ArticuloId, OTDA_Cantidad, BOR_Cantidad
                        HAVING OTDA_Cantidad - ISNULL(BOR_Cantidad, 0.0) > 0
                    ";

                    $pendiente = \DB::select($consulta);

                    $ot = OrdenesTrabajo::find($ordenTrabajoId);
                    if(count($pendiente) == 0) {
                        $ot->OT_CMM_Estatus = 'F860806C-B1EC-4047-AA95-EDAD406DE10E'; // Recibo Completo
                    } else {
                        $ot->OT_CMM_Estatus = '213ED3B9-12B3-41C9-8C6E-230DC86BBF90'; // Recibo Parcial
                    }
                    $ot->save();
                }
      
            }

            $bul = Bultos::find($bultoId);
            $bul->BUL_CMM_EstatusBultoId = self::RECIBO_COMPLETO;
            $bul->BUL_ALM_AlmacenId = $almacenId;
            $bul->BUL_LOC_LocalidadId = $localidadId;
            $bul->save();

            \DB::commit();
            // TODO BIEN
            echo json_encode(
                [
                    "Respuesta" => [
                        [
                            'InformacionError' => [],
                            'Estatus' => 'Procesado',
                            'Mensaje' => "La transacción fue realizada exitosamente."
                        ]
                    ]
                ]
            );
        } catch (\Exception $e) {
            \DB::rollback();
           echo json_encode(array(
                "mensaje" => $e->getMessage(),
                "codigo" => $e->getCode(),
                "clase" => $e->getFile(),
                "linea" => $e->getLine()
           ));
        }

    }

}
