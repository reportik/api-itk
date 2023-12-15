<?php
/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 07/03/2016
 * Time: 02:06 PM
 */

namespace App\Http\Controllers\Inventario;

use Illuminate\Support\Facades\Facade;
use App\Http\Controllers\Inventario\Articulos\ArticulosController;
use App\Http\Controllers\inventario\Localidades\LocalidadesArticuloController;
use App\Http\Controllers\inventario\Localidades\LocalidadesController;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Mapeos\Controles\ControlesMaestrosMultiples;
use App\Models\Articulos;
use App\Models\Inventario\Articulos\Articulo;
use App\Models\Inventario\InventarioFisico\DetallesMovimientoInventario;
use App\Models\InventarioFisico;
use App\Models\InventarioFisicoControl;
use App\Models\Inventario\InventarioFisico\TraspasoMovto;
use App\Models\inventario\Localidad;
use App\Models\inventario\TraspasosLocalidades;
use App\Models\InventarioFisicoDetalle;
use App\Models\Localidades;
use App\Models\Lotes;
use App\Models\LotesPallets;
use App\Models\LotesRecibos;
use App\Models\Traspasos;
use App\Models\TraspasosDetalle;
use App\Models\TraspasosSolicitudes;
use App\Models\TraspasosSolicitudesDetalle;
use Carbon\Carbon;
use App\Http\Controllers\Embarques\EmbarquesController;
use App\Models\Inventario\InventarioFisico\ProcesadorMovimientoInventarios;


class InventarioFisicoMovilController extends Controller {

    public function procesaInventarioFisico(){

//        $json = //json_encode(
//            [
//                "InventarioFisico" => [
//
//                        'Lotes' => [
//                            ['Lote' => 'RS05004',
//                                'Cantidad'=> 4
//                            ],
//                            ['Lote' => 'RR07001',
//                                'Cantidad'=> 9
//                            ],
//
//                            ['Lote' => 'RR07001',
//                                'Cantidad'=> 1
//                            ]
//                        ],
//                        'LocalidadId' => 'AD69F54E-D428-4960-B721-EB48CC3B6C3F',
//                        'EmpleadoId'=> '3A2D4A67-BB29-493B-BFB1-3A1A03310372',
//                        'Fecha'=> '28-08-2016',
//                          'IdControl'=> '711979BD-85F0-461A-AC34-75F29F3790BF'
//
//                ]
//            ]
//        //)
//        ;

//        $jsonResponse = json_encode(
//            [
//                "Respuesta" => [
//                    [
//                        'InformacionError' => [
//                            ['Lote' => $lote->LOT_CodigoLote,
//                                'Localidad' => $codigoLocalidad ." - ".$localidad->LOC_Nombre,
//                                'Articulo'=> $lote->ART_CodigoArticulo
//                            ]
//                        ],
//                        'Estatus' => 'Error',
//                        'Mensaje'=> "No es posible sacar la cantidad de ". abs($cantidadTraspasar) .", ya que su existencia es de " . $loteLocalidad[0]->LOTL_Cantidad . "."
//                    ]
//                ]
//            ]
//        );

        $jsonRecibo = json_decode(\Illuminate\Support\Facades\Request::input('InventarioFisico'), true);

        file_put_contents("inventarioFisico.txt", date("Y-m-d | h:i:sa")." -->  ".\Illuminate\Support\Facades\Request::input('InventarioFisico')."\r\n",FILE_APPEND);
        //dd($json);
        //$jsonRecibo = json_decode($json, true);

        //dd($jsonRecibo['Recibo']);
        // LotesRecibosController::guardaReciboLote($jsonRecibo['Recibo']);
        InventarioFisicoMovilController::guardaLecturas($jsonRecibo['InventarioFisico']);

        InventarioFisicoMovilController::guardaInventarioFisico($jsonRecibo['InventarioFisico']);

        //return $jsonTraspasos;

    }

    private function guardaLecturas($arrayIF){
        try{
            //date_default_timezone_set('America/Mexico_City');

            if(!isset($arrayIF['EmpleadoNombre']))return;
            $nombreEmpleado = isset($arrayIF['EmpleadoNombre']) ? $arrayIF['EmpleadoNombre'] : "N/A";
            $localidadId = isset($arrayIF['LocalidadId']) ? $arrayIF['LocalidadId'] : "N/A";


            $lines="";
            $ingresoSistema=date("Y-m-d | h:i:sa");
            if(isset($arrayIF['Lotes'])){
                $arrayLotes=$arrayIF['Lotes'];
                $longitud = count($arrayLotes);

                for ($i = 0; $i < $longitud; $i++) {
                    $lote= $arrayLotes[$i];
                    $ArticuloCodigo="";
                    extract($lote);

                    $lineLectura=  $ArticuloCodigo.";".$ArticuloNombre.";".$Lote.";".$Cantidad.";".$Fecha.";".$nombreEmpleado.";".$ingresoSistema.";".$localidadId."\n";
                    $lines .=$lineLectura;

                }
            }
            file_put_contents("inventarioFisicoLecturas.csv", $lines,FILE_APPEND);

        }catch (\Exception $e) {
            //throw $e;

        }
    }

