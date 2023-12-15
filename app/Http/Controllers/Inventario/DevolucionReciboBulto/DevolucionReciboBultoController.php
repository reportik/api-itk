<?php namespace App\Http\Controllers\Inventario\DevolucionReciboBulto;

use App\Http\Controllers\CFDI\EncabezadoPDF;
use App\Http\Controllers\Sistema\AutonumericoController;
use App\Http\Controllers\Sistema\DataBaseSession;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Sistema\DAOGeneralController;
use Illuminate\Support\Facades\Request as NewRequest;
use App\Mapeos\Controles\ControlesMaestrosMultiples;
use App\Models\Bultos;
use App\Models\BultoOTRecibo;
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

class DevolucionReciboBultoController extends Controller {

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

        //date_default_timezone_set('America/Mexico_City');
        $fecha = date('d/m/Y');

        return view('Inventario.DevolucionReciboBulto.buscadorDevolucionReciboBulto',compact('version','cantidad_decimales','precios_decimales','porcentaje_decimales','tc_decimales','fecha'));

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
						BOR_BultoOTReciboId AS DT_RowId
						,BUL_NumeroBulto
						,'(' + ART_CodigoArticulo + ')' + ' ' + ART_Nombre AS ART_Nombre
						,OT_Codigo	 
						,CAST(BOR_FechaRecibo AS DATE) AS BOR_FechaRecibo
						,SUM(BOR_Cantidad) AS BOR_Cantidad
						,'(' + C.EMP_CodigoEmpleado + ')' + ' ' + C.EMP_Nombre + ' ' + C.EMP_PrimerApellido + ' ' + C.EMP_SegundoApellido AS EMP_CreadoPor
						,'(' + M.EMP_CodigoEmpleado + ')' + ' ' + M.EMP_Nombre + ' ' + M.EMP_PrimerApellido + ' ' + M.EMP_SegundoApellido AS EMP_ModificadoPor
						,0 AS CHECK_BOX
						,TIPO.CMM_Valor AS BUL_TipoBulto
					FROM BultoOTRecibo
					INNER JOIN Bultos ON BUL_BultoId = BOR_BUL_BultoId
					INNER JOIN ControlesMaestrosMultiples TIPO ON TIPO.CMM_ControlId = BUL_CMM_TipoBultoId
					INNER JOIN OrdenesTrabajo ON OT_OrdenTrabajoId = BOR_OT_OrdenTrabajoId
					INNER JOIN Articulos ON ART_ArticuloId = BOR_ART_ArticuloId
					INNER JOIN Empleados C ON C.EMP_EmpleadoId = BOR_EMP_CreadoPorId
					INNER JOIN Empleados M ON M.EMP_EmpleadoId = BOR_EMP_ModificadoPorId
                    LEFT  JOIN (
                                SELECT 
                                	BUL_BultoId AS ID_BULTO
                                	,PREBD_Embarcado
                                FROM Bultos
                                LEFT  JOIN BultosDetalle ON BUL_BultoId = BULD_BUL_BultoId AND BULD_Eliminado = 0
                                INNER JOIN (
                                            SELECT 
                                                PREB_PreembarqueBultoId
                                                , PREBD_BUL_BultoId
                                                , PREBD_BULD_BultoDetalleId
                                                , PREBD_Embarcado
                                            FROM PreembarqueBulto
                                            INNER JOIN PreembarqueBultoDetalle ON PREB_PreembarqueBultoId = PREBD_PREB_PreembarqueBultoId AND PREBD_Eliminado = 0
                                            WHERE PREB_Eliminado = 0
                                        ) AS Preembarque ON BUL_BultoId = PREBD_BUL_BultoId OR BULD_BultoDetalleId = PREBD_BULD_BultoDetalleId
                                WHERE BUL_Eliminado = 0
                                AND PREBD_Embarcado = 0
                            ) AS Preembarque ON BUL_BultoId = ID_BULTO
					--WHERE CAST(BOR_FechaRecibo AS DATE) BETWEEN '".$FechaInicio."' AND '".$FechaFinal."'
					WHERE BOR_Cantidad > 0
					AND (BUL_CMM_EstatusBultoId = 'F742508D-9B5B-4B8E-9F43-AE5C31ADD7DF'
						OR BUL_CMM_EstatusBultoId = 'EB967196-EF77-49A5-82B8-57DAC0ABD632')--Recibo Completo,Recibo Parcial
                    AND ID_BULTO IS NULL
                    AND (PREBD_Embarcado IS NULL OR PREBD_Embarcado = 0)
					GROUP BY
						BOR_BultoOTReciboId
						,BUL_NumeroBulto
						,'(' + ART_CodigoArticulo + ')' + ' ' + ART_Nombre
						,OT_Codigo	 
						,BOR_FechaRecibo
						--,BOR_Cantidad
						,'(' + C.EMP_CodigoEmpleado + ')' + ' ' + C.EMP_Nombre + ' ' + C.EMP_PrimerApellido + ' ' + C.EMP_SegundoApellido
						,'(' + M.EMP_CodigoEmpleado + ')' + ' ' + M.EMP_Nombre + ' ' + M.EMP_PrimerApellido + ' ' + M.EMP_SegundoApellido	
						,TIPO.CMM_Valor
					--ORDER BY 
						--BUL_NumeroBulto
					--DESC

