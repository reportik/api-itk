<?php namespace App\Http\Controllers\Inventario\AjustesInventario;

use App\Http\Controllers\Inventario\Almacenes\AlmacenesController;
use App\Http\Controllers\Inventario\Articulos\ArticulosController;
use App\Http\Controllers\Inventario\Localidades\LocalidadesArticuloController;
use App\Http\Controllers\Inventario\Localidades\LocalidadesController;
use App\Http\Controllers\Inventario\LotesController;
use App\Http\Controllers\Inventario\LotesLocalidadesController;
use App\Http\Controllers\Sistema\DAOGeneralController;
use App\Http\Controllers\Sistema\DataBaseSession;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Mapeos\Controles\ControlesMaestrosMultiples;
use App\Models\AdmonSistema\ControlMaestroMultiple;
use App\Models\Inventario\Almacen;
use App\Models\Inventario\Articulos\Articulo;
use App\Models\Inventario\InventarioFisico\DetallesMovimientoInventario;
use App\Models\Inventario\InventarioFisico\LocalidadesArticulo;
use App\Models\Inventario\InventarioFisico\ProcesadorMovimientoInventarios;
use App\Models\Inventario\InventarioFisico\TraspasoMovto;
use App\Models\Inventario\Localidad;
use App\Models\Inventario\LocalidadesArticulos;
use App\Models\Inventario\TraspasosLocalidades;
use App\Models\Localidades;
use App\Models\Lotes;
use App\Models\LotesLocalidades;
use App\Models\TraspasosLotes;
use Response;

