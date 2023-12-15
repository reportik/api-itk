<?php namespace App\Http\Controllers\Inventario\Articulos;

// use Illuminate\Support\Facades\File;
// use Illuminate\Support\Facades\Input;
// use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Sistema\DAOGeneralController;
use App\Http\Controllers\Sistema\DataBaseSession;
//use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Http\Requests\CreateArticulosRequest;
//use App\Http\Requests\Inventario\Articulos\CreateArticulosRequest;
use App\Mapeos\Controles\ControlesMaestros;
use App\Models\AdmonSistema\ControlMaestro;
use App\Models\Articulos;
use App\Models\ArticulosFormasEmpaque;
use App\Models\ArticulosLocalidades;
use App\Models\ControlesMaestrosUM;
use App\Models\Inventario\Articulos\Almacen;
use App\Models\Inventario\Articulos\Articulo;
use App\Models\Inventario\Articulos\ArticuloMarca;
use App\Models\Inventario\Articulos\CMMult;
use App\Models\Inventario\Articulos\FactoresConversion;
use App\Models\Inventario\Articulos\Familia;
use App\Models\Inventario\Articulos\Categoria;
use App\Models\Inventario\Articulos\LocalidadesArticulo;
use App\Models\Inventario\Articulos\UMInventario;
use App\Models\Inventario\Articulos\TipoArticulo;
use App\Models\Inventario\Articulos\Localidad;
use App\Models\Inventario\Articulos\IVAPredeterminado;
//use Symfony\Component\HttpFoundation\File\UploadedFile;
use Response;


class ArticulosController extends Controller {

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

        $idEmpleado = DataBaseSession::getEmpleadoId();

        //generar arreglo para los encabezados de la tabla en el buscador
        $encabezados =array(
            'Id',
            'Código del Producto',
            'Nombre',
            'Familia',
            'Categoría',
            'Marca',
            'Tipo Articulo'
        );
        //generar arreglo para el contenido de la tabla en el buscador
        $contenidos=array(
            'ART_ArticuloId',
            'ART_CodigoArticulo',
            'ART_Nombre',
            'AFAM_Nombre',
            'ACAT_Nombre',
            'ARTM_Nombre',
            'ATP_Descripcion'
        );

        /////////////////////////consultas para la inicializacion de la ficha del articulo////////////////////////////////
        //$familias=array(''=>'') + Familia::select('AFAM_FamiliaId',  \DB::raw("AFAM_Codigo + ' - ' + AFAM_Nombre AS FULL_NAME"))
        $familias=array(''=>'') + Familia::select('AFAM_FamiliaId',  \DB::raw("AFAM_Nombre AS FULL_NAME"))
                ->where('AFAM_Fabricado','=',1)
                ->orderBy('AFAM_Nombre','ASC')
                ->lists('FULL_NAME', 'AFAM_FamiliaId')->all();
        //$categorias=array(''=>'') + Categoria::select('ACAT_CategoriaId',  \DB::raw("ACAT_Codigo + ' - ' + ACAT_Nombre AS FULL_NAME"))
        $categorias=array(''=>'') + Categoria::select('ACAT_CategoriaId',  \DB::raw("ACAT_Nombre AS FULL_NAME"))
                ->where('ACAT_Fabricado','=',1)
                ->orderBy('ACAT_Nombre','ASC')
                ->lists('FULL_NAME', 'ACAT_CategoriaId')->all();
        //$marcas=array(''=>'') + ArticuloMarca::select('ARTM_MarcaId',  \DB::raw("ARTM_Codigo + ' - ' + ARTM_Nombre AS FULL_NAME"))
        $marcas=array(''=>'') + ArticuloMarca::select('ARTM_MarcaId',  \DB::raw("ARTM_Nombre AS FULL_NAME"))
                ->orderBy('ARTM_Nombre','ASC')
                ->lists('FULL_NAME', 'ARTM_MarcaId')->all();
        $subcategorias=array(''=>'') + CMMult::where('CMM_Control', '=', 'CMM_INV_SubcategoriaArticulos')
                ->where('CMM_DefinidoPorUsuario3','=',1)
                ->orderBy('CMM_Valor','ASC')
                ->get()->lists('CMM_Valor', 'CMM_ControlId')->all();
        $inventarios=array(''=>'') + UMInventario::select('CMUM_UnidadMedidaId',  \DB::raw("CMUM_Nombre"))
                ->orderBy('CMUM_Nombre','ASC')
                ->lists('CMUM_Nombre','CMUM_UnidadMedidaId')->all();
        $tipoarticulos=array(''=>'') + TipoArticulo::where('ATP_Fabricado', '=', 1)
                ->orderBy('ATP_Descripcion','ASC')
                ->get()->lists('ATP_Descripcion','ATP_TipoId')->all();
        $ivaspredetermiados=array(''=>'') + IVAPredeterminado::select('CMIVA_IVAId','CMIVA_Porcentaje')
                ->where('CMIVA_CMM_TipoIVA','=','A5585C2F-CD77-4641-8853-2926FD929C91')
                ->lists('CMIVA_Porcentaje','CMIVA_IVAId')->all();
        //$almacenes = Almacen::select('ALM_AlmacenId',  \DB::raw("ALM_CodigoAlmacen + ' - ' + ALM_Nombre AS FULL_NAME"))
        $almacenes = Almacen::select('ALM_AlmacenId',  \DB::raw("ALM_Nombre AS FULL_NAME"))
            ->where('ALM_Eliminado','=',0)
            ->orderby('ALM_Nombre','ASC')
            ->lists('FULL_NAME','ALM_AlmacenId')->all();
        //$almacenes2 = Almacen::select('ALM_AlmacenId',  \DB::raw("ALM_CodigoAlmacen + ' - ' + ALM_Nombre AS FULL_NAME"))
        $almacenes2 = Almacen::select('ALM_AlmacenId',  \DB::raw("ALM_Nombre AS FULL_NAME"))
            ->where('ALM_Eliminado','=',0)
            ->orderby('ALM_Nombre','ASC')
            ->lists('FULL_NAME','ALM_AlmacenId')->all();
        //$almacenes3 = Almacen::select('ALM_AlmacenId',  \DB::raw("ALM_CodigoAlmacen + ' - ' + ALM_Nombre AS FULL_NAME"))
        $almacenes3 = Almacen::select('ALM_AlmacenId',  \DB::raw("ALM_Nombre AS FULL_NAME"))
            ->where('ALM_Eliminado','=',0)
            ->orderby('ALM_Nombre','ASC')
            ->lists('FULL_NAME','ALM_AlmacenId')->all();
        $localidades=Localidad::where('LOC_LocalidadGeneral','=',0)->where('LOC_Eliminado','=',0)->orderby('LOC_CodigoLocalidad','ASC')->lists('LOC_Nombre','LOC_LocalidadId')->all();
        $codigosciclo = array('' => '') + CMMult::where('CMM_Control', '=', 'CMM_INV_CodigoCiclo')->get()->lists('CMM_Valor', 'CMM_ControlId')->all();
        $manejosinventario = array('' => '') + CMMult::where('CMM_Control', '=', 'CMM_INV_ManejoInventario')->get()->lists('CMM_Valor', 'CMM_ControlId')->all();
        $politicasordenes =CMMult::where('CMM_Control', '=', 'CMM_INV_PoliticaOrden')->orderBy('CMM_Valor','DESC')->get()->lists('CMM_Valor', 'CMM_ControlId')->all();
        $factoresconversion=array('' => '');

        /////arreglo para la tabla de las especificaciones///////////
        $especificaciones=array(
            'CMM_Valor',
            'CMM_ControlId'
        );
        ////////////////////////////////////////////////////////////

        $especificaciones2=\DB::table('ControlesMaestrosMultiples')
            ->where('CMM_Control', '=', 'CMM_INV_ArticulosEspecificaciones')
            ->where('CMM_Eliminado', '=', 0)
            ->select($especificaciones)
            ->get();

        $results=\DB::table('Articulos')
            ->join('ArticulosFamilias', 'Articulos.ART_AFAM_FamiliaId', '=', 'ArticulosFamilias.AFAM_FamiliaId')
            ->join('ArticulosCategorias', 'Articulos.ART_ACAT_CategoriaId', '=', 'ArticulosCategorias.ACAT_CategoriaId')
            ->join('ArticulosMarcas', 'Articulos.ART_ARTM_MarcaId', '=', 'ArticulosMarcas.ARTM_MarcaId')
            ->join('ArticulosTipos', 'Articulos.ART_ATP_TipoId', '=', 'ArticulosTipos.ATP_TipoId')
            ->where('Articulos.ART_Eliminado','=',0)
            ->where('ArticulosTipos.ATP_Fabricado','=',1)
            ->select($contenidos)
            ->get();

        $imagen = null;

        $formaEmpaque = array('' => 'Seleccione formas de empaque') + CMMult::where('CMM_Control', '=', 'CMM_FormaEmpaque')->get()->lists('CMM_Valor', 'CMM_ControlId')->all();

        $ConsultaFormaEmpaqueRegistro = \DB::table('ArticulosFormasEmpaque')
            ->where('AFE_Eliminado', '=', 0)
            ->select('AFE_CMM_FormaEmpaqueId')
            ->get();

        $formaEmpaqueRegistro = array($ConsultaFormaEmpaqueRegistro);

        $version = $this->dao->nuevoId();