					UNION ALL

					SELECT
						BUL_BultoId AS DT_RowId
						,BUL_NumeroBulto
						,NULL AS ART_Nombre
						,NULL AS OT_Codigo	 
						,CAST(BUL_FechaCreacion AS DATE) AS BOR_FechaRecibo
						,1 AS BOR_Cantidad
						,'(' + C.EMP_CodigoEmpleado + ')' + ' ' + C.EMP_Nombre + ' ' + C.EMP_PrimerApellido + ' ' + C.EMP_SegundoApellido AS EMP_CreadoPor
						,'(' + M.EMP_CodigoEmpleado + ')' + ' ' + M.EMP_Nombre + ' ' + M.EMP_PrimerApellido + ' ' + M.EMP_SegundoApellido AS EMP_ModificadoPor
						,0 AS CHECK_BOX	
						,TIPO.CMM_Valor AS BUL_TipoBulto
					FROM Bultos
					LEFT JOIN BultosDetalle ON BULD_BUL_BultoId = BUL_BultoId
					INNER JOIN ControlesMaestrosMultiples TIPO ON TIPO.CMM_ControlId = BUL_CMM_TipoBultoId
					INNER JOIN Empleados C ON C.EMP_EmpleadoId = BUL_EMP_CreadoPorId
					LEFT JOIN Empleados M ON M.EMP_EmpleadoId = BUL_EMP_ModificadoPorId
					WHERE /*CAST(BUL_FechaCreacion AS DATE) BETWEEN '".$FechaInicio."' AND '".$FechaFinal."'
					AND*/ BUL_Eliminado = 0
					AND BUL_CMM_TipoBultoId = 'A00E0707-1CC9-4F59-8BA6-CD1DC4D82DD4'--COMPLEMENTO
					AND (BUL_CMM_EstatusBultoId = 'F742508D-9B5B-4B8E-9F43-AE5C31ADD7DF'
						OR BUL_CMM_EstatusBultoId = 'EB967196-EF77-49A5-82B8-57DAC0ABD632')--Recibo Completo,Recibo Parcial"
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