    private function guardaInventarioFisico($arrayIF){



        try {

            if(isset($arrayIF['IdControl'])) {

                $IFC = InventarioFisicoControl::find($arrayIF['IdControl']);

                if (count($IFC) > 0) {

                    echo json_encode(
                        [
                            "Respuesta" => [
                                [
                                    'InformacionError' => [],
                                    'Estatus' => 'Error',
                                    'Mensaje' => "No es posible enviar la información porque ya fue enviada anteriormente."
                                ]
                            ]
                        ]
                    );

                } else {


                    InventarioFisicoMovilController::registraInventarioFisico($arrayIF['Lotes']
                        , $arrayIF['EmpleadoId']
                        , $arrayIF['LocalidadId']
                        , $arrayIF['Fecha']
                        , $arrayIF['IdControl']
                    );


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

                }
            }
            else{

                InventarioFisicoMovilController::registraInventarioFisico($arrayIF['Lotes']
                    , $arrayIF['EmpleadoId']
                    , $arrayIF['LocalidadId']
                    , $arrayIF['Fecha']
                    , null
                );


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

            }

        } catch (\Exception $e) {
            \DB::rollback();
            echo json_encode(
                [
                    "Respuesta" => [
                        [
                            'InformacionError' => [],
                            'Estatus' => 'Error',
                            'Mensaje'=> "Error: " .$e->getMessage()
                        ]
                    ]
                ]
            );
        }

    }

