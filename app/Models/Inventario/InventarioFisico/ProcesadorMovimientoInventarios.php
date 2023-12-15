<?php namespace App\Models\Inventario\InventarioFisico;

//use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Inventario\Almacenes\AlmacenesController;
use App\Http\Controllers\Inventario\Articulos\ArticulosController;
//use App\Http\Controllers\Inventario\InventarioFisico\InventarioFisicoController;
use App\Http\Controllers\Inventario\Localidades\LocalidadesArticuloController;
use App\Http\Controllers\Inventario\Localidades\LocalidadesController;
use App\Http\Controllers\Inventario\LotesController;
use App\Http\Controllers\Inventario\LotesLocalidadesController;
use App\Http\Controllers\Sistema\DataBaseSession;
use App\Mapeos\Controles\ControlesMaestros;
//use App\Mapeos\Controles\ControlesMaestrosMultiples;
use App\Models\Inventario\LocalidadesArticulos;
use App\Models\Inventario\TraspasosLocalidades;
use App\Models\Inventario\InventarioFisico\TraspasoMovto;
use App\Models\Localidades;
use App\Models\Lotes;
use App\Models\LotesLocalidades;
use App\Models\TraspasosLotes;
//use Symfony\Component\HttpKernel\Tests\DataCollector\DumpDataCollectorTest;

class ProcesadorMovimientoInventarios {

    //public static function registraMovimientoEnInventario($transferenciaMovto,$detallesMovimientosEntrada){
    //return registraMovimientoEnInventario($transferenciaMovto, $detallesMovimientosEntrada, null, null, null, null, null, null, null);
    //}

    public static function registraMovimientoEnInventario($transferenciaMovto,$detallesMovimientosEntrada,$referenciaMovimientoId){

        $completos = ProcesadorMovimientoInventarios::sonDatosCompletosEnObjetoModeloTransferenciaMovto($transferenciaMovto);


        if ($completos) {
            $articulo = ArticulosController::buscaPorId($transferenciaMovto->TRAM_ART_ArticuloId);
            if ($articulo == null || $articulo->ART_Eliminado == 1 || $articulo->ART_Activo == 0) {
                return false;
            }

            $id_transferenciaMovto = ProcesadorMovimientoInventarios::guardaTransferenciaMovto($transferenciaMovto, $articulo);

            try {
                ProcesadorMovimientoInventarios::guardaDetalleDeMovimientos($articulo, $id_transferenciaMovto, $detallesMovimientosEntrada, $referenciaMovimientoId, $transferenciaMovto->TRAM_CantidadATraspasar, $transferenciaMovto->TRAM_Razon, $transferenciaMovto->TRAM_CMM_TipoTransferenciaId);
            }
            catch(\Exception $e){
                throw $e;
            }



            return $id_transferenciaMovto;
        } else {
            return null;
        }

    }

    public static function sonDatosCompletosEnObjetoModeloTransferenciaMovto($transferenciaMovto){
        $ban=true;

        if($transferenciaMovto->TRAM_ART_ArticuloId===null){
            $ban=false;
        }
        if($transferenciaMovto->TRAM_CantidadATraspasar===null){
            $ban=false;
        }
        if($transferenciaMovto->TRAM_CMM_TipoTransferenciaId===null){
            $ban=false;
        }

        return $ban;
    }