    public function registraDevolucionReciboBulto(){

        \DB::beginTransaction();

        try{

            ini_set('memory_limit', '-1');
            set_time_limit(0);

            //date_default_timezone_set('America/Mexico_City');            
            $hoy=date('d-m-Y H:i:s');
            $dia = date('d');
            $mes = date('m');
            
            $TablaDetalle = isset($_POST['TablaDetalle']) ? json_decode($_POST['TablaDetalle'], true) : array();
            $empleadoId = DataBaseSession::getEmpleadoId();                

            $cuentaTablaDetalle = count($TablaDetalle);
            for($x = 0; $x < $cuentaTablaDetalle; $x ++){

            	if ($TablaDetalle[$x]['tipoBulto'] != 'PRINCIPAL') {

            		$bulto = Bultos::find($TablaDetalle[$x]['reciboId']);            		
            		$bulto->BUL_CMM_EstatusBultoId = 'A1DDDA80-4B1C-4F72-AA48-BF69C66B64BA';//Abierto
					$bulto->BUL_FechaUltimaModificacion = $hoy;
					$bulto->BUL_EMP_ModificadoPorId = $empleadoId;
					$bulto->save();

            	}
            	else{

            		$consulta = \DB::select(
		                \DB::raw(
		                    "SELECT
								BOR_OT_OrdenTrabajoId
								,BOR_BUL_BultoId
								,BOR_ART_ArticuloId
								,BOR_Cantidad
								,BOR_TRAM_TraspasoMovtoId
								,CAST(BOR_FechaRecibo AS DATE) AS BOR_FechaRecibo
								,TRAM_ART_ArticuloId
								,TRAM_CantidadATraspasar
								,TRAM_Razon
								,TRAM_Referencia
								,TRAM_UnidadMedidadArt
								,TRAM_CantidadAMano
								,TRAM_CantidadAManoConTraspaso
								,LOTL_LOT_LoteId
								,LOTL_LOC_LocalidadId
								,LOC_ALM_AlmacenId	
								--*	 
							FROM BultoOTRecibo 
							INNER JOIN TraspasosMovtos ON TRAM_TraspasoMovtoId = BOR_TRAM_TraspasoMovtoId
							INNER JOIN TraspasosLotes ON TRLOT_TRAM_TraspasoMovtoId = TRAM_TraspasoMovtoId
							INNER JOIN LotesLocalidades ON LOTL_LoteLocalidadId = TRLOT_LOTL_LoteLocalidadId
							INNER JOIN Localidades ON LOC_LocalidadId = LOTL_LOC_LocalidadId
							WHERE BOR_BultoOTReciboId = '".$TablaDetalle[$x]['reciboId']."'"
		                )
		            );

									

					$bulto = Bultos::find($consulta[0]->BOR_BUL_BultoId);

					if($bulto->BUL_CMM_TipoBultoId == 'CDBBF4F2-3A62-475B-A0AB-B235496DFE7D'){//PRINCIPAL
						
						$divide = explode("-", $consulta[0]->BOR_FechaRecibo);
		            
		            	//INSERA EN TABLA RECIBOS
						$recibo = new BultoOTRecibo();
						$recibo->BOR_BultoOTReciboId = self::getNuevoId();             	
						$recibo->BOR_OT_OrdenTrabajoId = $consulta[0]->BOR_OT_OrdenTrabajoId;
						$recibo->BOR_BUL_BultoId = $consulta[0]->BOR_BUL_BultoId;
						$recibo->BOR_ART_ArticuloId = $consulta[0]->BOR_ART_ArticuloId;
						$recibo->BOR_FechaRecibo = $divide[2].'-'.$divide[1].'-'.$divide[0];
						$recibo->BOR_Cantidad = (-1 * $consulta[0]->BOR_Cantidad);
						$recibo->BOR_EMP_CreadoPorId = $empleadoId;				
						$recibo->BOR_EMP_ModificadoPorId = $empleadoId;

						//GUARDA TRASPASO MOVTO
			            $TraspasosMovtos = new TraspasoMovto();	            	           
			            $TraspasosMovtos->TRAM_ART_ArticuloId = $consulta[0]->TRAM_ART_ArticuloId;
			            //$TraspasosMovtos->TRAM_CantidadATraspasar = (-1 * floatval($TablaDetalle[$x]['cantidad']));
			            $TraspasosMovtos->TRAM_CantidadATraspasar = (-1 * $consulta[0]->TRAM_CantidadATraspasar);
			            $TraspasosMovtos->TRAM_Razon = "Devolucion Recibo Bulto: ".$consulta[0]->TRAM_Razon;
			            //$TraspasosMovtos->TRAM_Referencia = "Embarque web: ".$TablaDetalle[$x]['ART_CodigoArticulo']." Cantidad: ".floatval($TablaDetalle[$x]['cantidad']);
			            $TraspasosMovtos->TRAM_Referencia = "Devolucion Recibo Bulto: ".$consulta[0]->TRAM_Razon." Cantidad: -".$consulta[0]->TRAM_CantidadATraspasar;
			            $TraspasosMovtos->TRAM_UnidadMedidadArt = $consulta[0]->TRAM_UnidadMedidadArt;
			            $TraspasosMovtos->TRAM_EstatusContable = false;
			            $TraspasosMovtos->TRAM_CantidadAMano = $consulta[0]->TRAM_CantidadAMano;
			            //$TraspasosMovtos->TRAM_CantidadAManoConTraspaso = $TablaDetalle[$x]['ART_CantidadAMano'] + floatval($TablaDetalle[$x]['cantidad']);
			            $TraspasosMovtos->TRAM_CantidadAManoConTraspaso = $consulta[0]->TRAM_CantidadAMano + $consulta[0]->TRAM_CantidadATraspasar;
			            $TraspasosMovtos->TRAM_CMM_TipoTransferenciaId = 'DBAF012D-FCE9-4BBD-B188-66B9580C5FA4';//Devolucion Recibo Bulto
			            $TraspasosMovtos->TRAM_ReferenciaMovtoId = $recibo->BOR_BultoOTReciboId;	            
			            //$TraspasosMovtos->save();

			            //LLENA OBJETO PARA ENVIAR A PROCESADOR
			            $arrayDetallesMovimiento = array();
			            $dmi = new DetallesMovimientoInventario();

			            $dmi->setCantidadTransferir($TraspasosMovtos->TRAM_CantidadATraspasar);
			            $dmi->setIdAlmacen($consulta[0]->LOC_ALM_AlmacenId);

			            $localidad = new Localidades();
			            $localidad->COL_LOCALIDAD_ID = $consulta[0]->LOTL_LOC_LocalidadId;		            
			            $dmi->setLocalidad($localidad);

			            $lotes = new Lotes();
			            $lotes->COL_LOTE_ID = $consulta[0]->LOTL_LOT_LoteId;
			            $dmi->setLote($lotes);

			            array_push($arrayDetallesMovimiento, $dmi);

			            //ENVIAR INFORMACION A PROCESADOR
			            $idTraspasoMovto =ProcesadorMovimientoInventarios::registraMovimientoEnInventario($TraspasosMovtos, $arrayDetallesMovimiento, null); 

			            $recibo->BOR_TRAM_TraspasoMovtoId = $idTraspasoMovto;				
						$recibo->save();					

					}
	        		
					$bulto->BUL_CMM_EstatusBultoId = 'A1DDDA80-4B1C-4F72-AA48-BF69C66B64BA';//Abierto
					$bulto->BUL_FechaUltimaModificacion = $hoy;
					$bulto->BUL_EMP_ModificadoPorId = $empleadoId;
					$bulto->save();

            	}            	

            }
                  
            $response = array("action" => "success");

            \DB::commit();

            return ['Status' => 'Valido', 'respuesta' => $response];

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


}	