    private function registraInventarioFisico($arrayLotes, $empleadoId, $localidadId, $fecha, $idControl){

        try {

            //date_default_timezone_set('America/Mexico_City');
            $hoy=date('d/m/Y');

            $inventarioFisicoExistente = InventarioFisico::whereRaw("CAST(IF_FechaInventario AS DATE) = '".$hoy."'")->get();

            if(count($inventarioFisicoExistente) > 0){
                $inventarioFisico = InventarioFisico::find($inventarioFisicoExistente[0]->IF_InventarioFisicoId);
                $bloqueadoIFD = InventarioFisicoDetalle::select('IFD_Bloqueado')
                    ->where('IFD_IF_InventarioFisicoId', '=', $inventarioFisico->IF_InventarioFisicoId)
                    ->where('IFD_LOC_LocalidadId', '=', $localidadId)
                    ->groupBy('IFD_Bloqueado')
                    ->get();

                if(count($bloqueadoIFD) > 0){

                    if(count($bloqueadoIFD) > 1){
                        throw new \Exception("No es posible realizar la transacción. La fecha de inventario y la localidad elegida ya ha sido bloqueado.");
                    }
                    else{

                        if($bloqueadoIFD[0]->IFD_Bloqueado == "1"){
                            throw new \Exception("No es posible realizar la transacción. La fecha de inventario y la localidad elegida ya ha sido bloqueado.");
                        }

                    }

                }

            }
            else{
                $inventarioFisico = new InventarioFisico();
                $inventarioFisico->IF_InventarioFisicoId = EmbarquesController::getNuevoId();
                $inventarioFisico->IF_FechaInventario = $hoy;
                $inventarioFisico->IF_EMP_EmpleadoCreadorId = $empleadoId;
            }

            \DB::beginTransaction();
            $inventarioFisico->IF_EMP_ModificadoPorId = $empleadoId;

            $inventarioFisico->save();

            if($idControl != null) {
                $inventarioControl = new InventarioFisicoControl();
                $inventarioControl->IFC_Id = $idControl;
                $inventarioControl->IFC_IF_InventarioFisicoId = $inventarioFisico->IF_InventarioFisicoId;
                $inventarioControl->save();
            }

            \DB::commit();

            $almacenId = Localidades::find($localidadId)->LOC_ALM_AlmacenId;

            $longitud = count($arrayLotes);

            for ($i = 0; $i < $longitud; $i++) {

                $lote = Lotes::find(LotesController::getIdLotePorCodigo($arrayLotes[$i]['Lote']));

                if ($lote == null) {
                    throw new \Exception("No es posible realizar la transacción. El lote " . $arrayLotes[$i]['Lote'] . " no existe.");
                } else {

                    $inventarioFisicoExistente = InventarioFisico::whereRaw("CAST(IF_FechaInventario AS DATE) = '".$hoy."'")->get();

                    \DB::beginTransaction();

                    if(count($inventarioFisicoExistente) > 0){

                        $detalleExistente = InventarioFisicoDetalle::where('IFD_LOT_LoteId', '=', $lote->LOT_LoteId)
                            ->where('IFD_IF_InventarioFisicoId', '=', $inventarioFisico->IF_InventarioFisicoId)
                            ->where('IFD_LOC_LocalidadId', '=', $localidadId)
                            ->get();

                        if(count($detalleExistente) > 0){

                            $cantidadAnterior = Lotes::select('LOTL_Cantidad')
                                ->leftJoin('LotesLocalidades', 'LOTL_LOT_LoteId', '=', 'LOT_LoteId')
                                ->where('LOT_LoteId', '=', $lote->LOT_LoteId)
                                ->get()[0]->LOTL_Cantidad;

                            $inventarioFisicoDetalle = InventarioFisicoDetalle::find($detalleExistente[0]->IFD_InventarioFisicoDetId);
                            $inventarioFisicoDetalle->IFD_IF_InventarioFisicoId = $inventarioFisico->IF_InventarioFisicoId;
                            $inventarioFisicoDetalle->IFD_CantidadContada = $arrayLotes[$i]['Cantidad'] + $inventarioFisicoDetalle->IFD_CantidadContada;
                            $inventarioFisicoDetalle->IFD_ART_ArticuloId = $lote->LOT_ART_ArticuloId;
                            $inventarioFisicoDetalle->IFD_CantidadAnterior = $cantidadAnterior == null ? 0 : $cantidadAnterior;
                            $inventarioFisicoDetalle->IFD_ALM_AlmacenId = $almacenId;
                            $inventarioFisicoDetalle->IFD_LOC_LocalidadId = $localidadId;
                            $inventarioFisicoDetalle->IFD_LOT_LoteId = $lote->LOT_LoteId;
                            //$inventarioFisicoDetalle->IFD_FechaConteo = $hoy;
                            $inventarioFisicoDetalle->IFD_EMP_ResponsableId = $empleadoId;
                            $inventarioFisicoDetalle->IFD_EMP_ModificadoPorId = $empleadoId;

                            $inventarioFisicoDetalle->save();

                        }
                        else{
                            $cantidadAnterior = Lotes::select('LOTL_Cantidad')
                                ->leftJoin('LotesLocalidades', 'LOTL_LOT_LoteId', '=', 'LOT_LoteId')
                                ->where('LOT_LoteId', '=', $lote->LOT_LoteId)
                                ->get()[0]->LOTL_Cantidad;

                            $inventarioFisicoDetalle = new InventarioFisicoDetalle();
                            $inventarioFisicoDetalle->IFD_IF_InventarioFisicoId = $inventarioFisico->IF_InventarioFisicoId;
                            $inventarioFisicoDetalle->IFD_ART_ArticuloId = $lote->LOT_ART_ArticuloId;
                            $inventarioFisicoDetalle->IFD_CantidadContada = $arrayLotes[$i]['Cantidad'];
                            $inventarioFisicoDetalle->IFD_CantidadAnterior = $cantidadAnterior == null ? 0 : $cantidadAnterior;
                            $inventarioFisicoDetalle->IFD_ALM_AlmacenId = $almacenId;
                            $inventarioFisicoDetalle->IFD_LOC_LocalidadId = $localidadId;
                            $inventarioFisicoDetalle->IFD_LOT_LoteId = $lote->LOT_LoteId;
                            //$inventarioFisicoDetalle->IFD_FechaConteo = $hoy;
                            $inventarioFisicoDetalle->IFD_EMP_ResponsableId = $empleadoId;
                            $inventarioFisicoDetalle->IFD_EMP_ModificadoPorId = $empleadoId;

                            $inventarioFisicoDetalle->save();
                        }


                    }
                    else{

                        $cantidadAnterior = Lotes::select('LOTL_Cantidad')
                            ->leftJoin('LotesLocalidades', 'LOTL_LOT_LoteId', '=', 'LOT_LoteId')
                            ->where('LOT_LoteId', '=', $lote->LOT_LoteId)
                            ->get()[0]->LOTL_Cantidad;

                        $inventarioFisicoDetalle = new InventarioFisicoDetalle();
                        $inventarioFisicoDetalle->IFD_IF_InventarioFisicoId = $inventarioFisico->IF_InventarioFisicoId;
                        $inventarioFisicoDetalle->IFD_ART_ArticuloId = $lote->LOT_ART_ArticuloId;
                        $inventarioFisicoDetalle->IFD_CantidadContada = $arrayLotes[$i]['Cantidad'];
                        $inventarioFisicoDetalle->IFD_CantidadAnterior = $cantidadAnterior == null ? 0 : $cantidadAnterior;
                        $inventarioFisicoDetalle->IFD_ALM_AlmacenId = $almacenId;
                        $inventarioFisicoDetalle->IFD_LOC_LocalidadId = $localidadId;
                        $inventarioFisicoDetalle->IFD_LOT_LoteId = $lote->LOT_LoteId;
                        $inventarioFisicoDetalle->IFD_FechaConteo = $fecha;
                        $inventarioFisicoDetalle->IFD_EMP_ResponsableId = $empleadoId;
                        $inventarioFisicoDetalle->IFD_EMP_ModificadoPorId = $empleadoId;

                        $inventarioFisicoDetalle->save();
                    }

                    \DB::commit();

                }

            }

        }
        catch(\Exception $e){
            throw $e;
        }

    }

} 