    public static function guardaTransferenciaMovto($transferenciaMovto,$articulo){
        $transferenciaMovto_tmp=new TraspasoMovto();
        $transferenciaMovto_tmp->TRAM_TraspasoMovtoId=self::nuevoId();
        $transferenciaMovto_tmp->TRAM_TRA_TraspasoId=$transferenciaMovto->TRAM_TRA_TraspasoId;
        $transferenciaMovto_tmp->TRAM_NumeroPartida=$transferenciaMovto->TRAM_NumeroPartida;
        $transferenciaMovto_tmp->TRAM_ART_ArticuloId=$transferenciaMovto->TRAM_ART_ArticuloId;
        $transferenciaMovto_tmp->TRAM_CantidadATraspasar=round($transferenciaMovto->TRAM_CantidadATraspasar,4, PHP_ROUND_HALF_UP);
        $transferenciaMovto_tmp->TRAM_CMM_TipoTransferenciaId=$transferenciaMovto->TRAM_CMM_TipoTransferenciaId;
        $transferenciaMovto_tmp->TRAM_Razon=$transferenciaMovto->TRAM_Razon;
        $transferenciaMovto_tmp->TRAM_Referencia=$transferenciaMovto->TRAM_Referencia;
        if($transferenciaMovto->TRAM_UnidadMedidadArt==null){
            $unidadmedida=ArticulosController::buscaNombreUMInventarioPorArticuloId($transferenciaMovto->TRAM_ART_ArticuloId);
            $unidadmedida = count($unidadmedida) > 0 ? $unidadmedida[0]->CMUM_Nombre : "";
        }else{
            $unidadmedida=$transferenciaMovto->TRAM_UnidadMedidadArt;
        }
        $transferenciaMovto_tmp->TRAM_UnidadMedidadArt=$unidadmedida;
        $transferenciaMovto_tmp->TRAM_EstatusContable=$transferenciaMovto->TRAM_EstatusContable;
        $transferenciaMovto_tmp->TRAM_CantidadAMano=$articulo->ART_CantidadAMano;
        $transferenciaMovto_tmp->TRAM_CantidadAManoConTraspaso=$articulo->ART_CantidadAMano+$transferenciaMovto->TRAM_CantidadATraspasar;
        $transferenciaMovto_tmp->TRAM_CantidadInspeccion=$transferenciaMovto->TRAM_CantidadInspeccion;
        $transferenciaMovto_tmp->TRAM_FechaRequerida=$transferenciaMovto->TRAM_FechaRequerida;
        //$transferenciaMovto_tmp->TRAM_PrecioArticulo=$transferenciaMovto->TRAM_PrecioArticulo;
        //$transferenciaMovto_tmp->TRAM_EMP_ModificadoPor=$transferenciaMovto->TRAM_EMP_ModificadoPor;
        $transferenciaMovto_tmp->TRAM_FechaEmbarque=$transferenciaMovto->TRAM_FechaEmbarque;
        $transferenciaMovto_tmp->TRAM_FechaRecibo=$transferenciaMovto->TRAM_FechaRecibo;
        $transferenciaMovto_tmp->TRAM_FechaInspeccion=$transferenciaMovto->TRAM_FechaInspeccion;
        $transferenciaMovto_tmp->TRAM_PRY_ProyectoId=$transferenciaMovto->TRAM_PRY_ProyectoId;
        //$transferenciaMovto_tmp->TRAM_FechaLibroMayor=$transferenciaMovto->TRAM_FechaLibroMayor;
        //$transferenciaMovto_tmp->TRAM_ValorContableArticuloAnterior=$transferenciaMovto->TRAM_ValorContableArticuloAnterior;
        //$transferenciaMovto_tmp->TRAM_ValorContableArticuloActual=$transferenciaMovto->TRAM_ValorContableArticuloActual;
        $transferenciaMovto_tmp->TRAM_ART_CMM_TipoCostoId=$articulo->ART_CMM_TipoCostoId;
        $transferenciaMovto_tmp->TRAM_ReferenciaMovtoId=$transferenciaMovto->TRAM_ReferenciaMovtoId;
        $transferenciaMovto_tmp->TRAM_EMP_ModificadoPor = $transferenciaMovto->TRAM_EMP_ModificadoPor != null
            && $transferenciaMovto->TRAM_EMP_ModificadoPor != '' ? $transferenciaMovto->TRAM_EMP_ModificadoPor
                : DataBaseSession::getEmpleadoId();

        $transferenciaMovto_tmp->save();
        //$ultimoinsertado=ProcesadorMovimientoInventarios::buscaIdUltimoInsertado();
        $ultimoinsertado=$transferenciaMovto_tmp->TRAM_TraspasoMovtoId;//ProcesadorMovimientoInventarios::buscaIdUltimoInsertado();
        return $ultimoinsertado;
    }