        return view('Inventario.Articulos.create', compact('results','imagen', 'encabezados', 'contenidos', 'familias',
            'categorias','inventarios', 'tipoarticulos', 'ivaspredetermiados', 'localidades', 'codigosciclo', 'manejosinventario',
            'politicasordenes', 'almacenes', 'almacenes2', 'almacenes3', 'factoresconversion', 'especificaciones', 'especificaciones2',
            'marcas', 'subcategorias','formaEmpaque','formaEmpaqueRegistro','version'));
    }

    public static function consultarcantidaddecimales(){
        $sub=ControlMaestro::select('CMA_Valor')
            ->where('CMA_Control','=',ControlesMaestros::$CMA_INV_DecimalesCantidades)
            ->get();
        return Response::json($sub);
    }

    public function consultarlocalidadesarticulo($id){
        //CARGAR DATOS DE LA TABLA DE LOCALIDADESARTICULO
        $localidadesarticulo=LocalidadesArticulo::select('Almacenes.ALM_Nombre','Localidades.LOC_Nombre','LocalidadesArticulo.LOCA_Cantidad')
            ->join('Localidades', 'Localidades.LOC_LocalidadId', '=', 'LocalidadesArticulo.LOCA_LOC_LocalidadId')
            ->join('Almacenes', 'Localidades.LOC_ALM_AlmacenId', '=', 'Almacenes.ALM_AlmacenId')
            ->where('LocalidadesArticulo.LOCA_ART_ArticuloId', '=', $id)
            ->get();
        return Response::json($localidadesarticulo);
        //////////////////////////////////////////////////
    }

    public function locpredentradas($id){
        $sub = \DB::table('Localidades')
            ->where('LOC_ALM_AlmacenId', '=', $id)
            ->where('LOC_Eliminado', '=', 0)
            ->where('LOC_LocalidadGeneral', '=', 0)
            ->select('LOC_LocalidadId','LOC_Nombre')
            ->orderby('LOC_Nombre','ASC')
            ->get();
        return Response::json($sub);
    }

    public function locpredsalidas($id){
        $sub = \DB::table('Localidades')
            ->where('LOC_ALM_AlmacenId', '=', $id)
            ->where('LOC_Eliminado', '=', 0)
            ->where('LOC_LocalidadGeneral', '=', 0)
            ->select('LOC_LocalidadId','LOC_Nombre')
            ->orderby('LOC_Nombre','ASC')
            ->get();
        return Response::json($sub);
    }

    public function UMInventario($id){
        $sub = \DB::table('ControlesMaestrosUM')
            ->where('CMUM_UnidadMedidaId', '=', $id)
            ->get();
        return Response::json($sub);
    }

    public function UMConversion($id,$id2){
        $sub = \DB::table('ArticulosFactoresConversion')
            ->where('AFC_CMUM_UnidadMedidaId', '=', $id)
            ->where('AFC_ART_ArticuloId', '=', $id2)
            ->get();
        return Response::json($sub);
    }

    public function UMAdicionales($id){
        $sub=FactoresConversion::select('ArticulosFactoresConversion.AFC_CMUM_UnidadMedidaId','ArticulosFactoresConversion.AFC_FactorConversion','ControlesMaestrosUM.CMUM_Nombre')
            ->join('ControlesMaestrosUM', 'ArticulosFactoresConversion.AFC_CMUM_UnidadMedidaId', '=', 'ControlesMaestrosUM.CMUM_UnidadMedidaId')
            ->where('ArticulosFactoresConversion.AFC_ART_ArticuloId', '=', $id)
            ->orderby('ArticulosFactoresConversion.AFC_FactorDefault','DESC')
            ->get();
        return Response::json($sub);
    }

    public function BuscarCodigoArt($id){
        $sub = \DB::table('Articulos')
            ->where('ART_CodigoArticulo', '=', $id)
            ->where('ART_Eliminado','=',0)
            ->where('ART_Activo','=',1)
            ->get();
        return Response::json($sub);
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
    public function store(CreateArticulosRequest $request)
    {

        \DB::beginTransaction();

        try {

            $idEmpleado = DataBaseSession::getEmpleadoId();

            //cachar variables de almacenes ent y sal. antes de asignar al modelo
            $ART_ALM_AlmPredEntradasId=$request->ART_ALM_AlmPredEntradasId;
            $ART_ALM_AlmPredSalidasId=$request->ART_ALM_AlmPredSalidasId;
            $registra = new Articulo($request->all());

            /////////////////buscar almacenes predeterminados de entradas////////////////
            if($ART_ALM_AlmPredEntradasId !="" && ($registra->ART_LOC_LocPredEntradasId=='null' || $registra->ART_LOC_LocPredEntradasId=='')){

                $sub = \DB::table('Localidades')
                    ->where('LOC_ALM_AlmacenId', '=', $ART_ALM_AlmPredEntradasId)
                    ->where('LOC_Eliminado', '=', 0)
                    ->where('LOC_LocalidadGeneral', '=', 1)
                    ->select('LOC_LocalidadId')
                    ->get();

                if(count($sub)>0){
                    $idloc=$sub[0]->LOC_LocalidadId;
                    $registra->ART_LOC_LocPredEntradasId=$idloc;
                }
                else{
                    $registra->ART_LOC_LocPredEntradasId="";
                }

            }

            /////////////////buscar almacenes predeterminados de salidas////////////////
            if($ART_ALM_AlmPredSalidasId !="" && ($registra->ART_LOC_LocPredSalidasId=='null' || $registra->ART_LOC_LocPredSalidasId=='')){
                $sub = \DB::table('Localidades')
                    ->where('LOC_ALM_AlmacenId', '=', $ART_ALM_AlmPredSalidasId)
                    ->where('LOC_Eliminado', '=', 0)
                    ->where('LOC_LocalidadGeneral', '=', 1)
                    ->select('LOC_LocalidadId')
                    ->get();

                if(count($sub)>0){
                    $idloc=$sub[0]->LOC_LocalidadId;
                    $registra->ART_LOC_LocPredSalidasId=$idloc;
                }
                else{
                    $registra->ART_LOC_LocPredSalidasId="";
                }

            }

            //VALIDAR VALORES NULL DE SELECT COMO UNIQUEREQUIRED
            if($registra->ART_CMUM_UMInventarioId=="")$registra->ART_CMUM_UMInventarioId=null;
            if($registra->ART_LOC_LocPredEntradasId=="")$registra->ART_LOC_LocPredEntradasId=null;
            if($registra->ART_LOC_LocPredSalidasId=="" || $registra->ART_LOC_LocPredSalidasId=="null")$registra->ART_LOC_LocPredSalidasId=null;
            if($registra->ART_CMUM_UMConversionOCId=="")$registra->ART_CMUM_UMConversionOCId=null;
            if($registra->ART_CMUM_UMConversionOVId=="")$registra->ART_CMUM_UMConversionOVId=null;
            if($registra->ART_CMUM_UMConversionOTId=="")$registra->ART_CMUM_UMConversionOTId=null;
            if($registra->ART_AFAM_FamiliaId=="")$registra->ART_AFAM_FamiliaId=null;
            if($registra->ART_ACAT_CategoriaId=="")$registra->ART_ACAT_CategoriaId=null;
            if($registra->ART_CMM_SubcategoriaId=="")$registra->ART_CMM_SubcategoriaId=null;
            if($registra->ART_ARTM_MarcaId=="")$registra->ART_ARTM_MarcaId=null;
            if($registra->ART_ATP_TipoId=="")$registra->ART_ATP_TipoId=null;
            if($registra->ART_CMM_ManejoInventarioId=="")$registra->ART_CMM_ManejoInventarioId=null;
            if($registra->ART_CMM_PoliticaOrdenesId=="")$registra->ART_CMM_PoliticaOrdenesId=null;
            if($registra->ART_CMM_CodigoCicloId=="")$registra->ART_CMM_CodigoCicloId=null;
            if($registra->ART_Precio=="")$registra->ART_Precio=null;
            if($registra->ART_CantidadAMano=="")$registra->ART_CantidadAMano=null;
            if($registra->ART_PorcentajeComision=="")$registra->ART_PorcentajeComision=null;
            if($registra->ART_CMM_IVAPredeterminadoId=="")$registra->ART_CMM_IVAPredeterminadoId=null;

            if($registra->ART_CantidadUMEmpaqueEnCaja=="")$registra->ART_CantidadUMEmpaqueEnCaja=null;
            if($registra->ART_CantidadCajasEnPallet=="")$registra->ART_CantidadCajasEnPallet=null;
            if($registra->ART_DiasVidaAnaquel=="")$registra->ART_DiasVidaAnaquel=null;

            if($registra->ART_Departamento=="")$registra->ART_Departamento=null;
            if($registra->ART_Pasta=="")$registra->ART_Pasta=null;
            if($registra->ART_Presentacion=="")$registra->ART_Presentacion=null;

            //VALIDARVALORES NULL DE INPUT Y CHECKBOX, COMO BIT, INT Y DOUBLE
            if($registra->ART_SeguimientoLocMult==null)$registra->ART_SeguimientoLocMult=0;
            if($registra->ART_Obsoleto==null)$registra->ART_Obsoleto=0;
            if($request->ART_DegustableDetalle==null)$registra->ART_DegustableDetalle=0;
            if($request->ART_DegustableAutoservicio==null)$registra->ART_DegustableAutoservicio=0;
            if($registra->ART_Consignable==null)$registra->ART_Consignable=0;
            if($registra->ART_PermitirCambioAlmacen==null)$registra->ART_PermitirCambioAlmacen=0;
            if($registra->ART_SeguimientoLocMult==null)$registra->ART_SeguimientoLocMult=0;
            if($registra->ART_CrearLocAlmacenaje==null)$registra->ART_CrearLocAlmacenaje=0;
            if($registra->ART_OcultarLocsCantCero==null)$registra->ART_OcultarLocsCantCero=0;
            if($registra->ART_SeguimientoLotMult==null)$registra->ART_SeguimientoLotMult=0;
            if($registra->ART_RendimientoEstandar==null)$registra->ART_RendimientoEstandar=0;
            if($registra->ART_CantMinimaOrden==null)$registra->ART_CantMinimaOrden=0;
            if($registra->ART_CantMaximaOrden==null)$registra->ART_CantMaximaOrden=0;
            if($registra->ART_CantMultipleOrden==null)$registra->ART_CantMultipleOrden=0;
            if($registra->ART_NoDiasAbastecimiento==null)$registra->ART_NoDiasAbastecimiento=0;
            if($registra->ART_CantOrdenEconomica==null)$registra->ART_CantOrdenEconomica=0;
            if($registra->ART_CantPuntoOrden==null)$registra->ART_CantPuntoOrden=0;
            if($registra->ART_OmitirOT==null)$registra->ART_OmitirOT=0;
            if($registra->ART_IncluirEmpaque==null)$registra->ART_IncluirEmpaque=0;
            if($registra->ART_DeduccionRetroactivaMaterial==null)$registra->ART_DeduccionRetroactivaMaterial=0;
            if($registra->ART_DeduccionRetroactivaManoObra==null)$registra->ART_DeduccionRetroactivaManoObra=0;
            if($registra->ART_InspeccionOC==null)$registra->ART_InspeccionOC=0;
            if($registra->ART_InspeccionOT==null)$registra->ART_InspeccionOT=0;
            if($registra->ART_OTDependiente==null)$registra->ART_OTDependiente=0;
            if($registra->ART_EstandarPredeterminado==null)$registra->ART_EstandarPredeterminado=0;
            if($registra->ART_NoParticionar==null)$registra->ART_NoParticionar=0;
            if($registra->ART_OmitirPlaneacionOC==null)$registra->ART_OmitirPlaneacionOC=0;
            if($registra->ART_ManejarDecimalesVentaRuta==null)$registra->ART_ManejarDecimalesVentaRuta=0;

            //////////////////validar la imagen///////////////////////
//            if($registra->ART_Imagen!=""){
//                $imageName = $request->file('ART_Imagen')->getClientOriginalName();
//                $i = 0;
//                $info = explode(".",$imageName);
//                $miImg = $imageName;
//                while(file_exists(base_path() . '/public/img/articulos/'. $miImg)){
//                    $i++;
//                    $miImg = $info[0]."(".$i.")".".".$info[1];
//                }
//                $request->file('ART_Imagen')->move(base_path() . '/public/img/articulos/', $miImg);
//                $registra->ART_Imagen=$miImg;
//            }

            //PONER OBSOLETO SIEMPRE AL REGISTAR EN 0
            $registra->ART_Obsoleto=0;
            /////////////////////////////////////////

            $id = \App\Http\Controllers\Embarques\EmbarquesController::getNuevoId();

            \DB::table('Articulos')->insert(
                array(
                    'ART_ArticuloId' => $id,
                    'ART_CodigoArticulo' => $registra->ART_CodigoArticulo,
                    'ART_Nombre' => $registra->ART_Nombre,
                    'ART_Activo' => $registra->ART_Activo,
                    'ART_Anaquel' => $registra->ART_Anaquel,
                    'ART_Comentarios' => $registra->ART_Comentarios,
                    //'ART_Imagen' => $registra->ART_Imagen,
                    'ART_Precio' => $registra->ART_Precio,
                    'ART_CantidadAMano' => $registra->ART_CantidadAMano,
                    'ART_PorcentajeComision' => $registra->ART_PorcentajeComision,
                    'ART_SeguimientoLocMult' => $registra->ART_SeguimientoLocMult,
                    'ART_PermitirCambioAlmacen' => $registra->ART_PermitirCambioAlmacen,
                    'ART_CrearLocAlmacenaje' => $registra->ART_CrearLocAlmacenaje,
                    'ART_OcultarLocsCantCero' => $registra->ART_OcultarLocsCantCero,
                    'ART_SeguimientoLotMult' => $registra->ART_SeguimientoLotMult,
                    'ART_RendimientoEstandar' => floatval($registra->ART_RendimientoEstandar),
                    'ART_CantMinimaOrden' => $registra->ART_CantMinimaOrden,
                    'ART_CantMaximaOrden' => $registra->ART_CantMaximaOrden,
                    'ART_CantMultipleOrden' => $registra->ART_CantMultipleOrden,
                    'ART_NoDiasAbastecimiento' => intval($registra->ART_NoDiasAbastecimiento),
                    'ART_CantOrdenEconomica' => $registra->ART_CantOrdenEconomica,
                    'ART_CantPuntoOrden' => $registra->ART_CantPuntoOrden,
                    'ART_Eliminado' => $registra->ART_Eliminado,
                    'ART_Obsoleto' => $registra->ART_Obsoleto,
                    'ART_GLN' => $registra->ART_GLN,
                    'ART_DegustableDetalle' => $request->ART_DegustableDetalle == null ? 0 : $request->ART_DegustableDetalle,
                    'ART_DegustableAutoservicio' => $request->ART_DegustableAutoservicio == null ? 0 : $request->ART_DegustableAutoservicio,
                    'ART_Consignable' => $registra->ART_Consignable,
                    'ART_CantidadLotesMostrar' => $registra->ART_CantidadLotesMostrar,
                    'ART_DiasVigencia' => $registra->ART_DiasVigencia,
                    'ART_ManejarDecimalesVentaRuta' => $registra->ART_ManejarDecimalesVentaRuta,
                    'ART_OmitirOT' => $registra->ART_OmitirOT,
                    'ART_IncluirEmpaque' => $registra->ART_IncluirEmpaque,
                    'ART_DeduccionRetroactivaMaterial' => $registra->ART_DeduccionRetroactivaMaterial,
                    'ART_DeduccionRetroactivaManoObra' => $registra->ART_DeduccionRetroactivaManoObra,
                    'ART_OmitirPlaneacionOC' => $registra->ART_OmitirPlaneacionOC,
                    'ART_InspeccionOC' => $registra->ART_InspeccionOC,
                    'ART_InspeccionOT' => $registra->ART_InspeccionOT,
                    'ART_OTDependiente' => $registra->ART_OTDependiente,
                    'ART_EstandarPredeterminado' => $registra->ART_EstandarPredeterminado,
                    'ART_NoParticionar' => $registra->ART_NoParticionar,

                    'ART_CMUM_UMEmpaqueId' => $registra->ART_CMUM_UMInventarioId,
                    'ART_CantidadUMEmpaqueEnCaja' => $registra->ART_CantidadUMEmpaqueEnCaja,
                    'ART_CantidadCajasEnPallet' => $registra->ART_CantidadCajasEnPallet,
                    'ART_DiasVidaAnaquel' => $registra->ART_DiasVidaAnaquel,

                    'ART_Departamento' => $registra->ART_Departamento,
                    'ART_Pasta' => $registra->ART_Pasta,
                    'ART_Presentacion' => $registra->ART_Presentacion,

                    'ART_ATP_TipoId' => $registra->ART_ATP_TipoId,
                    'ART_AFAM_FamiliaId' => $registra->ART_AFAM_FamiliaId,
                    'ART_ACAT_CategoriaId' => $registra->ART_ACAT_CategoriaId,
                    'ART_CMM_SubcategoriaId' => $registra->ART_CMM_SubcategoriaId,
                    'ART_ARTM_MarcaId' => $registra->ART_ARTM_MarcaId,
                    'ART_CMUM_UMInventarioId' => $registra->ART_CMUM_UMInventarioId,
                    'ART_CMUM_UMConversionOCId' => $registra->ART_CMUM_UMConversionOCId,
                    'ART_CMUM_UMConversionOVId' => $registra->ART_CMUM_UMConversionOVId,
                    'ART_CMUM_UMConversionOTId' => $registra->ART_CMUM_UMConversionOTId,
                    'ART_CMM_PoliticaOrdenesId' => $registra->ART_CMM_PoliticaOrdenesId,
                    'ART_CMM_IVAPredeterminadoId' => $registra->ART_CMM_IVAPredeterminadoId,
                    'ART_CMM_CodigoCicloId' => $registra->ART_CMM_CodigoCicloId,
                    'ART_CMM_ManejoInventarioId' => $registra->ART_CMM_ManejoInventarioId,
                    'ART_LOC_LocPredEntradasId' => $registra->ART_LOC_LocPredEntradasId,
                    'ART_LOC_LocPredSalidasId' => $registra->ART_LOC_LocPredSalidasId,

                    'ART_EMP_ModificadoPor' => $idEmpleado,
                    'ART_EMP_CreadoPorId' => $idEmpleado,
                    'ART_CMM_ClaveProductoId' => empty($registra->ART_CMM_ClaveProductoId) ? null : $registra->ART_CMM_ClaveProductoId
                )
            );

            ////////////////consultar ID del articulo registrado/////////////////////////
            $ArticuloId = Articulo::orderby('ART_FechaCreacion', 'DESC')->first()->ART_ArticuloId;
            //REGISTRAR FACTORES DE CONVERSION
            for($val=1; $val<=$_POST['CCON_contador']; $val++){
                \DB::table('ArticulosFactoresConversion')->insert(
                    array(
                        'AFC_ART_ArticuloId' => $ArticuloId,
                        'AFC_CMUM_UnidadMedidaId' => $_POST['AFC_CMUM_UnidadMedidaId'.$val],
                        'AFC_FactorConversion' => $_POST['AFC_FactorConversion'.$val],
                        'AFC_FactorDefault' => $_POST['AFC_FactorDefault'.$val]
                    )
                );
            }

            //REGISTRAR ESPECIFICACIONES
            for($val=1; $val<=$_POST['DCON_contador']; $val++){
                \DB::table('ArticulosEspecificaciones')->insert(
                    array(
                        'AET_ART_ArticuloId' => $ArticuloId,
                        'AET_CMM_ArticuloEspecificaciones' => $_POST['AET_CMM_ArticuloEspecificaciones'.$val],
                        'AET_Valor' => $_POST['AET_Valor'.$val]
                    )
                );
            }

            //GUARDAR NUEVO ARTICULOS FORMAS DE EMPAQUE
            $FormaEmpaque = $request->ART_CMM_FormaEmpaque;
            $cuentaFormaEmpaque = count($FormaEmpaque);
            for ($x = 0; $x < $cuentaFormaEmpaque; $x++) {

                if($FormaEmpaque[$x] != ''){

                    $articulosFormasEmpaque = new ArticulosFormasEmpaque();
                    $articulosFormasEmpaque->AFE_ART_ArticuloId = $ArticuloId;
                    $articulosFormasEmpaque->AFE_CMM_FormaEmpaqueId = $FormaEmpaque[$x];
                    $articulosFormasEmpaque->AFE_EMP_CreadoPorId = $idEmpleado;
                    $articulosFormasEmpaque->AFE_EMP_ModificadoPorId = $idEmpleado;
                    $articulosFormasEmpaque->save();

                }

            }

            //$almacenes = $_POST['almacenes'];
            //REGISTRAR TIPOS DE ALMACENES Y LOCALIDADES
            $Localidades = $request->ART_CMM_Localidades;

            $cuentaLocalidades = count($Localidades);
            for ($x = 0; $x < $cuentaLocalidades; $x++) {

                if($Localidades[$x] != ''){

                    //CONSULTA ALMACEN DE LA LOCALIDAD A REGISTRAR
                    $alm = \DB::table('Almacenes')
                        ->join('Localidades','ALM_AlmacenId','=','LOC_ALM_AlmacenId')
                        ->where('LOC_LocalidadId', '=', $Localidades[$x])
                        ->select('ALM_AlmacenId')
                        ->get();

                    $articulosLocalidades = new ArticulosLocalidades();
                    $articulosLocalidades->ARL_LOC_LocalidadId = $Localidades[$x];
                    $articulosLocalidades->ARL_ALM_AlmacenId = $alm[0]->ALM_AlmacenId;
                    $articulosLocalidades->ARL_ART_ArticuloId = $ArticuloId;
                    $articulosLocalidades->ARL_EMP_ModificadoPorId = $idEmpleado;
                    $articulosLocalidades->save();

                }

            }

            //return json_encode(array());

            $mensaje = 'El  Artículo se registró con éxito.';

            \DB::commit();

            return ['Status' => 'Valido', 'Mensaje' => $mensaje, 'Id' => $id];

        }
        catch (\Exception $e) {

            \DB::rollback();

            return ['Status' => 'Error', 'Mensaje' => 'No se registró el Artículo. Ocurrió un error al realizar el proceso. Error: '.$e->getMessage()];

        }

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

        //CARGAR COMBOS DE OTRAS TABLAS
        //$familias=array(''=>'') + Familia::select('AFAM_FamiliaId',  \DB::raw("AFAM_Codigo + ' - ' + AFAM_Nombre AS FULL_NAME"))
        $familias=array(''=>'') + Familia::select('AFAM_FamiliaId',  \DB::raw("AFAM_Nombre AS FULL_NAME"))
                ->where('AFAM_Fabricado','=',1)
                ->orderBy('AFAM_Nombre','ASC')
                ->lists('FULL_NAME', 'AFAM_FamiliaId')->all();
        //$categorias=array(''=>'') + Categoria::select('ACAT_CategoriaId',  \DB::raw("ACAT_Codigo + ' - ' + ACAT_Nombre AS FULL_NAME"))
        $categorias=array(''=>'') + Categoria::select('ACAT_CategoriaId',  \DB::raw("ACAT_Nombre AS FULL_NAME"))
                ->where('ACAT_Fabricado','=',1)
                ->orderBy('ACAT_Nombre','ASC')
                ->lists('FULL_NAME', 'ACAT_CategoriaId')->all();
        //$marcas=array(''=>'') + ArticuloMarca::select('ARTM_MarcaId',  \DB::raw("ARTM_Codigo + ' - ' + ARTM_Nombre AS FULL_NAME"))
        $marcas=array(''=>'') + ArticuloMarca::select('ARTM_MarcaId',  \DB::raw("ARTM_Nombre AS FULL_NAME"))
                ->orderBy('ARTM_Nombre','ASC')
                ->lists('FULL_NAME', 'ARTM_MarcaId')->all();
        $subcategorias=array(''=>'') + CMMult::where('CMM_Control', '=', 'CMM_INV_SubcategoriaArticulos')
                ->where('CMM_DefinidoPorUsuario3','=',1)
                ->orderBy('CMM_Valor','ASC')
                ->get()->lists('CMM_Valor', 'CMM_ControlId')->all();

        $inventarios=array(''=>'') + UMInventario::select('CMUM_UnidadMedidaId',  \DB::raw("CMUM_Nombre"))
                ->orderBy('CMUM_Nombre','ASC')
                ->lists('CMUM_Nombre','CMUM_UnidadMedidaId')->all();
        $tipoarticulos=array(''=>'') + TipoArticulo::where('ATP_Fabricado', '=', 1)
                ->orderBy('ATP_Descripcion','ASC')
                ->get()->lists('ATP_Descripcion','ATP_TipoId')->all();
        $ivaspredetermiados=array(''=>'') + IVAPredeterminado::select('CMIVA_IVAId','CMIVA_Porcentaje')
                ->where('CMIVA_CMM_TipoIVA','=','A5585C2F-CD77-4641-8853-2926FD929C91')
                ->lists('CMIVA_Porcentaje','CMIVA_IVAId')->all();
        $localidades=Localidad::where('LOC_LocalidadGeneral','=',0)->where('LOC_Eliminado','=',0)->lists('LOC_Nombre','LOC_LocalidadId')->all();
        $codigosciclo = array('' => '') + CMMult::where('CMM_Control', '=', 'CMM_INV_CodigoCiclo')->get()->lists('CMM_Valor', 'CMM_ControlId')->all();
        $manejosinventario = array('' => '') + CMMult::where('CMM_Control', '=', 'CMM_INV_ManejoInventario')->get()->lists('CMM_Valor', 'CMM_ControlId')->all();
        $politicasordenes =CMMult::where('CMM_Control', '=', 'CMM_INV_PoliticaOrden')->get()->lists('CMM_Valor', 'CMM_ControlId')->all();

        $resultados=Articulo::find($id);

        $especificaciones=array(
            'CMM_Valor',
            'AET_Valor',
            'CMM_ControlId'

        );

        $especificaciones2 = \DB::select(
            \DB::raw(
                "SELECT CMM_ControlId, CMM_Valor, AE.AET_Valor
                FROM ControlesMaestrosMultiples
                LEFT JOIN (
                    SELECT AET_CMM_ArticuloEspecificaciones, AET_Valor
                    FROM ArticulosEspecificaciones
                    WHERE 1=1 AND AET_ART_ArticuloId = '".$id."'
                    ) AS AE
                ON CMM_ControlId = AET_CMM_ArticuloEspecificaciones
                WHERE CMM_Control = 'CMM_INV_ArticulosEspecificaciones'
                AND CMM_Eliminado = 0"
            )
        );

        /*$especificaciones2=CMMult::select('CMM_Valor','AET_Valor','CMM_ControlId')
            ->leftJoin('ArticulosEspecificaciones', 'CMM_ControlId','=','AET_CMM_ArticuloEspecificaciones')
            ->where('AET_ART_ArticuloId', '=', $resultados->ART_ArticuloId)
            ->where('CMM_Eliminado', '=', 0)
            ->where('CMM_Sistema', '=', 0)
            ->get();*/

        $factoresconversion=UMInventario::select('CMUM_Nombre','CMUM_UnidadMedidaId')
            ->join('ArticulosFactoresConversion', 'ControlesMaestrosUM.CMUM_UnidadMedidaId', '=', 'ArticulosFactoresConversion.AFC_CMUM_UnidadMedidaId')
            ->join('Articulos', 'ArticulosFactoresConversion.AFC_ART_ArticuloId', '=', 'Articulos.ART_ArticuloId')
            ->where('AFC_ART_ArticuloId', '=', $resultados->ART_ArticuloId)
            ->orderby('ArticulosFactoresConversion.AFC_FactorDefault','DESC')
            ->lists('CMUM_Nombre','CMUM_UnidadMedidaId')->all();

        $idalment=$resultados->ART_LOC_LocPredEntradasId;
        $idalmsal=$resultados->ART_LOC_LocPredSalidasId;

        if($resultados->ART_SeguimientoLocMult==1){
            if($idalment==null){
                $almacenes=Almacen::select('ALM_AlmacenId', 'ALM_Nombre')->where('ALM_Eliminado','=',0)
                    ->orderby('ALM_Nombre','ASC')
                    ->lists('ALM_Nombre','ALM_AlmacenId')->all();
            }else{
                $results=\DB::table('Almacenes')
                    ->join('Localidades', 'Localidades.LOC_ALM_AlmacenId', '=', 'Almacenes.ALM_AlmacenId')
                    ->where('Localidades.LOC_LocalidadId', '=', $idalment)
                    ->where('ALM_Eliminado','=',0)
                    //->where('LOC_Eliminado','=',0)
                    //->where('LOC_LocalidadGeneral','=',0)
                    ->select('Almacenes.ALM_AlmacenId','Almacenes.ALM_Nombre')
                    ->get();

                //$almacenes=array($results[0]->LOC_LocalidadId=>$results[0]->ALM_Nombre) + Almacen::select('ALM_AlmacenId', 'ALM_Nombre')->where('ALM_Eliminado','=',0)->lists('ALM_Nombre','ALM_AlmacenId')->all();
                $almacenes= array($results[0]->ALM_AlmacenId=>$results[0]->ALM_Nombre) + Almacen::select('ALM_AlmacenId', 'ALM_Nombre')
                    ->where('ALM_Eliminado','=',0)
                    ->orderby('ALM_Nombre','ASC')
                    ->lists('ALM_Nombre','ALM_AlmacenId')->all();
            }
            if($idalmsal==null){
                $almacenes2=Almacen::select('ALM_AlmacenId', 'ALM_Nombre')
                    ->where('ALM_Eliminado','=',0)
                    ->orderby('ALM_Nombre','ASC')
                    ->lists('ALM_Nombre','ALM_AlmacenId')->all();
            }else{
                $results=\DB::table('Almacenes')
                    ->join('Localidades', 'Localidades.LOC_ALM_AlmacenId', '=', 'Almacenes.ALM_AlmacenId')
                    ->where('Localidades.LOC_LocalidadId', '=', $idalmsal)
                    ->where('ALM_Eliminado','=',0)
                    //->where('LOC_Eliminado','=',0)
                    //->where('LOC_LocalidadGeneral','=',0)
                    ->select('Almacenes.ALM_AlmacenId','Almacenes.ALM_Nombre')
                    ->get();
                $almacenes2= array($results[0]->ALM_AlmacenId=>$results[0]->ALM_Nombre) + Almacen::select('ALM_AlmacenId', 'ALM_Nombre')
                    ->where('ALM_Eliminado','=',0)
                    ->orderby('ALM_Nombre','ASC')
                    ->lists('ALM_Nombre','ALM_AlmacenId')->all();
            }
        }else{
            if($idalment==null){
                $almacenes=Almacen::select('ALM_AlmacenId', 'ALM_Nombre')
                    ->where('ALM_Eliminado','=',0)
                    ->orderby('ALM_Nombre','ASC')
                    ->lists('ALM_Nombre','ALM_AlmacenId');
            }else{
                $results=\DB::table('Almacenes')
                    ->join('Localidades', 'Localidades.LOC_ALM_AlmacenId', '=', 'Almacenes.ALM_AlmacenId')
                    ->where('Localidades.LOC_LocalidadId', '=', $idalment)
                    ->where('ALM_Eliminado','=',0)
                    //->where('LOC_Eliminado','=',0)
                    //->where('LOC_LocalidadGeneral','=',0)
                    ->select('Localidades.LOC_LocalidadId','Almacenes.ALM_Nombre')
                    ->get();

                $almacenes= Almacen::select('ALM_AlmacenId', 'ALM_Nombre')
                    ->where('ALM_Eliminado','=',0)
                    ->orderby('ALM_Nombre','ASC')
                    ->lists('ALM_Nombre','ALM_AlmacenId')->all();
            }

            if($idalmsal==null){
                $almacenes2=Almacen::select('ALM_AlmacenId', 'ALM_Nombre')
                    ->where('ALM_Eliminado','=',0)
                    ->orderby('ALM_Nombre','ASC')
                    ->lists('ALM_Nombre','ALM_AlmacenId');
            }else{
                $results=\DB::table('Almacenes')
                    ->join('Localidades', 'Localidades.LOC_ALM_AlmacenId', '=', 'Almacenes.ALM_AlmacenId')
                    ->where('Localidades.LOC_LocalidadId', '=', $idalmsal)
                    ->where('ALM_Eliminado','=',0)
                    //->where('LOC_Eliminado','=',0)
                    //->where('LOC_LocalidadGeneral','=',0)
                    ->select('Localidades.LOC_LocalidadId','Almacenes.ALM_Nombre')
                    ->get();

                $almacenes2= Almacen::select('ALM_AlmacenId', 'ALM_Nombre')
                    ->where('ALM_Eliminado','=',0)
                    ->orderby('ALM_Nombre','ASC')
                    ->lists('ALM_Nombre','ALM_AlmacenId')->all();
            }
        }
        $almacenes3=Almacen::select('ALM_AlmacenId', 'ALM_Nombre')
            ->where('ALM_Eliminado','=',0)
            ->orderby('ALM_Nombre','ASC')
            ->lists('ALM_Nombre','ALM_AlmacenId')->all();

        $imagen=$resultados->ART_Imagen;
        if(!is_null($imagen)) {
            //dd($imagen);
            //echo('Entra');
            $public_path = public_path();
            $url = $public_path . '/img/articulos/' . $imagen;
            //dd($url);
            //verificamos si el archivo existe y lo retornamos
            if (\Storage::exists($imagen)) {
                //dd($imagen);
              //  echo('no entra');
                $imagen = '/img/articulos/' . $imagen;
            }
        }

        $productoId = $resultados->ART_CMM_ClaveProductoId;
        $codigoproducto = '';
        $nombreproducto = '';
        if(!empty($productoId)){
            $dao = new DAOGeneralController();
            $producto = $dao->getEjecutaConsulta("SELECT *
                                               FROM ControlesMaestrosMultiples
                                               WHERE CMM_ControlId = '$productoId' ");

            $nombreproducto = $producto[0]->CMM_Valor;
            $codigoproducto = $producto[0]->CMM_DefinidoPorUsuario1;
        }

        $art_DegustableDetalle = $resultados->ART_DegustableDetalle;
        $art_DegustableAutoservicio = $resultados->ART_DegustableAutoservicio;
        $art_Activo = $resultados->ART_Activo;
        $art_Obsoleto = $resultados->ART_Obsoleto;
        $art_Consignable = $resultados->ART_Consignable;
        $art_PermitirCambioAlmacen = $resultados->ART_PermitirCambioAlmacen;
        $art_SeguimientoLocMult = $resultados->ART_SeguimientoLocMult;
        $art_CrearLocAlmacenaje = $resultados->ART_CrearLocAlmacenaje;
        $art_OcultarLocsCantCero = $resultados->ART_OcultarLocsCantCero;
        $art_SeguimientoLotMult = $resultados->ART_SeguimientoLotMult;
        $art_Anaquel = $resultados->ART_Anaquel;
        $art_IncluirEmpaque = $resultados->ART_IncluirEmpaque;
        $art_ValorContable = $resultados->ART_ValorContable;
        $art_OmitirOT = $resultados->ART_OmitirOT;
        $art_DeduccionRetroactivaMaterial = $resultados->ART_DeduccionRetroactivaMaterial;
        $art_InspeccionOC = $resultados->ART_InspeccionOC;
        $art_OmitirPlaneacionOC = $resultados->ART_OmitirPlaneacionOC;
        $art_DeduccionRetroactivaManoObra = $resultados->ART_DeduccionRetroactivaManoObra;
        $art_InspeccionOT = $resultados->ART_InspeccionOT;
        $art_OTDependiente = $resultados->ART_OTDependiente;
        $art_EstandarPredeterminado = $resultados->ART_EstandarPredeterminado;
        $art_NoParticionar = $resultados->ART_NoParticionar;

        $formaEmpaque = array('' => 'Seleccione formas de empaque') + CMMult::where('CMM_Control', '=', 'CMM_FormaEmpaque')->get()->lists('CMM_Valor', 'CMM_ControlId')->all();

        $ConsultaFormaEmpaqueRegistro = \DB::table('ArticulosFormasEmpaque')
            ->where('AFE_ART_ArticuloId', '=', $id)
            ->where('AFE_Eliminado', '=', 0)
            ->select('AFE_CMM_FormaEmpaqueId')
            ->get();

        $formaEmpaqueRegistro = array($ConsultaFormaEmpaqueRegistro);

        return view('Inventario.Articulos.editar', compact('productoId', 'codigoproducto', 'nombreproducto', 'resultados', 'familias',
            'categorias','inventarios', 'tipoarticulos', 'ivaspredetermiados', 'localidades', 'codigosciclo', 'manejosinventario',
            'politicasordenes', 'imagen', 'almacenes', 'almacenes2', 'almacenes3', 'factoresconversion', 'id', 'especificaciones', 'especificaciones2', 'marcas', 'subcategorias','art_DegustableDetalle','art_DegustableAutoservicio','art_SeguimientoLocMult','art_SeguimientoLotMult', 'art_CrearLocAlmacenaje','art_Activo','art_Obsoleto','art_Consignable','art_PermitirCambioAlmacen','art_OcultarLocsCantCero','art_Anaquel','art_IncluirEmpaque','art_ValorContable','art_OmitirOT','art_DeduccionRetroactivaMaterial','art_InspeccionOC', 'formaEmpaque','formaEmpaqueRegistro', 'art_OmitirPlaneacionOC','art_DeduccionRetroactivaManoObra','art_InspeccionOT','art_OTDependiente', 'art_EstandarPredeterminado', 'art_NoParticionar'));
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {

        \DB::beginTransaction();

        try{

            $idEmpleado = DataBaseSession::getEmpleadoId();
            //$idEmpleado = "3A2D4A67-BB29-493B-BFB1-3A1A03310372";

            //date_default_timezone_set('America/Mexico_City');
            $hoy=date('d/m/Y H:i:s');
            //cachar variables de almacenes ent y sal. antes de asignar al modelo
            $ART_ALM_AlmPredEntradasId=$request->ART_ALM_AlmPredEntradasId;
            $ART_ALM_AlmPredSalidasId=$request->ART_ALM_AlmPredSalidasId;
            $ART_Anaquel = $request->ART_Anaquel;
            $registra=Articulo::find($id);
            $imagenanterior=$registra->ART_Imagen;
            $registra->ART_Anaquel = $ART_Anaquel;
            $registra->fill($request->all());

            ////////////////////////////buscar almacenes predeterminados de entrada//////////////////////////////////////
            if($ART_ALM_AlmPredEntradasId !="" && ($registra->ART_LOC_LocPredEntradasId=='null' || $registra->ART_LOC_LocPredEntradasId=='' )){

                $sub = \DB::table('Localidades')
                    ->where('LOC_LocalidadId', '=', $ART_ALM_AlmPredEntradasId)
                    ->where('LOC_Eliminado', '=', 0)
                    ->where('LOC_LocalidadGeneral', '=', 1)
                    ->select('LOC_LocalidadId')
                    ->get();

                if(count($sub)>0){
                    $idloc=$sub[0]->LOC_LocalidadId;
                    $registra->ART_LOC_LocPredEntradasId=$idloc;
                }
                else{
                    $registra->ART_LOC_LocPredEntradasId="";
                }

            }

            ////////////////////////////buscar almacenes predeterminados de salida//////////////////////////////////////
            if($ART_ALM_AlmPredSalidasId !="" && ($registra->ART_LOC_LocPredSalidasId=='null' || $registra->ART_LOC_LocPredSalidasId=='')){

                $sub = \DB::table('Localidades')
                    ->where('LOC_LocalidadId', '=', $ART_ALM_AlmPredSalidasId)
                    ->where('LOC_Eliminado', '=', 0)
                    ->where('LOC_LocalidadGeneral', '=', 1)
                    ->select('LOC_LocalidadId')
                    ->get();

                if(count($sub)>0){
                    $idloc=$sub[0]->LOC_LocalidadId;
                    $registra->ART_LOC_LocPredSalidasId=$idloc;
                }
                else{
                    $registra->ART_LOC_LocPredSalidasId="";
                }

            }

            //VALIDAR VALORES DE CHECKBOX 1-0
            if($request->ART_Activo>1)$registra->ART_Activo=1; else $registra->ART_Activo=0;
            if($request->ART_Anaquel>1)$registra->ART_Anaquel=1; else $registra->ART_Anaquel=0;
            if($request->ART_Obsoleto>1)$registra->ART_Obsoleto=1; else $registra->ART_Obsoleto=0;
            if($request->ART_DegustableDetalle>1)$registra->ART_DegustableDetalle=1; else $registra->ART_DegustableDetalle=0;
            if($request->ART_DegustableAutoservicio>1)$registra->ART_DegustableAutoservicio=1; else $registra->ART_DegustableAutoservicio=0;
            if($request->ART_Consignable>1)$registra->ART_Consignable=1; else $registra->ART_Consignable=0;
            if($request->ART_PermitirCambioAlmacen>1)$registra->ART_PermitirCambioAlmacen=1; else $registra->ART_PermitirCambioAlmacen=0;
            if($request->ART_SeguimientoLocMult>1)$registra->ART_SeguimientoLocMult=1; else $registra->ART_SeguimientoLocMult=0;
            if($request->ART_CrearLocAlmacenaje>1)$registra->ART_CrearLocAlmacenaje=1; else $registra->ART_CrearLocAlmacenaje=0;
            if($request->ART_OcultarLocsCantCero>1)$registra->ART_OcultarLocsCantCero=1; else $registra->ART_OcultarLocsCantCero=0;
            if($request->ART_SeguimientoLotMult>1)$registra->ART_SeguimientoLotMult=1; else $registra->ART_SeguimientoLotMult=0;
            if($request->ART_OmitirOT>1)$registra->ART_OmitirOT=1; else $registra->ART_OmitirOT=0;
            if($request->ART_IncluirEmpaque>1)$registra->ART_IncluirEmpaque=1; else $registra->ART_IncluirEmpaque=0;
            if($request->ART_DeduccionRetroactivaMaterial>1)$registra->ART_DeduccionRetroactivaMaterial=1; else $registra->ART_DeduccionRetroactivaMaterial=0;
            if($request->ART_DeduccionRetroactivaManoObra>1)$registra->ART_DeduccionRetroactivaManoObra=1; else $registra->ART_DeduccionRetroactivaManoObra=0;
            if($request->ART_InspeccionOC>1)$registra->ART_InspeccionOC=1; else $registra->ART_InspeccionOC=0;
            if($request->ART_InspeccionOT>1)$registra->ART_InspeccionOT=1; else $registra->ART_InspeccionOT=0;
            if($request->ART_OTDependiente>1)$registra->ART_OTDependiente=1; else $registra->ART_OTDependiente=0;
            if($request->ART_EstandarPredeterminado>1)$registra->ART_EstandarPredeterminado=1; else $registra->ART_EstandarPredeterminado=0;
            if($request->ART_NoParticionar>1)$registra->ART_NoParticionar=1; else $registra->ART_NoParticionar=0;
            if($request->ART_OmitirPlaneacionOC>1)$registra->ART_OmitirPlaneacionOC=1; else $registra->ART_OmitirPlaneacionOC=0;
            if($request->ART_ManejarDecimalesVentaRuta>1)$registra->ART_ManejarDecimalesVentaRuta=1; else $registra->ART_ManejarDecimalesVentaRuta=0;

            //VALIDAR VALORES NULL DE SELECT COMO UNIQUEREQUIRED
            if($request->ART_CMUM_UMInventarioId=="")$registra->ART_CMUM_UMInventarioId=null;
            if($request->ART_LOC_LocPredEntradasId=="")$registra->ART_LOC_LocPredEntradasId=null;
            if($request->ART_LOC_LocPredSalidasId=="")$registra->ART_LOC_LocPredSalidasId=null;
            if($request->ART_CMUM_UMConversionOCId=="")$registra->ART_CMUM_UMConversionOCId=null;
            if($request->ART_CMUM_UMConversionOVId=="")$registra->ART_CMUM_UMConversionOVId=null;
            if($request->ART_CMUM_UMConversionOTId=="")$registra->ART_CMUM_UMConversionOTId=null;
            if($request->ART_AFAM_FamiliaId=="")$registra->ART_AFAM_FamiliaId=null;
            if($request->ART_ACAT_CategoriaId=="")$registra->ART_ACAT_CategoriaId=null;
            if($request->ART_CMM_SubcategoriaId=="")$registra->ART_CMM_SubcategoriaId=null;
            if($request->ART_ARTM_MarcaId=="")$registra->ART_ARTM_MarcaId=null;
            if($request->ART_CMM_ManejoInventarioId=="")$registra->ART_CMM_ManejoInventarioId=null;
            if($request->ART_ATP_TipoId=="")$registra->ART_ATP_TipoId=null;
            if($request->ART_CMM_PoliticaOrdenesId=="")$registra->ART_CMM_PoliticaOrdenesId=null;
            if($request->ART_CMM_CodigoCicloId=="")$registra->ART_CMM_CodigoCicloId=null;
            if($request->ART_Precio=="")$registra->ART_Precio=null;
            if($request->ART_CantidadAMano=="")$registra->ART_CantidadAMano=null;
            if($request->ART_PorcentajeComision=="")$registra->ART_PorcentajeComision=null;
            if($request->ART_CMM_IVAPredeterminadoId=="")$registra->ART_CMM_IVAPredeterminadoId=null;

            //VALIDARVALORES NULL DE INPUT Y CHECKBOX, COMO BIT, INT Y DOUBLE
            if($request->ART_SeguimientoLocMult==null)$registra->ART_SeguimientoLocMult=0;
            if($request->ART_Obsoleto==null)$registra->ART_Obsoleto=0;
            if($request->ART_Anaquel==null)$registra->ART_Anaquel=0;
            if($request->ART_DegustableDetalle==null)$registra->ART_DegustableDetalle=0;
            if($request->ART_DegustableAutoservicio==null)$registra->ART_DegustableAutoservicio=0;
            if($request->ART_Consignable==null)$registra->ART_Consignable=0;
            if($request->ART_PermitirCambioAlmacen==null)$registra->ART_PermitirCambioAlmacen=0;
            if($request->ART_SeguimientoLocMult==null)$registra->ART_SeguimientoLocMult=0;
            if($request->ART_CrearLocAlmacenaje==null)$registra->ART_CrearLocAlmacenaje=0;
            if($request->ART_OcultarLocsCantCero==null)$registra->ART_OcultarLocsCantCero=0;
            if($request->ART_SeguimientoLotMult==null)$registra->ART_SeguimientoLotMult=0;
            if($request->ART_RendimientoEstandar==null)$registra->ART_RendimientoEstandar=0;
            if($request->ART_CantMinimaOrden==null)$registra->ART_CantMinimaOrden=0;
            if($request->ART_CantMaximaOrden==null)$registra->ART_CantMaximaOrden=0;
            if($request->ART_CantMultipleOrden==null)$registra->ART_CantMultipleOrden=0;
            if($request->ART_NoDiasAbastecimiento==null)$registra->ART_NoDiasAbastecimiento=0;
            if($request->ART_CantOrdenEconomica==null)$registra->ART_CantOrdenEconomica=0;
            if($request->ART_CantPuntoOrden==null)$registra->ART_CantPuntoOrden=0;
            if($request->ART_OmitirOT==null)$registra->ART_OmitirOT=0;
            if($request->ART_IncluirEmpaque==null)$registra->ART_IncluirEmpaque=0;
            if($request->ART_DeduccionRetroactivaMaterial==null)$registra->ART_DeduccionRetroactivaMaterial=0;
            if($request->ART_DeduccionRetroactivaManoObra==null)$registra->ART_DeduccionRetroactivaManoObra=0;
            if($request->ART_InspeccionOC==null)$registra->ART_InspeccionOC=0;
            if($request->ART_InspeccionOT==null)$registra->ART_InspeccionOT=0;
            if($request->ART_OTDependiente==null)$registra->ART_OTDependiente=0;
            if($request->ART_EstandarPredeterminado==null)$registra->ART_EstandarPredeterminado=0;
            if($request->ART_NoParticionar==null)$registra->ART_NoParticionar=0;
            if($request->ART_OmitirPlaneacionOC==null)$registra->ART_OmitirPlaneacionOC=0;
            if($request->ART_ManejarDecimalesVentaRuta==null)$registra->ART_ManejarDecimalesVentaRuta=0;

            if($request->ART_CantidadUMEmpaqueEnCaja==null)$registra->ART_CantidadUMEmpaqueEnCaja=0;
            if($request->ART_CantidadCajasEnPallet==null)$registra->ART_CantidadCajasEnPallet=0;
            if($request->ART_DiasVidaAnaquel==null)$registra->ART_DiasVidaAnaquel=0;

            if($request->ART_Departamento=="")$registra->ART_Departamento=null;
            if($request->ART_Pasta=="")$registra->ART_Pasta=null;
            if($request->ART_Presentacion=="")$registra->ART_Presentacion=null;

            $registra->ART_CMUM_UMEmpaqueId = $registra->ART_CMUM_UMInventarioId;

            //dd($registra);
            $registra->ART_EMP_ModificadoPor = $idEmpleado;
            $registra->ART_FechaUltimaModificacion = $hoy;
            $registra->ART_CMM_ClaveProductoId = empty($registra->ART_CMM_ClaveProductoId) ? null : $registra->ART_CMM_ClaveProductoId;
            $registra->ART_RendimientoEstandar = floatval($request->ART_RendimientoEstandar);
            $registra->ART_NoDiasAbastecimiento = intval($request->ART_NoDiasAbastecimiento);

            $registra->save();
            //////validar imagen/////
//            if($registra->save()){
//                if($imagenanterior!=$registra->ART_Imagen){
//                    $imageName = $request->file('ART_Imagen')->getClientOriginalName();
//                    $i = 0;
//                    $info = explode(".",$imageName);
//                    $miImg = $imageName;
//                    while(file_exists(base_path() . '/public/img/articulos/'. $miImg)){
//                        $i++;
//                        $miImg = $info[0]."(".$i.")".".".$info[1];
//                    }
//                    $request->file('ART_Imagen')->move(base_path() . '/public/img/articulos/', $miImg);
//                    unlink(base_path() . '/public/img/articulos/'. $imagenanterior);
//                    $registra->ART_Imagen=$miImg;
//                    $registra->save();
//                }
//            }

            $ArticuloId=$registra->ART_ArticuloId;

            \DB::table('ArticulosFactoresConversion')->where('AFC_ART_ArticuloId', '=', $ArticuloId)->delete();
            //REGISTRAR FACTORES DE CONVERSION
            for($val=1; $val<=$_POST['CCON_contador']; $val++){
                \DB::table('ArticulosFactoresConversion')->insert(
                    array(
                        'AFC_ART_ArticuloId' => $ArticuloId,
                        'AFC_CMUM_UnidadMedidaId' => $_POST['AFC_CMUM_UnidadMedidaId'.$val],
                        'AFC_FactorConversion' => $_POST['AFC_FactorConversion'.$val],
                        'AFC_FactorDefault' => $_POST['AFC_FactorDefault'.$val]
                    )
                );
            }

            \DB::table('ArticulosEspecificaciones')->where('AET_ART_ArticuloId', '=', $ArticuloId)->delete();
            //REGISTRAR ESPECIFICACIONES
            for($val=1; $val<=$_POST['DCON_contador']; $val++){

                if($_POST['AET_Valor'.$val] == "")
                {

                    $_POST['AET_Valor'.$val] = null;

                }

                \DB::table('ArticulosEspecificaciones')->insert(
                    array(
                        'AET_ART_ArticuloId' => $ArticuloId,
                        'AET_CMM_ArticuloEspecificaciones' => $_POST['AET_CMM_ArticuloEspecificaciones'.$val],
                        'AET_Valor' => $_POST['AET_Valor'.$val]
                    )
                );
            }

            //ELIMINAR REGISTROS DE ARTICULO EN ARTICULOS FORMAS DE EMPAQUE
            \DB::table('ArticulosFormasEmpaque')
                ->where('AFE_ART_ArticuloId', '=', $ArticuloId)
                ->update(
                    array(
                        'AFE_Eliminado' => 1,
                        'AFE_EMP_ModificadoPorId' => $idEmpleado
                    )
                );

            //GUARDAR NUEVO ARTICULOS FORMAS DE EMPAQUE
            $FormaEmpaque = $request->ART_CMM_FormaEmpaque;
            $cuentaFormaEmpaque = count($FormaEmpaque);
            for ($x = 0; $x < $cuentaFormaEmpaque; $x++) {

                if($FormaEmpaque[$x] != ''){

                    $articulosFormasEmpaque = new ArticulosFormasEmpaque();
                    $articulosFormasEmpaque->AFE_ART_ArticuloId = $ArticuloId;
                    $articulosFormasEmpaque->AFE_CMM_FormaEmpaqueId = $FormaEmpaque[$x];
                    $articulosFormasEmpaque->AFE_EMP_CreadoPorId = $idEmpleado;
                    $articulosFormasEmpaque->AFE_EMP_ModificadoPorId = $idEmpleado;
                    $articulosFormasEmpaque->save();

                }

            }

            //$almacenes = $_POST['almacenes'];
            //ELIMINA ARTICULOSLOCALIDADES QUE ESTEN REGISTRADOS
            \DB::table('ArticulosLocalidades')
                ->where('ARL_ART_ArticuloId', '=', $ArticuloId)
                ->update(
                    array(
                        'ARL_Eliminado' => 1,
                        'ARL_FechaUltimaModificacion' => $hoy,
                        'ARL_EMP_ModificadoPorId' => $idEmpleado
                    )
                );

            //REGISTRAR TIPOS DE ALMACENES Y LOCALIDADES
            $Localidades = $request->ART_CMM_Localidades;
            $cuentaLocalidades = count($Localidades);
            for ($x = 0; $x < $cuentaLocalidades; $x++) {

                if($Localidades[$x] != ''){

                    //CONSULTA ALMACEN DE LA LOCALIDAD A REGISTRAR
                    $alm = \DB::table('Almacenes')
                        ->join('Localidades','ALM_AlmacenId','=','LOC_ALM_AlmacenId')
                        ->where('LOC_LocalidadId', '=', $Localidades[$x])
                        ->select('ALM_AlmacenId')
                        ->get();

                    //if(count($alm) == 0)dd($alm);
                    $articulosLocalidades = new ArticulosLocalidades();
                    $articulosLocalidades->ARL_LOC_LocalidadId = $Localidades[$x];
                    $articulosLocalidades->ARL_ALM_AlmacenId = $alm[0]->ALM_AlmacenId;
                    $articulosLocalidades->ARL_ART_ArticuloId = $ArticuloId;
                    $articulosLocalidades->ARL_FechaUltimaModificacion = $hoy;
                    $articulosLocalidades->ARL_EMP_ModificadoPorId = $idEmpleado;
                    $articulosLocalidades->save();

                }

            }

            //return json_encode(array());

            $mensaje = 'El Artículo se actualizó con éxito.';

            \DB::commit();

            return ['Status' => 'Valido', 'Mensaje' => $mensaje];

        }
        catch (\Exception $e) {

            \DB::rollback();

            return ['Status' => 'Error', 'Mensaje' => 'No se actualizó el Artículo. Ocurrió un error al realizar el proceso. Error: '.$e->getMessage()];

        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $result=Articulo::find($id);
        $result->ART_Eliminado=1;
        $result->ART_Activo=0;
        $result->save();
        return json_encode(array());
    }

    public function guardaImagen($id, Request $request){

        $file = $request->file('archivo');


        if(!is_null($file)) {
            //obtenemos el nombre del archivo
            $nombre = $file->getClientOriginalName();

            //dd($id);

            //indicamos que queremos guardar un nuevo archivo en el disco local
            \Storage::disk('local')->put($nombre, \File::get($file));

            $articulo = Articulos::find($id);
            $articulo->ART_Imagen = $nombre;
            $articulo->save();

        }
        else{
            //$articulo = Articulos::find($id);
            //$articulo->ART_Imagen = null;
            //$articulo->save();
        }

        return "archivo guardado";

    }

    public function getImagen($id){

        $articulo = Articulo::find($id);
        $imagen=$articulo->ART_Imagen;

        if(!is_null($imagen)) {
            $public_path = public_path();
            $url = $public_path . '/img/articulos/' . $imagen;
            //verificamos si el archivo existe y lo retornamos
            if (\Storage::exists($imagen)) {
                return response()->download($url);
            }
        }



    }

    public static function allArticulos(){
        $results=\DB::table('Articulos')
            ->where('ART_Eliminado','=',0)
            ->select('ART_CodigoArticulo','ART_Nombre','ART_ArticuloId')
            ->orderby('ART_CodigoArticulo','ASC')
            ->get();

        return Response::json($results);
    }

    public static function BuscarCodigoArt2($id){
        $sub = \DB::table('Articulos')
            ->where('ART_CodigoArticulo', '=', $id)
            ->where('ART_Eliminado','=',0)
            ->where('ART_Activo','=',1)
            ->get();
        return $sub;
    }

    /////////////////////////////////////FUNCIONESPARA PROCESADOR DEINVENTARIO FISICO///////////////////////////////////

    public static function buscaPorId($idArticulo){
        $articulo=Articulo::find($idArticulo);
        return $articulo;
    }

    public static function buscaNombreUMInventarioPorArticuloId($articuloid){
        $nombreunidadmedida=ControlesMaestrosUM::select('CMUM_Nombre')
            ->join('Articulos','ART_CMUM_UMInventarioId','=','CMUM_UnidadMedidaId')
            ->where('ART_ArticuloId','=',$articuloid)
            ->get();
        return$nombreunidadmedida;
    }

    public static function getValorContablePorId(){

    }

    public static function actualizaCantidadAManoPorId($id,$cantidadAfectadaEnInventario){
        $articulo=Articulo::find($id);
        $articulo->ART_CantidadAMano=sprintf('%f',$articulo->ART_CantidadAMano+$cantidadAfectadaEnInventario);
        $articulo->save();
    }

    public static function actualizaCamposDeAjustePorId($objeto){

        try{

            //date_default_timezone_set('America/Mexico_City');
            $hoy=date('d/m/Y H:i:s');
            $Articulo=$objeto;
            \DB::table('Articulos')->where('ART_ArticuloId', '=', $Articulo->ART_ArticuloId)->update(
                array(
                    'ART_CantidadAMano' => $Articulo->ART_CantidadAMano,
                    'ART_CantidadUltimoAjuste' => $Articulo->ART_CantidadUltimoAjuste,
                    'ART_FechaUltimaModificacion' => $hoy,
                    'ART_FechaUltimoAjuste'=>$hoy
                )
            );

        }
        catch(\Exception $e){

            throw $e;

        }

    }

    /////////////////////////////////FIN FUNCIONESPARA PROCESADOR DEINVENTARIO FISICO///////////////////////////////////

    public static function cosultarDatosPrelote($ART_Departamento,$ART_Pasta,$ART_Presentacion){

        $consulta = \DB::select(

            \DB::raw(

                "select *
                from Articulos
                where ART_Departamento = '".$ART_Departamento."'
                and ART_Pasta = '".$ART_Pasta."'
                and ART_Presentacion = '".$ART_Presentacion."'"

            )

        );

        $cuentaConsulta = count($consulta);
        $mensaje = "";
        if($cuentaConsulta > 0)
        {

            $mensaje = "El Artículo ".$consulta[0]->ART_CodigoArticulo." - ".$consulta[0]->ART_Nombre." ya tiene los parametros ingresados. ¡Cambielos!";


        }
        else
        {

            $mensaje = "";

        }

        return ['Status' => 'Valido', 'Mensaje' => $mensaje];

    }

    public static function cosultarDatosPreloteEditar($ART_Departamento,$ART_Pasta,$ART_Presentacion,$ART_ArticuloId){

        $consulta = \DB::select(

            \DB::raw(

                "select *
                from Articulos
                where ART_Departamento = '".$ART_Departamento."'
                and ART_Pasta = '".$ART_Pasta."'
                and ART_Presentacion = '".$ART_Presentacion."'"

            )

        );

        $cuentaConsulta = count($consulta);
        $mensaje = "";
        if($cuentaConsulta > 0)
        {

            if($ART_ArticuloId != $consulta[0]->ART_ArticuloId)
            {

                $mensaje = "El Artículo ".$consulta[0]->ART_CodigoArticulo." - ".$consulta[0]->ART_Nombre." ya tiene los parametros ingresados. ¡Cambielos!";

            }
            else
            {

                $mensaje = "";

            }


        }
        else
        {

            $mensaje = "";

        }

        return ['Status' => 'Valido', 'Mensaje' => $mensaje];

    }

    public function consultaLocalidadesPorAlmacen(){

        try{

            ini_set('memory_limit', '-1');
            set_time_limit(0);

            $almacenes = $_POST['almacenes'];

            if($almacenes == "" || $almacenes == null){


                $consultaLocalidadesPorAlmacen = \DB::select(

                    \DB::raw(

                        "select LOC_LocalidadId, LOC_CodigoLocalidad + ' - ' + LOC_Nombre AS FULL_NAME
                        from Localidades
                        --where LOC_Eliminado=0 and LOC_LocalidadGeneral=0
                        order by LOC_CodigoLocalidad ASC"

                    )

                );

            }
            else{

                $consultaLocalidadesPorAlmacen = \DB::select(

                    \DB::raw(

                        "select LOC_LocalidadId, LOC_CodigoLocalidad + ' - ' + LOC_Nombre AS FULL_NAME
                        from Localidades
                        --where LOC_Eliminado=0 and LOC_LocalidadGeneral=0 and
                        where LOC_ALM_AlmacenId IN ('".$this->getCadenaId($almacenes)."')
                        order by LOC_CodigoLocalidad ASC"

                    )

                );

            }

            return ['Status' => 'Valido', 'respuesta' => $consultaLocalidadesPorAlmacen];

        } catch (\Exception $e){

            return ['Status' => 'Error', 'Mensaje' => 'Ocurrió un error al realizar el proceso. Error: ' .$e->getMessage()];

        }

    }

    public function consultaTiposAlmacenes(){

        try{

            ini_set('memory_limit', '-1');
            set_time_limit(0);

            $artId = $_POST['artId'];

            $consulta = \DB::select(
                \DB::raw(
                    "SELECT ARL_ALM_AlmacenId,ARL_LOC_LocalidadId FROM ArticulosLocalidades WHERE ARL_ART_ArticuloId = '".$artId."' AND ARL_Eliminado = 0"
                )
            );

            $cadena = '';
            $cuentaConsulta = count($consulta);
            for($x = 0; $x < $cuentaConsulta; $x ++){

                if($cadena == ''){

                    $cadena = "'".$consulta[$x]->ARL_ALM_AlmacenId."'";

                }
                else{

                    $cadena = $cadena.",'".$consulta[$x]->ARL_ALM_AlmacenId."'";

                }

            }

            if($cadena != ''){

                $consulta2 = \DB::select(
                    \DB::raw(
                        "SELECT LOC_LocalidadId,LOC_CodigoLocalidad + ' - ' + LOC_Nombre AS LOC_NombreFull FROM Localidades WHERE LOC_ALM_AlmacenId IN (".$cadena.") ORDER BY LOC_CodigoLocalidad, LOC_Nombre ASC"
                    )
                );

            }
            else{

                $consulta2 = array();

            }

            //$ajaxData = array();
            //$ajaxData['data'] = $consulta;
            return ['Status' => 'Valido', 'respuesta' => $consulta, 'respuesta2' => $consulta2];
            //return $ajaxData;

        } catch (\Exception $e){

            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json; charset=UTF-8');
            die(json_encode(array("mensaje" => $e->getMessage(),
                "codigo" => $e->getCode(),
                "clase" => $e->getFile(),
                "linea" => $e->getLine())));

        }

    }

    public function getCadenaId($ids){
        return str_replace(',', "', '", $ids);
    }

    public function consultaArticulosNuevo(){

        $consulta = \DB::select(
            \DB::raw(
                "SELECT
                    ART_ArticuloId AS DT_RowId,
                    ART_CodigoArticulo,
                    ART_Nombre,
                    AFAM_Nombre,
                    ACAT_Nombre,
                    ARTM_Nombre,
                    ATP_Descripcion
                FROM Articulos
                INNER JOIN ArticulosFamilias ON AFAM_FamiliaId = ART_AFAM_FamiliaId
                INNER JOIN ArticulosCategorias ON ACAT_CategoriaId = ART_ACAT_CategoriaId
                INNER JOIN ArticulosMarcas ON ARTM_MarcaId = ART_ARTM_MarcaId
                INNER JOIN ArticulosTipos ON ATP_TipoId = ART_ATP_TipoId
                WHERE ART_Eliminado = 0
                AND ART_Activo = 1
                AND ATP_Fabricado = 1"
            )
        );

        $ajaxData = array();
        $ajaxData['data'] = $consulta;
        $ajaxData['options'] = array();
        return (json_encode($ajaxData));

    }

}

