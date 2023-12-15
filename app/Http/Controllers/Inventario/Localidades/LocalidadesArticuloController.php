<?php namespace App\Http\Controllers\Inventario\Localidades;

// use App\Http\Requests\CreateLocalidad;
// use Illuminate\Routing\Route;
// use Illuminate\Support\Facades\Input;
// use Illuminate\Support\Facades\Session;
// use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
// use App\Models\AdmonSistema\Ciudad;
// use App\Models\AdmonSistema\Estado;
// use App\Models\AdmonSistema\Pais;
// use App\Models\Inventario\Localidad;
use App\Models\Inventario\LocalidadesArticulos;
use Response;

class LocalidadesArticuloController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    //
    public static function  existePorLocalidadId($id){

        $locaArticuloExiste= \DB::table('LocalidadesArticulo')->where('LOCA_LOC_LocalidadId', $id)->where('LOCA_Eliminado','0')->get();
        //return false
        if( $locaArticuloExiste==null){
            return false;

        }
        else{
            return true;

        }

    }
    public static function  existePorLocalidadIdConCantidad($id){

        $existePorLocaCantidad= \DB::table('LocalidadesArticulo')->where('LOCA_LOC_LocalidadId', $id)->where('LOCA_Cantidad','<>','0')->get();
        //return false
        // dd($existePorLocaCantidad);
        if( $existePorLocaCantidad!=null){
            return false;

        }
        else{
            return true;

        }

    }

    public function index(Request $request)
    {

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */

    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id, Request $request)
    {

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {

    }


    /////////////////////////////////////FUNCIONESPARA PROCESADOR DE INVENTARIO FISICO///////////////////////////////////

    public static function buscaPorArticuloIdYLocalidadId($articuloId,$localidadId){
        $localidad=LocalidadesArticulos::where('LOCA_ART_ArticuloId','=',$articuloId)
            ->where('LOCA_LOC_LocalidadId','=',$localidadId)
            ->where('LOCA_Eliminado','=','0')
            ->get();
        return $localidad;
    }

    public static function buscaPorArticuloIdYLocalidadIdTodos($articuloId,$localidadId){
        $localidad=LocalidadesArticulos::where('LOCA_ART_ArticuloId','=',$articuloId)
            ->where('LOCA_LOC_LocalidadId','=',$localidadId)
            ->get();
        return $localidad;
    }

    public static function buscaBorradoPorArticuloIdYLocalidadId($articuloId,$localidadId){
        $localidad=LocalidadesArticulos::where('LOCA_ART_ArticuloId','=',$articuloId)
            ->where('LOCA_LOC_LocalidadId','=',$localidadId)
            ->where('LOCA_Eliminado','=','1')
            ->get();
        return $localidad;
    }

    public static function restauraPorId($objeto){

        try{

            //date_default_timezone_set('America/Mexico_City');
            $hoy=date('d/m/Y H:i:s');
            $loca=$objeto;
            \DB::table('LocalidadesArticulo')->where('LOCA_LocalidadArticuloId', '=', $loca->LOCA_LocalidadArticuloId)->update(
                array(
                    'LOCA_Borrado' => 0,
                    'LOCA_Cantidad' => $loca->LOCA_Cantidad,
                    'LOCA_FechaUltimaModificacion' => $hoy,
                    'LOCA_FechaAsignacion' => $hoy
                    //'LOCA_EMP_ModificadoPor' => '',
                )
            );

            return LocalidadesArticuloController::buscaPorId($loca->LOCA_LocalidadArticuloId);

        }
        catch(\Exception $e){

            throw $e;

        }

    }

    public static function buscaPorId($id){
        $localidadarticulo=LocalidadesArticulos::find($id)->where('LOCA_Borrado','=','0');
        return $localidadarticulo;
    }

    public static function actualizaPorId($objeto,$nuevaCantidadPorAjustar){

        try{

            //date_default_timezone_set('America/Mexico_City');
            $hoy=date('d/m/Y H:i:s');
            $localidadArticulo=$objeto;
            \DB::table('LocalidadesArticulo')->where('LOCA_LocalidadArticuloId', '=', $localidadArticulo[0]->LOCA_LocalidadArticuloId)->update(
                array(
                    //'LOCA_Cantidad' => $localidadArticulo[0]->LOCA_Cantidad,
                    'LOCA_Cantidad' => $nuevaCantidadPorAjustar,
                    'LOCA_FechaUltimaModificacion' => $hoy
                    //'LOCA_EMP_ModificadoPor' => '',
                )
            );

        }
        catch(\Exception $e){

            throw $e;

        }

    }

    public static function buscaIdUltimoInsertado(){
        $ultimo = LocalidadesArticulos::orderby('LOCA_FechaUltimaModificacion', 'DESC')->get();
        return $ultimo;
    }

    

    /////////////////////////////////FIN FUNCIONESPARA PROCESADOR DEINVENTARIO FISICO///////////////////////////////////
}