    public static function buscaIdUltimoInsertado(){
        $ultimo = TraspasoMovto::orderby('TRAM_FechaTraspaso', 'DESC')->first()->TRAM_TraspasoMovtoId;
        return $ultimo;
    }

    public static function guardaDetalleDeMovimientos($articulo,$id_transferenciaMovto,$detallesMovimientosEntrada,$referenciaMovimientoId,$TRAM_CantidadATraspasar,$TRAM_Razon,$TRAM_CMM_TipoTransferenciaId){


        $longitudDetalles = count($detallesMovimientosEntrada);

        for ($i = 0; $i < $longitudDetalles; $i++) {

            $almacen = AlmacenesController::buscaPorId($detallesMovimientosEntrada[$i]->getIdAlmacen());
            $localidad = ProcesadorMovimientoInventarios::obtenerObjetoLocalidadCompleto($articulo, $almacen, $detallesMovimientosEntrada[$i]->getLocalidad());

            $lote = null;
            $cantidadTraspaso = $detallesMovimientosEntrada[$i]->getCantidadTransferir();

            try {
                if ($detallesMovimientosEntrada[$i]->getCantidadTransferir() === null) {
                    throw new \Exception('La cantidad a transferir es nula.');
                }

                if ($localidad == null) {
                    throw new \Exception('La localidad es nula.');
                }
            }
            catch(\Exception $e){
                throw $e;
            }


            $loc_art = ProcesadorMovimientoInventarios::actualizarInventarioEnLocalidad($articulo->ART_ArticuloId, $localidad->LOC_LocalidadId, $cantidadTraspaso, $TRAM_CMM_TipoTransferenciaId);

            if ($articulo->ART_SeguimientoLotMult) {

                $lote = $detallesMovimientosEntrada[$i]->getLote();
                //$valor_contable_Historico_antes_del_movimiento=getValorContablePorId($articulo->ART_ArticulId,$lote->LOT_LoteId);
                if($lote != null){

                    $lote = LotesController::buscaLotePorId($lote->COL_LOTE_ID);
                    try {
                        $loteLocalidad = ProcesadorMovimientoInventarios::actualizarInventarioEnLote($lote->LOT_LoteId, $localidad->LOC_LocalidadId, $cantidadTraspaso, $TRAM_CMM_TipoTransferenciaId);
                    }
                    catch(\Exception $e){
                        throw $e;
                    }
                    $traspasoLote = new TraspasosLotes();
                    $traspasoLote->TRLOT_TRAM_TraspasoMovtoId = $id_transferenciaMovto;
                    $traspasoLote->TRLOT_LOTL_LoteLocalidadId = $loteLocalidad[0]->LOTL_LoteLocalidadId;
                    $traspasoLote->TRLOT_CantidadTraspaso = round($cantidadTraspaso,4, PHP_ROUND_HALF_UP);
                    $traspasoLote->TRLOT_CantidadAMano = $articulo->ART_CantidadAMano;
                    $traspasoLote->TRLOT_CantidadAnteriorLote = round($loteLocalidad[0]->LOTL_Cantidad - $cantidadTraspaso,4, PHP_ROUND_HALF_UP);
                    $traspasoLote->save();

                }

            }

            $trans_localidad = new TraspasosLocalidades();
            $trans_localidad->TRLOC_TRAM_TraspasoMovtoId = $id_transferenciaMovto;
            $trans_localidad->TRLOC_LOCA_LocalidadArticuloId = $loc_art[0]->LOCA_LocalidadArticuloId;
            $trans_localidad->TRLOC_CantidadTransferida = round($cantidadTraspaso,4, PHP_ROUND_HALF_UP);
            if (!$articulo->ART_SeguimientoLocMult == null) {
                $trans_localidad->TRLOC_CodigoLocalidad = $localidad->LOC_CodigoLocalidad;
            }
            $trans_localidad->TRLOC_CantidadAMano = $articulo->ART_CantidadAMano;
            $trans_localidad->TRLOC_CantidadAnteriorLocalidad = round($loc_art[0]->LOCA_Cantidad - $cantidadTraspaso,4, PHP_ROUND_HALF_UP);
            $trans_localidad->save();
            ArticulosController::actualizaCantidadAManoPorId($articulo->ART_ArticuloId, $cantidadTraspaso);
            //$articulo=buscaPorId($articulo->ART_ArticuloId);
        }

    }