class AjustesInventarioController extends Controller {

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
        $articulos=array(''=>'Selecciona Articulo') + Articulo::select('ART_ArticuloId',\DB::raw("ART_CodigoArticulo + ' - ' + ART_Nombre AS FULL_NAME"))
                ->whereRaw("ART_Eliminado = 0 AND ART_Activo = 1")
                ->orderBy('ART_Nombre','ASC')
                ->lists('FULL_NAME', 'ART_ArticuloId')->all();
        $motivoajuste=array(''=>'Selecciona Motivo Ajuste') + ControlMaestroMultiple::select('CMM_ControlId','CMM_Valor')
                ->where('CMM_Control','=',ControlesMaestrosMultiples::$CMM_INV_MotivoAjuste)
                ->orderby('CMM_Valor','ASC')
                ->lists('CMM_Valor', 'CMM_ControlId')->all();
        $almacenes=array(''=>'Selecciona Almacén') +
            Almacen::whereRaw(" ALM_Eliminado = 0".(DataBaseSession::isPermisoCorporativo() ? "" : " AND ALM_AlmacenId IN (".DataBaseSession::getAlmacenesPorCediId().")"))
                ->orderby('ALM_Nombre','ASC')
                ->lists('ALM_Nombre','ALM_AlmacenId')->all();
        $localidades=array(''=>'Seleccione Localidad');// + Localidad::all()->lists('LOC_Nombre','LOC_LocalidadId');
        $lotes=array(''=>'Seleccione Lote');
        return view('Inventario.AjustesInventario.create', compact('version','articulos', 'motivoajuste','almacenes','localidades','lotes'));
    }

    public static function ConsultarLotesArticulo($id){
        /*$results=\DB::table('Lotes')
            ->whereRaw("LOT_ART_ArticuloId = '".$id."'
                and (LOT_CMM_EstatusLoteId = '".ControlesMaestrosMultiples::$CMM_EstatusLote_Recibido."'
                or LOT_CMM_EstatusLoteId = '".ControlesMaestrosMultiples::$CMM_EstatusLote_ReciboParcial."'
                or LOT_LoteManual = 1)"
                .(DataBaseSession::isPermisoCorporativo() ? "" : " AND lot_loteid in (
                                    select LOTL_lot_loteid from loteslocalidades
                                    inner join localidades on loc_localidadid = lotl_loc_localidadid
                                    inner join almacenes on alm_almacenId = loc_alm_almacenId
                                    where alm_almacenId in (".DataBaseSession::getAlmacenesPorCediId().")
                                    GROUP BY LOTL_lot_loteid
                    )")
            )
            ->select('LOT_LoteId','LOT_CodigoLote')
            ->get();*/
        $results=\DB::table('Lotes')
            ->whereRaw("LOT_ART_ArticuloId = '".$id."'"
                .(DataBaseSession::isPermisoCorporativo() ? "" : " AND lot_loteid in (
                                    select LOTL_lot_loteid from loteslocalidades
                                    inner join localidades on loc_localidadid = lotl_loc_localidadid
                                    inner join almacenes on alm_almacenId = loc_alm_almacenId
                                    where alm_almacenId in (".DataBaseSession::getAlmacenesPorCediId().")
                                    GROUP BY LOTL_lot_loteid
                    )")
            )
            ->select('LOT_LoteId','LOT_CodigoLote')
            ->get();

        return Response::json($results);
    }

    public function buscarlocalidades($almacenId){

        $consulta = \DB::select(
            \DB::raw(
                "SELECT
                    LOC_LocalidadId
                    ,LOC_Nombre
                FROM Localidades
                WHERE LOC_ALM_AlmacenId = '".$almacenId."'
                AND LOC_Eliminado = 0
                AND LOC_LocalidadGeneral = 0
                ORDER BY
                    LOC_Nombre"
            )
        );

        return $consulta;

    }

    public function consultarinventarioarticulos2($ART_ArticuloId,$LOC_LocalidadId,$LOT_LoteId){

        $sub = \DB::select(
            \DB::raw(
                "SELECT LOCA_ART_ArticuloId, ART_CodigoArticulo, ART_Nombre, ART_CantidadAMano, ART_SeguimientoLocMult, ART_SeguimientoLotMult, CMUM_Nombre,
                ALM_AlmacenId, ALM_Nombre, LOCA_LOC_LocalidadId, LOC_Nombre, LOCA_Cantidad, LOT_LoteId, LOT_CodigoLote,
                LOTL_Cantidad, EMP_EmpleadoId, LOCA_LocalidadArticuloId, LOTL_LoteLocalidadId
                FROM Articulos
                INNER JOIN ControlesMaestrosUM ON CMUM_UnidadMedidaId=ART_CMUM_UMInventarioId
                LEFT JOIN LocalidadesArticulo ON LOCA_ART_ArticuloId=ART_ArticuloId AND LOCA_LOC_LocalidadId = '".$LOC_LocalidadId."'
                LEFT JOIN Localidades ON LOCA_LOC_LocalidadId=LOC_LocalidadId
                LEFT JOIN (LotesLocalidades
			              INNER JOIN Lotes ON LOTL_LOT_LoteId = LOT_LoteId)
			              ON LOT_ART_ArticuloId = ART_ArticuloId AND LOTL_LOC_LocalidadId = LOC_LocalidadId AND LOT_LoteId = '".$LOT_LoteId."'
                LEFT JOIN Almacenes ON LOC_ALM_AlmacenId=ALM_AlmacenId
                LEFT JOIN Empleados ON EMP_EmpleadoId=ALM_EMP_ResponsableId
                WHERE ART_ArticuloId='".$ART_ArticuloId."'"
            )
        );

        return Response::json($sub);

    }

    public function seguimientoLocalidadesLotes(){
        $articuloId = $_POST['articuloId'];

        $consulta = "SELECT ART_SeguimientoLocMult,ART_SeguimientoLotMult FROM Articulos WHERE ART_Eliminado = 0 AND ART_Activo = 1 AND ART_ArticuloId = '$articuloId'";


        $seguimiento = \DB::select(\DB::raw($consulta));
        return $seguimiento;
    }

    public function consultarinventarioarticulos($id){
        $sub = \DB::select(
            \DB::raw(
                "SELECT LOCA_ART_ArticuloId, ART_CodigoArticulo, ART_Nombre, ART_CantidadAMano, ART_SeguimientoLocMult, ART_SeguimientoLotMult, CMUM_Nombre,
                ALM_AlmacenId, ALM_Nombre, LOCA_LOC_LocalidadId, LOC_Nombre, LOCA_Cantidad, LOT_LoteId, LOT_CodigoLote,
                LOTL_Cantidad, EMP_EmpleadoId, LOCA_LocalidadArticuloId, LOTL_LoteLocalidadId
                FROM Articulos
                INNER JOIN ControlesMaestrosUM ON CMUM_UnidadMedidaId=ART_CMUM_UMInventarioId
                LEFT JOIN LocalidadesArticulo ON LOCA_ART_ArticuloId=ART_ArticuloId
                LEFT JOIN Localidades ON LOCA_LOC_LocalidadId=LOC_LocalidadId
                LEFT JOIN (LotesLocalidades
			              INNER JOIN Lotes ON LOTL_LOT_LoteId = LOT_LoteId)
			              ON LOT_ART_ArticuloId = ART_ArticuloId AND LOTL_LOC_LocalidadId = LOC_LocalidadId
                LEFT JOIN Almacenes ON LOC_ALM_AlmacenId=ALM_AlmacenId
                LEFT JOIN Empleados ON EMP_EmpleadoId=ALM_EMP_ResponsableId
                WHERE ART_ArticuloId='".$id."'
                AND LOTL_Cantidad > 0
                ".(DataBaseSession::isPermisoCorporativo() ? "" : " AND ALM_AlmacenId IN (".DataBaseSession::getAlmacenesPorCediId().")")."
                "
            )
        );

        /*$sub=Articulo::select('LOCA_ART_ArticuloId','ART_ArticuloId','ART_CodigoArticulo','ART_Nombre','ART_CantidadAMano','ART_SeguimientoLocMult','CMUM_Nombre','ALM_AlmacenId','ALM_Nombre','LOCA_LOC_LocalidadId','LOC_Nombre','LOCA_Cantidad','EMP_EmpleadoId')
            ->join('ControlesMaestrosUM','CMUM_UnidadMedidaId','=','ART_CMUM_UMInventarioId')
            ->leftJoin('LocalidadesArticulo','LOCA_ART_ArticuloId','=','ART_ArticuloId')
            ->leftJoin('Localidades','LOCA_LOC_LocalidadId','=','LOC_LocalidadId')
            ->leftJoin('Almacenes','LOC_ALM_AlmacenId','=','ALM_AlmacenId')
            ->leftJoin('Empleados','EMP_EmpleadoId','=','ALM_EMP_ResponsableId')
            ->where('ART_ArticuloId','=',$id)
            ->get();*/
        return Response::json($sub);
    }

    public function consultarcantidadlocalidadesarticulos($idlocalidad,$idart){
        $sub=LocalidadesArticulo::select('LOCA_Cantidad')
            ->where('LOCA_LOC_LocalidadId','=',$idlocalidad)
            ->where('LOCA_ART_ArticuloId','=',$idart)
            ->get();
        return Response::json($sub);
    }

    public function consultararticulo($id){
        $sub=Articulo::select('ART_ArticuloId','ART_CodigoArticulo','ART_Nombre','ART_CantidadAMano','CMUM_Nombre')
            ->join('ControlesMaestrosUM','CMUM_UnidadMedidaId','=','ART_CMUM_UMInventarioId')
            ->where('ART_ArticuloId','=',$id)
            ->get();
        return Response::json($sub);
    }

    public function buscaridempleado($idalm){
        $sub= Almacen::select('ALM_EMP_ResponsableId')
            ->where('ALM_AlmacenId','=',$idalm)
            ->get();
        return Response::json($sub);
    }

    public function consultarcantidaddecimales(){
        $sub=ControlMaestro::select('CMA_Valor')
            ->where('CMA_Control','=',ControlesMaestros::$CMA_INV_DecimalesCantidades)
            ->get();
        return Response::json($sub);
    }

    public function ajustesinventariogeneral(){

        \DB::beginTransaction();
//dd($_POST['Ajustes']);
        try {
//
//            $val=$_POST['CCON_contador'];
//            $arreglo = array();
//            for($x=0; $x<$val; $x++){
//                $arreglo[$x][0]=$_POST['ART_CodigoArticulo'.($x+1)];
//                $arreglo[$x][1]=$_POST['ART_Nombre'.($x+1)];
//                $arreglo[$x][2]=$_POST['CMUM_Nombre'.($x+1)];
//                $arreglo[$x][3]=$_POST['ART_CantidadAMano'.($x+1)];
//                $arreglo[$x][4]=$_POST['ALM_Nombre'.($x+1)];
//                $arreglo[$x][5]=$_POST['LOC_Nombre'.($x+1)];
//                $arreglo[$x][6]=$_POST['EXI_ExistenciaActual'.($x+1)];
//                $arreglo[$x][7]=$_POST['LOT_CodigoLote'.($x+1)];
//                $arreglo[$x][8]=$_POST['LOTL_Cantidad'.($x+1)];
//                $arreglo[$x][9]=$_POST['CAN_CantidadAjuste'.($x+1)];
//                $arreglo[$x][10]=$_POST['EXI_ExistenciaFinal'.($x+1)];
//                $arreglo[$x][11]=$_POST['EliminaRenglon'.($x+1)];
//                $arreglo[$x][12]=$_POST['ART_ArticuloId'.($x+1)];
//                $arreglo[$x][13]=$_POST['ALM_AlmacenId'.($x+1)];
//                $arreglo[$x][14]=$_POST['LOC_LocalidadId'.($x+1)];
//                $arreglo[$x][15]=$_POST['LOTL_LoteLocalidadId'.($x+1)];
//                $arreglo[$x][16]=$_POST['EMP_EmpleadoId'.($x+1)];
//            }

            $arrayArticulos = json_decode($_POST['Ajustes']);

            $longitud = count($arrayArticulos);

            $TotalCantidadAjuste = 0;

            if($_POST['Operacion']=="Reemplazar") {

                for ($x = 0; $x < $longitud; $x++) {

                    if ($arrayArticulos[$x]->LoteLocalidadId != '') {
                        $existencia = floatval(LotesLocalidades::find($arrayArticulos[$x]->LoteLocalidadId)->LOTL_Cantidad);
                    } elseif ($arrayArticulos[$x]->LocalidadArticuloId != '') {

                        if($arrayArticulos[$x]->LoteId != ''){
                            $existencia = 0;
                        }
                        else {
                            $existencia = floatval(LocalidadesArticulo::find($arrayArticulos[$x]->LocalidadArticuloId)->LOCA_Cantidad);
                        }
                    }
                    else{
                        $existencia = 0;
                    }

                    if ($arrayArticulos[$x]->Cantidad == 0) {
                        $arrayArticulos[$x]->Cantidad = ($existencia) * (-1);//existencia actual localidad - cantidadajuste * -1
                    } elseif ($arrayArticulos[$x]->Cantidad > $existencia) {
                        $arrayArticulos[$x]->Cantidad = $arrayArticulos[$x]->Cantidad - $existencia;//cantidadajuste - existencia actual localidad
                    } elseif ($arrayArticulos[$x]->Cantidad < $existencia) {
                        $arrayArticulos[$x]->Cantidad = (($existencia - $arrayArticulos[$x]->Cantidad) * (-1));//existencia actual localidad - cantidadajuste  * -1
                    } elseif ($arrayArticulos[$x]->Cantidad == $existencia) {
                        $arrayArticulos[$x]->Cantidad = 0;
                    }

                    $TotalCantidadAjuste = $TotalCantidadAjuste + $arrayArticulos[$x]->Cantidad;

                }

            }
            else{
                $TotalCantidadAjuste = $_POST['Total'];
            }

            $this->guardaTraspasoMovto($TotalCantidadAjuste, $arrayArticulos, $_POST['ArticuloId'], $_POST['Motivo'], $_POST['Comentarios']);

//            $UnidadMedida=ArticulosController::buscaNombreUMInventarioPorArticuloId($_POST['ART_ArticuloId']);
//            $transferencia_id=AjustesInventarioController::procesaTransferirArticulo($CantidadPorAjustar,$_POST['ART_ArticuloId'],$_POST['CMM_MotivoAjuste'],$_POST['comentarios'],$UnidadMedida[0]->CMUM_Nombre,$Articulo->ART_CantidadAMano);
//            AjustesInventarioController::guardaCambios($transferencia_id,$val,$_POST['TipoOperacion'],$arreglo,$Articulo->ART_CantidadAMano,$Articulo->ART_SeguimientoLotMult);
//            if($_POST['TipoOperacion']=='Agregar'){
//                $cantidadAManoActual=$Articulo->ART_CantidadAMano;
//                $nuevoCantidadAMano=$cantidadAManoActual+$CantidadPorAjustar;
//                $Articulo->ART_CantidadAMano=$nuevoCantidadAMano;
//            }else{
//                $Articulo->ART_CantidadAMano=$TotalCantidadAjuste;
//            }
//            $Articulo->ART_CantidadUltimoAjuste=$CantidadPorAjustar;
//            ArticulosController::actualizaCamposDeAjustePorId($Articulo);

            //return Response::json(true);

            $mensaje = 'El  Ajuste de Inventario se registró con éxito.';

            \DB::commit();

            return ['Status' => 'Valido', 'Mensaje' => $mensaje];

        }
        catch (\Exception $e) {

            \DB::rollback();

            return ['Status' => 'Error', 'Mensaje' => 'No se registró el Ajuste de Inventario. Ocurrió un error al realizar el proceso. <br><br><br>Error: <br>'.$e->getMessage()];

        }

    }

    public function procesaTransferirArticulo($CantidadPorAjustar,$idArt,$MotivoAjuste,$comentarios,$UnidadMedida,$CantidadAMano){

        try{

            $TraspasosMovtos=new TraspasoMovto();
            $TraspasosMovtos->TRAM_ART_ArticuloId=$idArt;
            $TraspasosMovtos->TRAM_CantidadATraspasar=$CantidadPorAjustar;
            $TraspasosMovtos->TRAM_CMM_TipoTransferenciaId=ControlesMaestrosMultiples::$AJUSTE_DE_INVENTARIO;
            $TraspasosMovtos->TRAM_Razon=$MotivoAjuste;
            $TraspasosMovtos->TRAM_Referencia=$comentarios;
            $TraspasosMovtos->TRAM_UnidadMedidadArt=$UnidadMedida;
            $TraspasosMovtos->TRAM_EstatusContable=false;
            $TraspasosMovtos->TRAM_CantidadAMano=$CantidadAMano;
            $TraspasosMovtos->TRAM_CantidadAManoConTraspaso=$CantidadAMano+$CantidadPorAjustar;
            $TraspasosMovtos->save();

            $ultimoinsertado=ProcesadorMovimientoInventarios::buscaIdUltimoInsertado();

            return $ultimoinsertado;

        }
        catch(\Exception $e){

            throw $e;

        }

    }

    public function guardaCambios($transferencia_id,$val,$TipoOperacion,$arreglo,$CantidadAMano,$ART_SeguimientoLotMult){

        try{

            for($x=0;$x<$val-1;$x++){
                if($TipoOperacion=="Agregar" && $arreglo[$x][9]==0){//cantidadajuste
                    //no hacer nada
                }elseif($TipoOperacion=="Reemplazar" && $arreglo[$x][9]==0){//cantidadajuste
                    //no hacer nada
                }else{
                    $TotalCantidadAjuste=$arreglo[$x][9];//cantidadajuste
                    if($TipoOperacion=="Reemplazar"){
                        $CantidadPorAjustar=(($arreglo[$x][6]-$arreglo[$x][9])*(-1));//existencia actual - cantidadajuste * -1
                    }else{
                        $CantidadPorAjustar=$arreglo[$x][9];//cantidadajuste
                    }
                    $codigoLocalidad=LocalidadesController::buscaCodigoLocalidad($arreglo[$x][14]);//localidadId
                    AjustesInventarioController::asignarInventarioEnLocalidad($TotalCantidadAjuste,$TipoOperacion,$arreglo[$x][12],$arreglo[$x][14],$CantidadPorAjustar,$codigoLocalidad,$transferencia_id,$CantidadAMano);//articuloId, localidadId
                    if($ART_SeguimientoLotMult == 1){
                        AjustesInventarioController::asignarInventarioEnLote($arreglo[$x][7],$TotalCantidadAjuste,$TipoOperacion,$arreglo[$x][12],$arreglo[$x][14],$CantidadPorAjustar,$codigoLocalidad,$transferencia_id,$CantidadAMano);//codigoLote, articuloId, localidadId
                    }
                }
            }

        }
        catch(\Exception $e){

            throw $e;

        }

    }

    public function asignarInventarioEnLocalidad($TotalCantidadAjuste,$TipoOperacion,$articuloId,$localidadId,$CantidadPorAjustar,$codigoLocalidad,$transferencia_id,$CantidadAMano){

        try{

            $loc_art=LocalidadesArticuloController::buscaPorArticuloIdYLocalidadIdTodos($articuloId,$localidadId);
            if(count($loc_art) == 0){
                $loc_art= new LocalidadesArticulos();
                $loc_art->LOCA_LOC_LocalidadId=$localidadId;
                $loc_art->LOCA_ART_ArticuloId=$articuloId;
                $loc_art->LOCA_Cantidad=$CantidadPorAjustar;
                $loc_art->save();
                $loc_art=LocalidadesArticuloController::buscaIdUltimoInsertado();
                $loc_art[0]->LOCA_Cantidad = 0;
            }else if($loc_art[0]->LOCA_Eliminado==1){
                if($TipoOperacion=="Agregar"){
                    $nuevaCantidadPorAjustar=$loc_art[0]->LOCA_Cantidad+$CantidadPorAjustar;
                }else{
                    $nuevaCantidadPorAjustar=$TotalCantidadAjuste;
                }
                LocalidadesArticuloController::restauraPorId($loc_art,$nuevaCantidadPorAjustar);
            }else{
                if($TipoOperacion=="Agregar"){
                    $nuevaCantidadPorAjustar=$loc_art[0]->LOCA_Cantidad+$CantidadPorAjustar;
                }else{
                    $nuevaCantidadPorAjustar=$TotalCantidadAjuste;
                }
                LocalidadesArticuloController::actualizaPorId($loc_art,$nuevaCantidadPorAjustar);
            }
            $trans_localidad=new TraspasosLocalidades();
            $trans_localidad->TRLOC_TRAM_TraspasoMovtoId=$transferencia_id;
            $trans_localidad->TRLOC_LOCA_LocalidadArticuloId=$loc_art[0]->LOCA_LocalidadArticuloId;
            $trans_localidad->TRLOC_CantidadTransferida=$CantidadPorAjustar;
            $trans_localidad->TRLOC_CodigoLocalidad=$codigoLocalidad;
            $trans_localidad->TRLOC_CantidadAMano=$CantidadAMano;
            $trans_localidad->TRLOC_CantidadAnteriorLocalidad=$loc_art[0]->LOCA_Cantidad;
            $trans_localidad->save();

        }
        catch(\Exception $e){

            throw $e;

        }

    }

    public function asignarInventarioEnLote($CodigoLote,$TotalCantidadAjuste,$TipoOperacion,$articuloId,$localidadId,$CantidadPorAjustar,$codigoLocalidad,$transferencia_id,$CantidadAMano){

        try{

            $Lotes = LotesController::buscaLotePorCodigoLote($CodigoLote);
            $loteloca = LotesLocalidadesController::buscaPorLoteIdYLocalidadId($Lotes[0]->LOT_LoteId,$localidadId);
            if(count($loteloca) == 0){
                $loteloca = new LotesLocalidades();
                $loteloca->LOTL_LOT_LoteId = $Lotes[0]->LOT_LoteId;
                $loteloca->LOTL_LOC_LocalidadId = $localidadId;
                $loteloca->LOTL_Cantidad=$CantidadPorAjustar;
                $loteloca->save();
                $loteloca=LotesLocalidadesController::buscaIdUltimoInsertado();
                $loteloca[0]->LOTL_Cantidad = 0;
            }else if($loteloca[0]->LOTL_Eliminado == 1){
                if($TipoOperacion=="Agregar"){
                    $nuevaCantidadPorAjustar=$loteloca[0]->LOTL_Cantidad+$CantidadPorAjustar;
                }else{
                    $nuevaCantidadPorAjustar=$TotalCantidadAjuste;
                }
                LotesLocalidadesController::restauraPorId($loteloca,$nuevaCantidadPorAjustar);
            }else{
                if($TipoOperacion=="Agregar"){
                    $nuevaCantidadPorAjustar=$loteloca[0]->LOTL_Cantidad+$CantidadPorAjustar;
                }else{
                    $nuevaCantidadPorAjustar=$TotalCantidadAjuste;
                }
                LotesLocalidadesController::actualizaPorId($loteloca,$nuevaCantidadPorAjustar);
            }
            $trans_lotes = new TraspasosLotes();
            $trans_lotes->TRLOT_TRAM_TraspasoMovtoId=$transferencia_id;
            $trans_lotes->TRLOT_LOTL_LoteLocalidadId=$loteloca[0]->LOTL_LoteLocalidadId;
            $trans_lotes->TRLOT_CantidadTraspaso=$CantidadPorAjustar;
            $trans_lotes->TRLOT_CantidadAMano=$CantidadAMano;
            $trans_lotes->TRLOT_CantidadAnteriorLote=$loteloca[0]->LOTL_Cantidad;
            $trans_lotes->save();

        }
        catch(\Exception $e){

            throw $e;

        }

    }


    private function guardaTraspasoMovto($cantidadTraspasar, $arrayAjustes, $articuloId, $motivo, $comentarios){

        try {

            $traspasoMovto = new TraspasoMovto();
            $traspasoMovto->TRAM_ART_ArticuloId = $articuloId;
            $traspasoMovto->TRAM_CantidadATraspasar = $cantidadTraspasar;
            $traspasoMovto->TRAM_CMM_TipoTransferenciaId = ControlesMaestrosMultiples::$AJUSTE_DE_INVENTARIO;
            $traspasoMovto->TRAM_Razon = $motivo;
            $traspasoMovto->TRAM_EMP_ModificadoPorId = DataBaseSession::getEmpleadoId();
            $traspasoMovto->TRAM_Referencia = $comentarios;

            $cantidadLotes = count($arrayAjustes);
            $arrayDetallesMovimiento = array();

            for ($i = 0; $i < $cantidadLotes; $i++) {

                $dmi = new DetallesMovimientoInventario();

                $dmi->setCantidadTransferir($arrayAjustes[$i]->Cantidad);
                $dmi->setIdAlmacen(Localidades::where('LOC_LocalidadId', '=', $arrayAjustes[$i]->LocalidadId)->get()[0]->LOC_ALM_AlmacenId);

                //if (EmbarquesController::tieneSeguimientoLocalidades($afectaRegistros->ARTICULO_ID)) {
                $localidad = new Localidades();
                $localidad->COL_LOCALIDAD_ID = $arrayAjustes[$i]->LocalidadId;
                $dmi->setLocalidad($localidad);
                //}

                //if (EmbarquesController::tieneSeguimientoLotes($afectaRegistros->ARTICULO_ID)) {
                $lote = new Lotes();
                $lote->COL_LOTE_ID = $arrayAjustes[$i]->LoteId;
                $dmi->setLote($lote);
                //}

                array_push($arrayDetallesMovimiento, $dmi);
            }

            ProcesadorMovimientoInventarios::registraMovimientoEnInventario($traspasoMovto, $arrayDetallesMovimiento, null);

        }

        catch(\Exception $e){

            throw $e;

        }


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
        dd("sdufosdi");
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

}
