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
use App\Models\Inventario\InventarioFisico\TraspasoMovto;
use App\Models\inventario\Localidad;
use App\Models\inventario\TraspasosLocalidades;
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


class LotesRecibosController extends Controller {

    public function procesaReciboLote(){

//        $json = json_encode(
//            [
//                "Recibo" =>
//                    [
//                        'Lote' => '350JA300806',
//                        'CantidadRecibo'=> 11,
//                        'ReciboCompleto' => 0,
//                        'Pallets' => ["1","2", "4"],
//                        'EmpleadoId' => '',
//                        'ReciboId' => '',
//                    ]
//
//          ]
//        );

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

        $jsonRecibo = json_decode(\Illuminate\Support\Facades\Request::input('recibo'), true);

        //dd($json);
        //$jsonRecibo = json_decode($json, true);

        //dd($jsonRecibo['Recibo']);
        // LotesRecibosController::guardaReciboLote($jsonRecibo['Recibo']);

        file_put_contents("logs/ReciboLote.txt", date("Y-m-d | h:i:sa")." -->  ".\Illuminate\Support\Facades\Request::input('recibo')."\r\n",FILE_APPEND);

        LotesRecibosController::guardaReciboLote($jsonRecibo['Recibo']);

        //return $jsonTraspasos;

    }

