<?php namespace App\Http\Controllers\Inventario\Localidades;

// use App\Http\Requests\CreateLocalidad;
// use Illuminate\Routing\Route;
// use Illuminate\Support\Facades\Input;
// use Illuminate\Support\Facades\Session;
use App\Http\Controllers\GeneralController;
//use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
// use App\Models\AdmonSistema\Ciudad;
// use App\Models\AdmonSistema\Estado;
// use App\Models\AdmonSistema\Pais;
use App\Models\Inventario\Localidad;
use App\Models\Localidades;
use Response;

class LocalidadesController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    //
    public static function  getLocalidadGralPorAlmacenId($idAlmacen){
       // $idLocalidadGral=Localidad::find($idAlmacen, array('LOC_ALM_AlmacenId'))->LOC_LocalidadId;
        $localidadesGeneral = \DB::table('Localidades')->select('LOC_LocalidadId')->where('LOC_ALM_AlmacenId', $idAlmacen)->where('LOC_Eliminado','0')->where('LOC_LocalidadGeneral','1')->get();
       // dd( $localidadesGeneral);
        $localidadesGeneralId=array($localidadesGeneral[0]->LOC_LocalidadId);
        //$localidadesGeneral[1];

        return  $localidadesGeneralId[0];
    }
    public static function  buscaTodosPorCodigoAlmacen($id){

        $cantidadLoc = \DB::table('Localidades')->where('LOC_ALM_AlmacenId', $id)->where('LOC_Eliminado','0')->where('LOC_LocalidadGeneral','0')->count();

        if( $cantidadLoc>0){
            return false;

        }
        else{
            return true;

        }




       /* $codigoAlmacen=\DB::table('Almacenes')->where('ALM_AlmacenId', $id)->ALM_CodigoAlmacen;
        $idAlmacen=\DB::table('Almacenes')->where('ALM_CodigoAlmacen', $codigoAlmacen)->ALM_AlmacenId;
        $localidadesGeneral = \DB::table('Localidades')->where('LOC_ALM_AlmacenId', $id)->where('LOC_Eliminado','0')->where('LOC_LocalidadGeneral','1')->get();
        return $localidadesGeneral;*/
    }
    public function codlocalidad($id){
       // dd("hola");

        $codlocalidad =\DB::table('Localidades')->where('LOC_CodigoLocalidad', '=', $id)->get();

        return Response::json($codlocalidad);

    }


    public function index(Request $request)
    {
       // dd("hola");



       $encabezados=array(
            'id',
            'Codigo de localidad',
            'Nombre de la localidad',
           'Nombre del Almacen'

        );
        $contenidos=array(
            'LOC_LocalidadId',
            'LOC_CodigoLocalidad',
            'LOC_Nombre',
            'ALM_Nombre'

        );
        $results = \DB::table('Localidades')->where('LOC_Eliminado', '0')->where('LOC_LocalidadGeneral','0')
            ->join('Almacenes', 'Localidades.LOC_ALM_AlmacenId', '=', 'Almacenes.ALM_AlmacenId')
            ->get();


        $almacen =\DB::table('Almacenes')->where('ALM_Eliminado', '0')->orderBy('ALM_Nombre','ASC')->lists('ALM_Nombre', 'ALM_AlmacenId');
        $almaceneS = array('' => 'Seleccione') + $almacen;


        return view('Inventario.Localidades.indexLocalidad',compact('results','encabezados','contenidos','almaceneS'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
        // return redirect()->route('Prospectos.prospectos.index');;

    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        //dd("estoy por grabar");
        //  dd($_POST['CCON_contador']);
        // este es el codigo para grabar lo de la tabla
        ///////////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

        for($val=1; $val<=$_POST['CCON_contador']; $val++)
        {


            if($_POST['almacenn'.$val] == ""){ $_POST['CiudadContacto'.$val] = null; }
            if($_POST['planearr'.$val]==""){$_POST['planearr'.$val]='0';}
            if($_POST['locgenerall'.$val]==""){$_POST['locgenerall'.$val]='0';}


            \DB::table('Localidades')->insert(array(


                'LOC_CodigoLocalidad'=> $_POST['codigoLocalidadd'.$val],
                'LOC_Nombre'=> $_POST['nombreLocalidadd'.$val],
                'LOC_ALM_AlmacenId'=> $_POST['almacenn'.$val],
                'LOC_Planear'=> $_POST['planearr'.$val],
                'LOC_LocalidadGeneral'=> $_POST['locgenerall'.$val]

            ));
        }

        //return redirect()->route('inventario.localidades.index');

        return json_encode(array());

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
        //return view('Prospectos.editProspecto');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
       // dd("estamos en editar".$id);
       $loc =Localidad::Name($id);
        //dd($loc);
        $almaLoc=Localidad::find($id, array('LOC_ALM_AlmacenId'))->LOC_ALM_AlmacenId;
        $almacen =\DB::table('Almacenes')->where('ALM_Eliminado', '0')->orderBy('ALM_Nombre','ASC')->lists('ALM_Nombre', 'ALM_AlmacenId');

        $al=\DB::table('Almacenes')->where('ALM_Eliminado', '0')->orderBy('ALM_Nombre','ASC')->where('ALM_AlmacenId', '=', $almaLoc)->lists('ALM_Nombre', 'ALM_AlmacenId');
        $alma=$al+$almacen;
       

        return view('Inventario.Localidades.editLocalidad',compact('loc','alma'));


    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id, Request $request)
    {

       $localidad = new Localidad($request->all());
       //  dd($almacen);



        if($localidad->LOC_Planear=="")$localidad->LOC_Planear='0';

      //  dd($localidad);
        \DB::table('Localidades')->where('LOC_LocalidadId', '=', $id)->update(
            array(
              //  'LOC_CodigoLocalidad' => $almacen->LOC_CodigoLocalidad,
                'LOC_Nombre' => $localidad->LOC_Nombre,
                'LOC_ALM_AlmacenId' => $localidad->LOC_ALM_AlmacenId,
                'LOC_Planear' => $localidad->LOC_Planear
            )
        );
        //return redirect()->route('inventario.localidades.index');
        return json_encode(array());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public static function validaEliminarLocalidad($id){
       // dd(LocalidadesArticuloController::existePorLocalidadIdConCantidad($id));
        $ref = GeneralController::buscaReferenciasUso($id,"Localidades");
       // $referencias=GeneralController::ejecutaProcedure(array($id,"Localidades"));
       //dd($ref);

        if(LocalidadesArticuloController::existePorLocalidadIdConCantidad($id)==false){
            return Response::json("No se puede borrar porque contiene articulos");
        }

        if(GeneralController::buscaReferenciasUso($id,"Localidades")==true){
            return Response::json("No se puede eliminar La Localidad se encuentra Predeterminada de Entrada y/o Salida \x0Aen algún(os) articulo(s).");

        }
        if(GeneralController::buscaReferenciasUso($id,"Localidades")!=false){
            return Response::json("No se puede eliminar la localidad, existen referencias:La Localidad contiene artículos.");
        }
        else{
            return Response::json("");
        }

        //return Response::json("No se puede borrar el Almacen, \x0AContiene articulos");

        return json_encode(array());
    }

    public function destroy($id, Request $request)
    {
        $eliminaLocalidadGenral='1';
        \DB::table('Localidades')->where('LOC_LocalidadId', '=', $id)->update(
            array(
                'LOC_Eliminado'=>$eliminaLocalidadGenral
            )
        );

        return json_encode(array());


    }

    //buscar todas las localidades
    public static function buscarlocalidades($idalm){
        $result=\DB::table('Localidades')
            ->where('LOC_Eliminado','=',0)
            ->where('LOC_LocalidadGeneral','=',0)
            ->where('LOC_ALM_AlmacenId','=',$idalm)
            ->orderBy('LOC_Nombre','ASC')
            ->get();
        return Response::json($result);
    }


    /////////////////////////////////////FUNCIONESPARA PROCESADOR DEINVENTARIO FISICO///////////////////////////////////

    public static function buscaLocGralPorAlmacenId($almacenId){
        $localidad=Localidad::where('LOC_ALM_AlmacenId','=',$almacenId)
                ->where('LOC_LocalidadGeneral','=','1')
                ->where('LOC_Eliminado','=','0')
                ->get();
        return $localidad;

    }

    public static function buscaPorCodigoYAlmacenId($codigoLocalidad,$almacenId){
        $localidad=Localidad::where('LOC_CodigoLocalidad','=',$codigoLocalidad)
            ->where('LOC_ALM_AlmacenId','=',$almacenId)
            ->where('LOC_LocalidadGeneral','=','0')
            ->where('LOC_Eliminado','=','0')
            ->get();
        return $localidad;
    }

    public static function buscaBorradoPorCodigoYAlmacenId($codigoLocalidad,$almacenId){
        $localidad=Localidad::find($codigoLocalidad)
            ->where('LOC_ALM_AlmacenId','=',$almacenId)
            ->where('LOC_LocalidadGeneral','=','0')
            ->where('LOC_Eliminado','=','1');
        return $localidad;
    }

    public static function buscaPorId($id){
        $localidad=Localidades::where('LOC_LocalidadId','=',$id)
            ->where('LOC_LocalidadGeneral','=','0')
            ->where('LOC_Eliminado','=','0')
            ->get();
        return $localidad[0];
    }

    public static function buscaCodigoLocalidad($id){
        $localidad=Localidad::find($id);
        $codigo=$localidad->LOC_CodigoLocalidad;
        return $codigo;
    }

    public static function getLocalidadIdGralPorAlmacenGral(){

        $localidadId = \DB::select(
            \DB::raw(
                "select LOC_LocalidadId from Localidades
                inner join Almacenes on ALM_AlmacenId = LOC_ALM_AlmacenId
                where ALM_AlmacenPredeterminado =1 and LOC_General = 1"
            )
        );

        return $localidadId[0]->LOC_LocalidadId;

    }

    /////////////////////////////////FIN FUNCIONESPARA PROCESADOR DEINVENTARIO FISICO///////////////////////////////////
}