    public static function obtenerObjetoLocalidadCompleto($articulo,$almacen,$localidad){
        $localidad_tmp = null;

        if($articulo->ART_SeguimientoLocMult==0){
            $localidad_tmp=LocalidadesController::buscaLocGralPorAlmacenId($almacen[0]->ALM_AlmacenId);
        }else{
            if($localidad!=null && $localidad->COL_LOCALIDAD_ID==null){
                $localidad_tmp=ProcesadorMovimientoInventarios::creaNuevaLocalidad($localidad);
            }else if($localidad!=null && $localidad->COL_LOCALIDAD_ID!=null){
                $localidad_tmp=LocalidadesController::buscaPorId($localidad->COL_LOCALIDAD_ID);
            }else if($localidad == null){
                //mensaje de error
            }
        }
        return $localidad_tmp;
    }


    public static function creaNuevaLocalidad($newlocalidad){
        $localidad=LocalidadesController::buscaPorCodigoYAlmacenId($newlocalidad->LOC_CodigoLocalidad,$newlocalidad->ALM_AlmacenId);

        if($localidad==null){
            $localidad=LocalidadesController::buscaBorradoPorCodigoYAlmacenId($newlocalidad->LOC_CodigoLocalidad,$newlocalidad->ALM_AlmacenId);
            if($localidad==null){
                $alm=AlmacenesController::buscaPorId($newlocalidad->ALM_AlmacenId);
                $newlocalidad->LOC_Planear=$alm->ALM_Planear;
                //$newlocalidad->LOC_EMP_ModificadoPorId="";
                $newlocalidad->LOC_LocalidadGeneral=false;
                $newlocalidad->save();
                return $newlocalidad;
            }
        }

        return $localidad;

    }

    public static function actualizarInventarioEnLocalidad($articuloId,$localidadId,$cantidad,$tipoTransferenciaId){
        $loc_art=LocalidadesArticuloController::buscaPorArticuloIdYLocalidadId($articuloId,$localidadId);

        if(count($loc_art)<1){
            $loc_art=LocalidadesArticuloController::buscaBorradoPorArticuloIdYLocalidadId($articuloId, $localidadId);
            if(count($loc_art)>0){
                $loc_art->LOCA_Cantidad=$cantidad;
                $loc_art->LOCA_EMP_ModificadoPorId=DataBaseSession::getEmpleadoId();
                LocalidadesArticuloController::restauraPorId($loc_art);
            }else{
                $loc_art=ProcesadorMovimientoInventarios::crearNuevaRelacionLocalidadArticulo($articuloId,$localidadId,$cantidad);
            }
        }else{
            $loc_art[0]->LOCA_Cantidad=round($loc_art[0]->LOCA_Cantidad+$cantidad, 4, PHP_ROUND_HALF_UP);
            $loc_art->LOCA_EMP_ModificadoPorId= DataBaseSession::getEmpleadoId();
            LocalidadesArticuloController::actualizaPorId($loc_art, $loc_art[0]->LOCA_Cantidad);
        }
        return $loc_art;
    }

