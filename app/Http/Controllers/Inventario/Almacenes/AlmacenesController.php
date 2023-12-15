<?php namespace App\Http\Controllers\Inventario\Almacenes;

// use Illuminate\Routing\Route;
// use Illuminate\Support\Facades\Input;
// use Illuminate\Support\Facades\Session;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\Inventario\Localidades\LocalidadesArticuloController;
use App\Http\Controllers\Inventario\Localidades\LocalidadesController;
use App\Http\Controllers\Sistema\DataBaseSession;
//use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePuestos;
use Illuminate\Http\Request;
//use App\Models\AdmonSistema\Ciudad;
//use App\Models\AdmonSistema\Estado;
use App\Models\AdmonSistema\Pais;
use App\Models\Inventario\Almacen;
use App\Models\Inventario\Colonia;
//use App\Models\Inventario\Localidad;
use Response;

class AlmacenesController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    //
    public function codalmacen($id){


        $codalmacen =\DB::table('Almacenes')->where('ALM_CodigoAlmacen', '=', $id)->get();
        //$varr=$cpciudad[0]->CIUC_CodigoPostal;
        //$ciudadelCod=\DB::table('Ciudades')->where('CIU_CiudadId', '=', $varr)->get();

        //dd($varr);
        return Response::json($codalmacen);

    }
    public function codpostalestado($id){

        $ciudadelCod=\DB::table('Ciudades')->where('CIU_CiudadId', '=', $id)->get();
        $idEstado=$ciudadelCod[0]->CIU_EST_EstadoId;
        $estadoid = \DB::table('Estados')->where('EST_EstadoId', '=', $idEstado)->get();
        // $resultES = Estado::find( $idEstado, array('EST_EstadoId'))->EST_Nombre;
        // dd($estadoid);
        return Response::json(  $estadoid);

    }
    public function codpostalpais($id){

        $estadoid = \DB::table('Estados')->where('EST_EstadoId', '=', $id)->get();
        $idEstado=$estadoid[0]->EST_PAI_PaisId;
        $paiscodigop = \DB::table('Paises')->where('PAI_PaisId', '=', $idEstado)->get();
        // $resultES = Estado::find( $idEstado, array('EST_EstadoId'))->EST_Nombre;
        // dd($estadoid);
        return Response::json(  $paiscodigop);

    }
    public function colonia($id){

        //dd($id);
        $cpciudad =\DB::table('CiudadesColonias')->where('CIUC_CodigoPostal', '=', $id)->get();

        return Response::json($cpciudad);



    }

    public function nuevaColonia($id){


        $primeraNuevaColonia =\DB::table('CiudadesColonias')->orderBy('CIUC_Timestamp', 'DESC')->where('CIUC_CodigoPostal', '=', $id)->get();
        //dd($primeraNuevaColonia);
        return Response::json($primeraNuevaColonia);

    }

    public function departamentos(){


        $departamentos=\DB::table('Departamentos')
            ->join('ControlesMaestrosMultiples', 'Departamentos.DEP_CMM_TipoDeptoId', '=', 'ControlesMaestrosMultiples.CMM_ControlId')
            ->where('DEP_Eliminado', '0')->whereRaw("CMM_Valor = 'Sucursal'".(DataBaseSession::isPermisoCorporativo() ? "" : " AND DEP_DeptoId IN(".DataBaseSession::getCediId().")"))->get();


        return Response::json($departamentos);

    }
    public function departamentosAlmacensSucursale($id){


        $almacenesSucursalesDepartamento =\DB::table('AlmacenesSucursales')->where('ALMS_ALM_AlmacenId', '=', $id)->get();

        // dd($almacenesSucursalesDepartamento);
        return Response::json($almacenesSucursalesDepartamento);

    }



    public function codpostal($id){
        $codigopos =\DB::table('CiudadesColonias')->where('CIUC_CodigoPostal', '=', $id)->get();

        $varr=$codigopos[0]->CIUC_CIU_CiudadId;
        //dd($codigopos);
        $ciudadelCod=\DB::table('Ciudades')->where('CIU_CiudadId', '=', $varr)->get();



        return Response::json( $ciudadelCod);

    }
    public function conpais(){

        $sub = \DB::table('Paises')->orderBy('PAI_Nombre','ASC')->get();
        return Response::json($sub);
    }

    public function estadoss($id){

        if($id=="")$id=null;
        if($id!="null"){
            $sub = \DB::table('Estados')->orderBy('EST_Nombre','ASC')->where('EST_PAI_PaisId', '=', $id)->get();
            // dd($sub);
            return Response::json($sub);
        }
    }
    public function ciudadd($id){
        if($id=="")$id=null;
        if($id!="null"){
            $sub = \DB::table('Ciudades')->orderBy('CIU_Nombre','ASC')->where('CIU_EST_EstadoId', '=', $id)->get();
            return Response::json($sub);
        }
    }
    public function index(Request $request)
    {
        //dd("hola");
        $encabezados=array(
            'id',
            'Codigo del almacen',
            'Nombre del almacen',
            'Departamentos'

        );
        $contenidos=array(
            'ALM_AlmacenId',
            'ALM_CodigoAlmacen',
            'ALM_Nombre',
            'Departamentos'

        );

        $Acentamiento=\DB::table('ControlesMaestrosMultiples')->where('CMM_Control', '=','CMM_CRH_TipoAsentamiento' )->where('CMM_Eliminado', '0')->lists('CMM_Valor', 'CMM_ControlId');
        $tipoAcentamiento = array('' => 'Seleccione') + $Acentamiento;
        $Zona=\DB::table('ControlesMaestrosMultiples')->where('CMM_Control', '=','CMM_CRH_ZonaAsentamiento' )->where('CMM_Eliminado', '0')->lists('CMM_Valor', 'CMM_ControlId');
        $zonaAcentamiento = array('' => 'Seleccione') + $Zona;


        $tipalm=\DB::table('ControlesMaestrosMultiples')->where('CMM_Control', '=','CMM_TipoAlmacen' )->lists('CMM_Valor', 'CMM_ControlId');//array('' => 'Seleccione','1L' => '1L','2L' => '2L');
        $tipoalm=array('' => 'Seleccione') +$tipalm;
        $results = \DB::table('Almacenes')
            ->select('ALM_AlmacenId','ALM_CodigoAlmacen','ALM_Nombre','ALM_EMP_ResponsableId','ALM_Direccion','ALM_CIUC_ColoniaId',
                'ALM_CodigoPostal','ALM_CIU_CiudadId','ALM_EST_EstadoId','ALM_PAI_PaisId','ALM_CorreoElectronico',
                'ALM_Telefono','ALM_Extension','ALM_Fax','ALM_AlmacenPredeterminado','ALM_Planear',
                \DB::raw('dbo.getCEDISPorAlmacenId (ALM_AlmacenId) as Departamentos'))
            ->whereRaw(" ALM_Eliminado = 0".(DataBaseSession::isPermisoCorporativo() ? "" : " AND ALM_AlmacenId IN (".DataBaseSession::getAlmacenesPorCediId().")"))
            ->get();
        //dd($results);

        //dd($results);
        $empleados=\DB::table('Empleados')
            ->whereRaw('EMP_Eliminado = 0'. (DataBaseSession::isPermisoCorporativo() ? "" : " AND EMP_DEP_SucursalId IN(".DataBaseSession::getCediId().")"))
            ->lists('EMP_Nombre', 'EMP_EmpleadoId');
        $responsable=array(''=>'seleccione')+$empleados;
        /* $departamentos=\DB::table('Departamentos')
             ->join('ControlesMaestrosMultiples', 'Departamentos.DEP_CMM_TipoDeptoId', '=', 'ControlesMaestrosMultiples.CMM_ControlId')
             ->where('DEP_Eliminado', '0')->where('CMM_Valor', 'Sucursal')->get();
         //$depAlmacen=array(''=>'seleccione')+$departamentos;
         $results=$results+$departamentos;*/
        // dd($results);
        /* $u = \DB::table('Almacenes')
             ->select('ALM_AlmacenId','ALM_CodigoAlmacen','ALM_Nombre',\DB::raw('dbo.getCEDISPorAlmacenId (ALM_AlmacenId) as Departamentos'))
            ->get();
        dd($u);*/





        return view('Inventario.Almacenes.indexAlmacen',compact('tipoalm','results','encabezados','contenidos','responsable','tipoAcentamiento','zonaAcentamiento'));
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
    public function store(CreatePuestos $request)
    {
        //dd("estoy por grabar");
        $almacen=new Almacen($request->all());

        //dd($_POST['DepartamentosId1']);










        if($almacen->ALM_PAI_PaisId=="null")$almacen->ALM_PAI_PaisId=null;
        if($almacen->ALM_EST_EstadoId=="null")$almacen->ALM_EST_EstadoId=null;
        if($almacen->ALM_CIU_CiudadId=="null")$almacen->ALM_CIU_CiudadId=null;
        if($almacen->ALM_PAI_PaisId=="")$almacen->ALM_PAI_PaisId=null;
        if($almacen->ALM_EST_EstadoId=="")$almacen->ALM_EST_EstadoId=null;
        if($almacen->ALM_CIU_CiudadId=="")$almacen->ALM_CIU_CiudadId=null;
        if($almacen->ALM_Planear=="")$almacen->ALM_Planear='0';
        if($almacen->ALM_AlmacenPredeterminado=="")$almacen->ALM_AlmacenPredeterminado='0';
        if($almacen->ALM_CIUC_ColoniaId=="")$almacen->ALM_CIUC_ColoniaId=null;
        if($almacen->ALM_CIUC_ColoniaId=="null")$almacen->ALM_CIUC_ColoniaId=null;
        if($almacen->ALM_CMM_TipoAlmacenId=="null")$almacen->ALM_CMM_TipoAlmacenId=null;
        // dd($almacen);
        \DB::table('Almacenes')->insert(
            array(
                'ALM_CodigoAlmacen' => $almacen->ALM_CodigoAlmacen,
                'ALM_Nombre' => $almacen->ALM_Nombre,
                'ALM_EMP_ResponsableId' => $almacen->ALM_EMP_ResponsableId,
                'ALM_Direccion' => $almacen->ALM_Direccion,
                'ALM_CIUC_ColoniaId' => $almacen->ALM_CIUC_ColoniaId,
                'ALM_CodigoPostal' => $almacen->ALM_CodigoPostal,
                'ALM_CIU_CiudadId' => $almacen->ALM_CIU_CiudadId,
                'ALM_EST_EstadoId' => $almacen->ALM_EST_EstadoId,
                'ALM_PAI_PaisId' => $almacen->ALM_PAI_PaisId,
                'ALM_CorreoElectronico' => $almacen->ALM_CorreoElectronico,
                'ALM_Telefono' => $almacen->ALM_Telefono,
                'ALM_Extension' => $almacen->ALM_Extension,
                'ALM_Fax' => $almacen->ALM_Fax,
                'ALM_AlmacenPredeterminado' => $almacen->ALM_AlmacenPredeterminado,
                'ALM_Planear' => $almacen->ALM_Planear,
                'ALM_CMM_TipoAlmacenId' => $almacen->ALM_CMM_TipoAlmacenId,

                // 'ALM_DEP_DeptoId' => $almacen->ALM_DEP_DeptoId,



            )
        );
        //$localidad = new Localidad($request->all());
        $AlmacenId = Almacen::orderby('ALM_FechaUltimaModificacion', 'DESC')->first()->ALM_AlmacenId;

        //dd( $AlmacenId);

        $codigoLocalidad=$almacen->ALM_CodigoAlmacen.'-'.'Loc-00';
        $nombreLocalidad=$almacen->ALM_CodigoAlmacen.'-'.'Localidad general';
        $localidadPlanear=$almacen->ALM_Planear;
        $localidadGeneral='1';
        // dd($localidad);
        //dd($localidadPlanear);
        \DB::table('Localidades')->insert(
            array(
                'LOC_CodigoLocalidad' => $codigoLocalidad,
                'LOC_Nombre'=>$nombreLocalidad,
                'LOC_ALM_AlmacenId'=>$AlmacenId,
                'LOC_Planear'=>$localidadPlanear,
                'LOC_LocalidadGeneral'=>$localidadGeneral



            )
        );


        //REGISTRAR LOS DEPARTAMENTOS SELECCIONADOS Y ENVIADOS CON EL IMPUT
        for($val=1; $val<=$_POST['CCON_contador3']; $val++){
            \DB::table('AlmacenesSucursales')->insert(
                array(
                    'ALMS_ALM_AlmacenId' => $AlmacenId,
                    'ALMS_DEP_DeptoId' => $_POST['DepartamentosIds'.$val]
                )
            );
        }
        //return redirect()->route('inventario.almacenes.index');
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
        $almacendat = Almacen::Name($id);
        $results = Almacen::find( $id, array('ALM_PAI_PaisId'))->ALM_PAI_PaisId;

        $resultES = Almacen::find( $id, array('ALM_EST_EstadoId'))->ALM_EST_EstadoId;
        $resultCI = Almacen::find( $id, array('ALM_CIU_CiudadId'))->ALM_CIU_CiudadId;


        $resultCo = Almacen::find( $id, array('ALM_CIUC_ColoniaId'))->ALM_CIUC_ColoniaId;





        $estadosss=\DB::table('Estados')->orderBy('EST_Nombre','ASC')->where('EST_PAI_PaisId', '=', $results)->lists('EST_Nombre', 'EST_EstadoId');
        $ciudadesss=\DB::table('Ciudades')->orderBy('CIU_Nombre','ASC')->where('CIU_EST_EstadoId', '=', $resultES)->lists('CIU_Nombre', 'CIU_CiudadId');
        $rePname =\DB::table('Paises')->orderBy('PAI_Nombre','ASC')->where('PAI_PaisId', '=', $results)->lists('PAI_Nombre', 'PAI_PaisId');
        $pais = $rePname + Pais::orderBy('PAI_Nombre','ASC')->lists('PAI_Nombre', 'PAI_PaisId')->all();

        $estadosss=\DB::table('Estados')->orderBy('EST_Nombre','ASC')->where('EST_PAI_PaisId', '=', $results)->lists('EST_Nombre', 'EST_EstadoId');
        $ciudadesss=\DB::table('Ciudades')->orderBy('CIU_Nombre','ASC')->where('CIU_EST_EstadoId', '=', $resultES)->lists('CIU_Nombre', 'CIU_CiudadId');
        $rePname =\DB::table('Paises')->orderBy('PAI_Nombre','ASC')->where('PAI_PaisId', '=', $results)->lists('PAI_Nombre', 'PAI_PaisId');
        $pais = $rePname + Pais::orderBy('PAI_Nombre','ASC')->lists('PAI_Nombre', 'PAI_PaisId')->all();

        $paises = array('' => 'Seleccione') + $pais;
        $reESname =\DB::table('Estados')->orderBy('EST_Nombre','ASC')->where('EST_EstadoId', '=', $resultES)->lists('EST_Nombre', 'EST_EstadoId');
        if($reESname==null){$reESnull=array(''=> 'Seleccione');
            $es = $reESnull + $estadosss;
        }
        else{
            $es = $reESname + $estadosss;
        }
        $reCIname =\DB::table('Ciudades')->orderBy('CIU_Nombre','ASC')->where('CIU_CiudadId', '=', $resultCI)->lists('CIU_Nombre', 'CIU_CiudadId');
        if($reCIname==null){$reCInull=array(''=> 'Seleccione');
            $ci = $reCInull + $ciudadesss;
        }
        else{
            $ci = $reCIname + $ciudadesss;
        }
        //para colonias


        $todasColonias=\DB::table('CiudadesColonias')->orderBy('CIUC_Nombre','ASC')->where('CIUC_CIU_CiudadId', '=', $resultCI)->lists('CIUC_Nombre', 'CIUC_ColoniaId');
        // dd($todasColonias);

        $col=\DB::table('CiudadesColonias')->orderBy('CIUC_Nombre','ASC')->where('CIUC_ColoniaId', '=', $resultCo)->lists('CIUC_Nombre', 'CIUC_ColoniaId');

        if($col==null){$colNull=array(''=> 'Seleccione');
            $colonias = $colNull + $todasColonias;
        }
        else{
            $colonias = $col + $todasColonias;
        }




        $resultEmpleado = Almacen::find( $id, array('ALM_EMP_ResponsableId'))->ALM_EMP_ResponsableId;
        $responsableActual=\DB::table('Empleados')->orderBy('EMP_Nombre','ASC')->where('EMP_EmpleadoId', '=', $resultEmpleado)->lists('EMP_Nombre', 'EMP_EmpleadoId');

        $empleados=\DB::table('Empleados')->where('EMP_Eliminado', '0')->lists('EMP_Nombre', 'EMP_EmpleadoId');
        $responsable=$responsableActual + $empleados;

        // $resultDepartamento = Almacen::find( $id, array('ALM_DEP_DeptoId'))->ALM_DEP_DeptoId;
        // $departamentoActual=\DB::table('Departamentos')->orderBy('DEP_Nombre','ASC')->where('DEP_DeptoId', '=', $resultDepartamento)->lists('DEP_Nombre', 'DEP_DeptoId');

        /* $departamentos=\DB::table('Departamentos')

             ->join('ControlesMaestrosMultiples', 'Departamentos.DEP_CMM_TipoDeptoId', '=', 'ControlesMaestrosMultiples.CMM_ControlId')
             ->where('DEP_Eliminado', '0')->where('CMM_Valor', 'Sucursal')->lists('DEP_Nombre', 'DEP_DeptoId');
         $depAlmacen=$departamentoActual+$departamentos;    */





        $Acentamiento=\DB::table('ControlesMaestrosMultiples')->where('CMM_Control', '=','CMM_CRH_TipoAsentamiento' )->where('CMM_Eliminado', '0')->lists('CMM_Valor', 'CMM_ControlId');
        $tipoAcentamiento = array('' => 'Seleccione') + $Acentamiento;
        $Zona=\DB::table('ControlesMaestrosMultiples')->where('CMM_Control', '=','CMM_CRH_ZonaAsentamiento' )->where('CMM_Eliminado', '0')->lists('CMM_Valor', 'CMM_ControlId');
        $zonaAcentamiento = array('' => 'Seleccione') + $Zona;




        //$tipoalm=array('' => 'Seleccione','1L' => '1L','C820AF13-9CDD-4E8F-8E5B-EF385484D30F' => '2L','3L' => '3L');
        $tipalm=\DB::table('ControlesMaestrosMultiples')->where('CMM_Control', '=','CMM_TipoAlmacen' )->lists('CMM_Valor', 'CMM_ControlId');//array('' => 'Seleccione','1L' => '1L','2L' => '2L');
        $tipoalm=array('' => 'Seleccione') +$tipalm;
        //dd($almacendat);
        return view('Inventario.Almacenes.editAlmacen',compact('tipoalm','almacendat','paises','es','ci','responsable','tipoAcentamiento','zonaAcentamiento','colonias'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id, Request $request)
    {

        $almacen = new Almacen($request->all());
        //  dd($almacen);


        if($almacen->ALM_PAI_PaisId=="null")$almacen->ALM_PAI_PaisId=null;
        if($almacen->ALM_EST_EstadoId=="null")$almacen->ALM_EST_EstadoId=null;
        if($almacen->ALM_CIU_CiudadId=="null")$almacen->ALM_CIU_CiudadId=null;
        if($almacen->ALM_PAI_PaisId=="")$almacen->ALM_PAI_PaisId=null;
        if($almacen->ALM_EST_EstadoId=="")$almacen->ALM_EST_EstadoId=null;
        if($almacen->ALM_CIU_CiudadId=="")$almacen->ALM_CIU_CiudadId=null;
        if($almacen->ALM_Planear=="")$almacen->ALM_Planear='0';
        if($almacen->ALM_AlmacenPredeterminado=="")$almacen->ALM_AlmacenPredeterminado='0';
        if($almacen->ALM_CIUC_ColoniaId=="")$almacen->ALM_CIUC_ColoniaId=null;
        if($almacen->ALM_CIUC_ColoniaId=="null")$almacen->ALM_CIUC_ColoniaId=null;
        if($almacen->ALM_CMM_TipoAlmacenId=="null")$almacen->ALM_CMM_TipoAlmacenId=null;
      //   dd($almacen);



        \DB::table('Almacenes')->where('ALM_AlmacenId', '=', $id)->update(
            array(
                //  'ALM_CodigoAlmacen' => $almacen->ALM_CodigoAlmacen,
                'ALM_Nombre' => $almacen->ALM_Nombre,
                'ALM_EMP_ResponsableId' => $almacen->ALM_EMP_ResponsableId,
                'ALM_Direccion' => $almacen->ALM_Direccion,
                'ALM_CIUC_ColoniaId' => $almacen->ALM_CIUC_ColoniaId,
                'ALM_CodigoPostal' => $almacen->ALM_CodigoPostal,
                'ALM_CIU_CiudadId' => $almacen->ALM_CIU_CiudadId,
                'ALM_EST_EstadoId' => $almacen->ALM_EST_EstadoId,
                'ALM_PAI_PaisId' => $almacen->ALM_PAI_PaisId,
                'ALM_CorreoElectronico' => $almacen->ALM_CorreoElectronico,
                'ALM_Telefono' => $almacen->ALM_Telefono,
                'ALM_Extension' => $almacen->ALM_Extension,
                'ALM_Fax' => $almacen->ALM_Fax,
                'ALM_AlmacenPredeterminado' => $almacen->ALM_AlmacenPredeterminado,
                'ALM_Planear' => $almacen->ALM_Planear,
                'ALM_CMM_TipoAlmacenId' => $almacen->ALM_CMM_TipoAlmacenId,
                //
                //'ALM_DEP_DeptoId' => $almacen->ALM_DEP_DeptoId



            )
        );

        ///primero elimino los departamentos relacionados

        \DB::table('AlmacenesSucursales')->where('ALMS_ALM_AlmacenId', '=', $id)->delete();
        //despues registro todos los chequeados :)))

        //REGISTRAR LOS DEPARTAMENTOS SELECCIONADOS Y ENVIADOS CON EL IMPUT
        for($val=1; $val<=$_POST['CCON_contador3']; $val++){
            \DB::table('AlmacenesSucursales')->where('ALMS_ALM_AlmacenId', '=', $id)->insert(
                array(
                    'ALMS_ALM_AlmacenId' => $id,
                    'ALMS_DEP_DeptoId' => $_POST['DepartamentosIds'.$val]
                )
            );
        }


        //return redirect()->route('inventario.almacenes.index');
        return json_encode(array());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public static function  isAlmacenPredeterminado($id){

        $almacenPredeterminado= \DB::table('Almacenes')->where('ALM_AlmacenId', $id)->where('ALM_AlmacenPredeterminado','1')->where('ALM_Eliminado','0')->get();
        //return false
        if( $almacenPredeterminado==null||$almacenPredeterminado==""){
            return false;

        }
        else{
            return true;

        }

    }
    public static function validaEliminar($id){


        //   dd( $localidadLocGral);
        // $calar=GeneralController::buscaReferenciasUso($localidadLocGral,"Almacenes");


        //Verificar que no sea el Almacén Predeterminado o Almacen General el que se borra
        if(AlmacenesController::isAlmacenPredeterminado($id)==false){
            //Buscar si el Almacen tiene alguna relación con algun articulo
            //y de ser cierto verificar si se encuentran en 0 las cantidades
            $localidadLocGral = LocalidadesController::getLocalidadGralPorAlmacenId($id);
            if(LocalidadesArticuloController::existePorLocalidadId($localidadLocGral)==true
                && LocalidadesArticuloController::existePorLocalidadIdConCantidad($localidadLocGral)==false){
                return Response::json("No se puede borrar el Almacen, \x0AContiene articulos");
            }
            //Verificar que el Almacén no tenga Localidades
            elseif(LocalidadesController::buscaTodosPorCodigoAlmacen($id)==false){
                return Response::json("No se puede borrar el Almacen \x0AAntes debe borrar todas sus localidades");
            }
            else{
                //Buscar si el Almacen es un Alamcen Predetermindao de Entrada y/o Salida de algún Articulo
                //Primero se busca la localidad General del Alamcen
                if(GeneralController::buscaReferenciasUso($localidadLocGral,"Almacenes")==true){
                    return Response::json("Algunos artículos tiene este Almacén como Almacen Predeterminado de Entrada y/o Salida. \x0AEs necesario eliminar antes esa relación con el artículo para poder borrar este Alamcén.");

                }
                else{
                    return Response::json("");
                }


            }



        }else{
            return Response::json("     El almacen No se puede borrar \x0A     Es Prdeterminado");
        }

        return json_encode(array());
    }

    public function destroy($id, Request $request)
    {
//dd("seelimino  :( ");
        $almacen = new Almacen($request->all());
        //dd('estas queriendo eliminar');
        $eliminaAlmacen='1';

        \DB::table('Almacenes')->where('ALM_AlmacenId', '=', $id)->update(
            array(
                'ALM_Eliminado'=>$eliminaAlmacen
            )
        );
        $eliminaLocalidadGenral='1';
        \DB::table('Localidades')->where('LOC_ALM_AlmacenId', '=', $id)->update(
            array(
                'LOC_Eliminado'=>$eliminaLocalidadGenral
            )
        );
        //return redirect()->route('inventario.almacenes.index');
        return json_encode(array());



    }
    public function nuevaCol(Request $request)
    {
//dd("estasqueriendo guardar una nueva localidad jajajajaja");
        $user = new Colonia($request->all());

//dd($user);
        // dd( $request->Ciudad);



        \DB::table('CiudadesColonias')->insert(
            array(
                'CIUC_CIU_CiudadId' => $request->Ciudad,
                'CIUC_ClaveCColonia' => $user->CIUC_ClaveCColonia,
                'CIUC_Nombre' => $user->CIUC_Nombre,
                'CIUC_CMM_TipoAsentamientoId' => $user->CIUC_CMM_TipoAsentamientoId,
                'CIUC_CMM_ZonaAsentamientoId' => $user->CIUC_CMM_ZonaAsentamientoId,
                'CIUC_CodigoPostal' => $request->CodigoPostal,





            )
        );

        return json_encode(array());



    }

    //buscar todos losalmacenes
    public static function buscaralmacenes(){
        $result=\DB::table('Almacenes')->where('ALM_Eliminado', '0')->orderBy('ALM_Nombre','ASC')->get();
        return Response::json($result);
    }

    /////////////////////////////////////FUNCIONESPARA PROCESADOR DEINVENTARIO FISICO///////////////////////////////////

    //funcion para buscar almacen (id almacen)
    public static function buscaPorId($almacen_id){
        $almacen=Almacen::where('ALM_Eliminado','=','0')->where('ALM_AlmacenId','=',$almacen_id)->get();
        return $almacen;
    }

    /////////////////////////////////FIN FUNCIONESPARA PROCESADOR DEINVENTARIO FISICO///////////////////////////////////
}