    private function guardaReciboLote($arrayRecibo){

        \DB::beginTransaction();

        try {

            $lote = Lotes::find(LotesController::getIdLotePorCodigo($arrayRecibo['Lote']));

            $duplicado = $this->isDuplicado($lote->LOT_LoteId, $arrayRecibo['Pallets']);

            if($duplicado['Status'] == 'Duplicado'){

                echo json_encode(
                    [
                        "Respuesta" => [
                            [
                                'InformacionError' => [],
                                'Estatus' => 'Error',
                                'Mensaje'=> "Error: " . 'Los siguientes pallets ya fueron recibidos: '. $duplicado['Pallets']
                            ]
                        ]
                    ]
                );

            }
            else {

                LotesRecibosController::registraLoteRecibo($lote
                    , $arrayRecibo['CantidadRecibo']
                    , $arrayRecibo['EmpleadoId']
                    , array_key_exists('ReciboId', $arrayRecibo) ? $arrayRecibo['ReciboId'] : EmbarquesController::getNuevoId()
                );

                $lotesPallets = LotesPallets::where('LPA_LOT_LoteId', '=', $lote->LOT_LoteId)
                    ->whereIn('LPA_NumeroPallet', $arrayRecibo['Pallets'])->update(['LPA_Recibido' => 1, 'LPA_EMP_RecibidoPorId' => $arrayRecibo['EmpleadoId'] == '' ? null : $arrayRecibo['EmpleadoId']]);

                //if($lote->LOT_CMM_EstatusLoteId = ControlesMaestrosMultiples::$CMM_EstatusLote_Cerrado){

                $palletsNoRecibidos = \DB::select(
                    \DB::raw(
                        "select LPA_NumeroPallet, LPA_Recibido
                        from LotesPallets
                        where LPA_LOT_LoteId = '" . $lote->LOT_LoteId . "' and LPA_Recibido = 0
                        order by LPA_NumeroPallet, LPA_Recibido"
                    )
                );

                if (count($palletsNoRecibidos) > 0)
                    $lote->LOT_CMM_EstatusLoteId = ControlesMaestrosMultiples::$CMM_EstatusLote_ReciboParcial;
                else
                    $lote->LOT_CMM_EstatusLoteId = ControlesMaestrosMultiples::$CMM_EstatusLote_Recibido;

//            }
//            else{
//                $lote->LOT_CMM_EstatusLoteId = ControlesMaestrosMultiples::$CMM_EstatusLote_Recibido;
//            }

                $lote->save();

                \DB::commit();

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
                            'Estatus' => 'Procesado',
                            'Mensaje'=> "Error: " .$e->getMessage()
                        ]
                    ]
                ]
            );
        }

    }

    private function registraLoteRecibo($lote, $cantidadRecibo, $empleadoId, $reciboId){

        $loteRecibo = new LotesRecibos();
        $loteRecibo->LOTR_LoteReciboId = $reciboId;
        $loteRecibo->LOTR_LOT_LoteId = $lote->LOT_LoteId;
        $loteRecibo->LOTR_CantidadRecibo = $cantidadRecibo;
        $loteRecibo->LOTR_EMP_RecibidoPorId  = $empleadoId == '' ? null : $empleadoId;
        $loteRecibo->LOTR_TRAM_TraspasoMovtoId = LotesRecibosController::guardaReciboLoteMovto($cantidadRecibo,
            $loteRecibo->LOTR_LoteReciboId,
            $lote,
            $empleadoId
        );

        try{
            $loteRecibo->save();
        }catch(\Illuminate\Database\QueryException $ex){
            $results = \DB::select(\DB::raw("select * from LotesRecibos where LOTR_LoteReciboId  = '".$reciboId."'"));

            if(sizeof($results)>0)
                throw new \Exception(" Informacion Enviada ",301);

            throw new \Exception(" Guardar Traspaso ",304);

        }

    }

    private function guardaReciboLoteMovto($cantidadRecibo, $idLoteRecibo, $lote, $empleadoId){

        $traspasoMovto = new TraspasoMovto();
        $traspasoMovto->TRAM_ART_ArticuloId = $lote->LOT_ART_ArticuloId;
        $traspasoMovto->TRAM_CantidadATraspasar = $cantidadRecibo;
        $traspasoMovto->TRAM_CMM_TipoTransferenciaId = 'C9919E06-142F-4316-B675-B7EABDDA3885';
        $traspasoMovto->TRAM_Razon = 'Recibo de Lote: '.$lote->LOT_CodigoLote;
        $traspasoMovto->TRAM_Referencia =
            "Recibo de Lote : " . $lote->LOT_CodigoLote . ", "
            . "Cantidad: " . $cantidadRecibo;
        $traspasoMovto->TRAM_ReferenciaMovtoId = $idLoteRecibo;
        $traspasoMovto->TRAM_EMP_ModificadoPorId = $empleadoId;

        $arrayDetallesMovimiento = array();

        $localidadId = LocalidadesController::getLocalidadIdGralPorAlmacenGral();

        $dmi = new DetallesMovimientoInventario();

        $dmi->setCantidadTransferir($cantidadRecibo);
        $dmi->setIdAlmacen(Localidades::where('LOC_LocalidadId', '=', $localidadId)->get()[0]->LOC_ALM_AlmacenId);


        //if (EmbarquesController::tieneSeguimientoLocalidades($afectaRegistros->ARTICULO_ID)) {
        $localidad = new Localidades();
        $localidad->COL_LOCALIDAD_ID = $localidadId;
        $dmi->setLocalidad($localidad);
        //}

        //if (EmbarquesController::tieneSeguimientoLotes($afectaRegistros->ARTICULO_ID)) {
        $idLote = $lote->LOT_LoteId;
        $lote = new Lotes();
        $lote->COL_LOTE_ID = $idLote;
        $dmi->setLote($lote);
        //}
        array_push($arrayDetallesMovimiento, $dmi);

        return ProcesadorMovimientoInventarios::registraMovimientoEnInventario($traspasoMovto, $arrayDetallesMovimiento, null);

    }

    private function isDuplicado($idLote, $arrayPallets){

        $lotesPallets = LotesPallets::select('LPA_NumeroPallet')
            ->where('LPA_LOT_LoteId', '=', $idLote)
            ->whereIn('LPA_NumeroPallet', $arrayPallets)
            ->where('LPA_Recibido', '=', 1)
            ->orderBy('LPA_NumeroPallet')
            ->get();

        $palletsRecibidos = '';

        if(count($lotesPallets) > 0){
            for($x=0; $x<count($lotesPallets); $x++){

                $palletsRecibidos = $palletsRecibidos
                    . ($palletsRecibidos == '' ? $lotesPallets[$x]->LPA_NumeroPallet : ', '. $lotesPallets[$x]->LPA_NumeroPallet);

            }

            return ['Status' => 'Duplicado', 'Pallets' => $palletsRecibidos];

        }
        else{
            return ['Status' => 'Libre'];
        }

    }
} 