    public static function actualizarInventarioEnLote($idLote,$idLocalidad,$cantidadTraspasar, $tipoTransferenciaId){

        try {
            $loteLocalidad = LotesLocalidadesController::buscaPorLoteIdYLocalidadId($idLote, $idLocalidad);

            if (count($loteLocalidad) < 1) {

                if ($cantidadTraspasar < 0){
                    $lote = Lotes::select('LOT_CodigoLote', 'ART_CodigoArticulo')
                        ->join('Articulos', 'ART_ArticuloId', '=', 'LOT_ART_ArticuloId')
                        ->where('LOT_LoteId', '=', $idLote)
                        ->get(1)[0];
                    $localidad = Localidades::find($idLocalidad);
                    $codigoLocalidad = $localidad->LOC_CodigoLocalidad;

                    $mensaje = 'No es posible realizar la transacción ya que no hay relación Lote-Localidad y por consecuencia no hay existencia de la cual sacar de inventario.<br/> Lote: '
                        . $lote->LOT_CodigoLote . ', Localidad: '. $codigoLocalidad .' - '.$localidad->LOC_Nombre . ', Articulo: '.$lote->ART_CodigoArticulo;

                    throw new \Exception($mensaje);
                }
                else {
                    $loteLocalidad = ProcesadorMovimientoInventarios::crearNuevaRelacionLoteLocalidad($idLote, $idLocalidad, $cantidadTraspasar);
                }
            } else {
                if (round($cantidadTraspasar,4, PHP_ROUND_HALF_UP) < 0
                    && round($loteLocalidad[0]->LOTL_Cantidad,4, PHP_ROUND_HALF_UP) < abs(round($cantidadTraspasar,4, PHP_ROUND_HALF_UP))) {
                    $lote = Lotes::select('LOT_CodigoLote', 'ART_CodigoArticulo')
                        ->join('Articulos', 'ART_ArticuloId', '=', 'LOT_ART_ArticuloId')
                        ->where('LOT_LoteId', '=', $idLote)
                        ->get(1)[0];
                    $localidad = Localidades::find($idLocalidad);
                    $codigoLocalidad = $localidad->LOC_CodigoLocalidad;

                    $mensaje = 'No es posible sacar la cantidad de '. abs($cantidadTraspasar) .', ya que su existencia es de ' . $loteLocalidad[0]->LOTL_Cantidad .'.<br/> Lote: '
                        . $lote->LOT_CodigoLote . ', Localidad: '. $codigoLocalidad .' - '.$localidad->LOC_Nombre . ', Articulo: '.$lote->ART_CodigoArticulo;

                    throw new \Exception($mensaje);
                }
                else {
                    LotesLocalidadesController::actualizaPorIdLoteLocalidad($loteLocalidad[0]->LOTL_LoteLocalidadId
                        , round($loteLocalidad[0]->LOTL_Cantidad + $cantidadTraspasar, 4, PHP_ROUND_HALF_UP));
                }
            }
        }
        catch(\Exception $e){
            throw $e;
        }
        return $loteLocalidad;

    }

    public static function crearNuevaRelacionLocalidadArticulo($articuloId,$localidadId,$cantidad){
        $loc_art= new LocalidadesArticulos();
        $loc_art->LOCA_LOC_LocalidadId=$localidadId;
        $loc_art->LOCA_ART_ArticuloId=$articuloId;
        $loc_art->LOCA_Cantidad=round($cantidad,4, PHP_ROUND_HALF_UP);
        $loc_art->LOCA_EMP_ModificadoPorId = DataBaseSession::getEmpleadoId();
        $loc_art->save();
        return LocalidadesArticuloController::buscaPorArticuloIdYLocalidadId($loc_art->LOCA_ART_ArticuloId, $loc_art->LOCA_LOC_LocalidadId);
    }

    public static function crearNuevaRelacionLoteLocalidad($idLote,$idLocalidad,$cantidadTraspasar){

        $loteLocalidad = new LotesLocalidades();
        $loteLocalidad->LOTL_LOT_LoteId=$idLote;
        $loteLocalidad->LOTL_LOC_LocalidadId = $idLocalidad;
        $loteLocalidad->LOTL_Cantidad = round($cantidadTraspasar,4, PHP_ROUND_HALF_UP);
        $loteLocalidad->LOTL_EMP_ModificadoPor = DataBaseSession::getEmpleadoId();
        $loteLocalidad->save();

        return LotesLocalidadesController::buscaPorLoteIdYLocalidadId($idLote, $idLocalidad);
    }

    public static function permiteInventarioNegativo(){
        $permite=false;
        $cm_permite=ControlesMaestros::$CMA_CEM_PermiteInventarioNegativo;
        $cm=buscaPorNombre($cm_permite);
        if($cm->CMA_Valor==1){
            $permite=true;
        }/*else{
            $permite=false;
        }*/
        return $permite;
    }

    public static function nuevoId()
    {        
        $resultSet = \DB::select("SELECT NEWID() AS ID");

        return $resultSet[0]->ID;
    }

}
