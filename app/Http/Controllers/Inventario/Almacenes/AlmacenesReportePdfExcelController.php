<?php namespace App\Http\Controllers\Inventario\Almacenes;


use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Facades\Excel;

use View;



use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Models\Inventario\Almacen;

use Response;

class AlmacenesReportePdfExcelController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    //

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
//dd("OA OA OA OA OA OCHOA FIGUROA");
        $date= date('d/m/Y   g:i a');
        /////////////////**********Para aumentar la capacidad de memoria ***********///////////////////
        ini_set('memory_limit', '-1');
        // ini_set('memory_limit', '1G');
        //ini_set('max_execution_time', 300);
        set_time_limit(0);
        /////////////////*********************///////////////////
        $encabezados=array(
            // 'id',
            'Código Almacen'
        ,'Nombre'
        ,'Departamentos'
        ,'Responsable'
        ,'Direccion'
        ,'Código  Postal'
        ,'Colonia'
        ,'Ciudad'
        ,'Estado'
        ,'Pais'
        ,'Correo Electronico'
        ,'Telefono'
        ,'Extension'
        ,'Fax'
        ,'Almacen Predeterminado'
        ,'CtaPredInv'
        ,'Planear'
        ,'Fecha Ultima Modificacion'
        ,'Modificado Por'
        );
        $contenidos=array(
            // 'ALM_AlmacenId',

            'ALM_CodigoAlmacen'
        ,'ALM_Nombre'
        ,'Departamentos'
        ,'ALM_EMP_ResponsableId'
        ,'ALM_Direccion'
        ,'ALM_CodigoPostal'
        ,'ALM_CIUC_ColoniaId'
        ,'ALM_CIU_CiudadId'
        ,'ALM_EST_EstadoId'
        ,'ALM_PAI_PaisId'
        ,'ALM_CorreoElectronico'
        ,'ALM_Telefono'
        ,'ALM_Extension'
        ,'ALM_Fax'
        ,'ALM_AlmacenPredeterminado'
        ,'ALM_CMM_CtaPredInvId'
        ,'ALM_Planear'
        ,'ALM_FechaUltimaModificacion'
        ,'ALM_EMP_ModificadoPorId'
        );
        $results=\DB::select(\DB::raw(
            "
Select
ALM_AlmacenId
,ALM_CodigoAlmacen
,ALM_Nombre
,dbo.getCEDISPorAlmacenId (ALM_AlmacenId) as Departamentos
,EmpleadoResponsable.EMP_Nombre AS ALM_EMP_ResponsableId --,ALM_EMP_ResponsableId
,ALM_Direccion
,ALM_CodigoPostal
,CIUC_Nombre AS ALM_CIUC_ColoniaId --,ALM_CIUC_ColoniaId
,CIU_Nombre AS ALM_CIU_CiudadId --,ALM_CIU_CiudadId
,EST_Nombre AS ALM_EST_EstadoId --,ALM_EST_EstadoId
,PAI_Nombre AS ALM_PAI_PaisId --,ALM_PAI_PaisId
,ALM_CorreoElectronico
,ALM_Telefono
,ALM_Extension
,ALM_Fax
,ALM_AlmacenPredeterminado
,CtaPred.CMM_Valor AS ALM_CMM_CtaPredInvId --,ALM_CMM_CtaPredInvId
,ALM_Planear
,ALM_FechaUltimaModificacion
,ModificadoPor.EMP_Nombre AS ALM_EMP_ModificadoPorId --,ALM_EMP_ModificadoPorId
FROM Almacenes
left join Empleados AS EmpleadoResponsable on Almacenes.ALM_EMP_ResponsableId = EmpleadoResponsable.EMP_EmpleadoId
left join CiudadesColonias on ALM_CIUC_ColoniaId = CIUC_ColoniaId
LEFT JOIN Ciudades on ALM_CIU_CiudadId = CIU_CiudadId
left join Estados on ALM_EST_EstadoId = EST_EstadoId
left join Paises on ALM_PAI_PaisId = PAI_PaisId
left join ControlesMaestrosMultiples AS CtaPred on ALM_CMM_CtaPredInvId = CMM_ControlId
left join Empleados AS ModificadoPor on Almacenes.ALM_EMP_ModificadoPorId = ModificadoPor.EMP_EmpleadoId

WHERE ALM_Eliminado = 0
order by ALM_AlmacenId
          "
        ));


        $htmlT=$request->input('nuevoinput');
        // dd($htmlT);
        $tipoReporte=$request->input('tipoReporte');

        // dd($request->all());
        if($tipoReporte=="excel"){
            Excel::create('Reporte App', function($excel)use($encabezados,$contenidos,$results)
            {
                $excel->sheet('Sheetname', function($sheet)use($encabezados,$contenidos,$results)
                {
                    $sheet->loadView('Inventario.Almacenes.plantillasPdfExcel.createExcelBlade',compact('encabezados','contenidos','results'));

                });
            })->download('xlsx');
        }



        else{

            /*  dd('No disponible cambiare de libreria de Dompdf a html2pdf');
              $name  = 'Reporte.pdf';

              $pdf=App::make('dompdf.wrapper');




              $pdf->loadView('inventario.Almacenes.plantillasPdfExcel.createPdfBlade',compact('htmlT','date'));
              // return view ('Transportes.TransportesUnidades.createPdfBlade',compact('htmlT','imagen','imagen2'));
              // $font = Font_Metrics::get_font("helvetica", "bold");

              return $pdf->stream($name);  */

            dd('Esta parte no se ocupa por el momento :P');
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


    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update()
    {

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */


    public function destroy()
    {

    }

    //Funcion para busqueda avanzada dentro del ModalBusqueda Avanzada

    public function paraBusqueda(Request $request )
    {
        //dd("estas aqui");
        // dd($request->get('MarcaModalBA'));
        $encabezadosBase=array(
            'id',
            'Codigo del almacen',
            'Nombre del almacen',
            'Departamento'

        );
        $contenidosBase=array(
            'ALM_AlmacenId',
            'ALM_CodigoAlmacen',
            'ALM_Nombre',
            'Departamentos'

        );







        $encabezados= array();
        $contenidos=array();


        if($request->all()!=null){
            $ar = array();
            $ar[0]=$request->get('CodigoModalBA');
            $ar[1]=$request->get('NombreModalBA');
            $ar[2]=$request->get('DepartamentoModalBA');


            $encabezados[0]=$encabezadosBase[0];
            $contenidos[0]=$contenidosBase[0];


            $longitud_ar=2;

            $contadorPocicionparaArreglos=1;
            $contadorArregloSinValor=0;

            for($x=0; $x<=$longitud_ar; $x++){
                if($ar[$x]!=null){
                    $encabezados[$contadorPocicionparaArreglos]= $encabezadosBase[$x+1];
                    $contenidos[$contadorPocicionparaArreglos]=$contenidosBase[$x+1];

                    $contadorPocicionparaArreglos=$contadorPocicionparaArreglos+1;
                }else{
                    $contadorArregloSinValor=$contadorArregloSinValor+1;
                    if($contadorArregloSinValor==$longitud_ar+1){
                        $encabezados=$encabezadosBase;
                        $contenidos=$contenidosBase;

                    }

                }

            }


        }


        //Aqui mandamos todos los campos puesto que no se ha mandado ninguna peticion de busqueda avanzada
        else{
            $encabezados=$encabezadosBase;
            $contenidos=$contenidosBase;


        }
        // dd($encabezados);
        $results = \DB::table('Almacenes')->select('*',\DB::raw('dbo.getCEDISPorAlmacenId (ALM_AlmacenId) as Departamentos'))
            ->where('ALM_Eliminado', '0')

            //->join('Departamentos', 'Almacenes.ALM_DEP_DeptoId', '=', 'Departamentos.DEP_DeptoId')


            ->get();
        //dd($results);

        $Acentamiento=\DB::table('ControlesMaestrosMultiples')->where('CMM_Control', '=','CMM_CRH_TipoAsentamiento' )->where('CMM_Eliminado', '0')->lists('CMM_Valor', 'CMM_ControlId');
        $tipoAcentamiento = array('' => 'Seleccione') + $Acentamiento;
        $Zona=\DB::table('ControlesMaestrosMultiples')->where('CMM_Control', '=','CMM_CRH_ZonaAsentamiento' )->where('CMM_Eliminado', '0')->lists('CMM_Valor', 'CMM_ControlId');
        $zonaAcentamiento = array('' => 'Seleccione') + $Zona;






        $empleados=\DB::table('Empleados')->where('EMP_Eliminado', '0')->lists('EMP_Nombre', 'EMP_EmpleadoId');
        $responsable=array(''=>'seleccione')+$empleados;
        $departamentos=\DB::table('Departamentos')
            ->join('ControlesMaestrosMultiples', 'Departamentos.DEP_CMM_TipoDeptoId', '=', 'ControlesMaestrosMultiples.CMM_ControlId')
            ->where('DEP_Eliminado', '0')->where('CMM_Valor', 'Sucursal')->lists('DEP_Nombre', 'DEP_DeptoId');
        $depAlmacen=array(''=>'seleccione')+$departamentos;

        return view('Inventario.Almacenes.indexAlmacen',compact('results','encabezados','contenidos','responsable','depAlmacen','tipoAcentamiento','zonaAcentamiento'));
    }



}