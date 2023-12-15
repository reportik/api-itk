<?php namespace App\Http\Controllers\Inventario\Localidades;

use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Facades\Excel;

use View;



use App\Http\Requests\CreateLocalidad;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\GeneralController;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\AdmonSistema\Ciudad;
use App\Models\AdmonSistema\Estado;
use App\Models\AdmonSistema\Pais;
use App\Models\Inventario\Localidad;
use Response;

class LocalidadesReporteExcelController extends Controller {


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

        $date= date('d/m/Y   g:i a');
        /////////////////**********Para aumentar la capacidad de memoria ***********///////////////////
        ini_set('memory_limit', '-1');
        // ini_set('memory_limit', '1G');
        //ini_set('max_execution_time', 300);
        set_time_limit(0);

        /////////////////*********************///////////////////

        $encabezados=array(
           // 'id',

        'Código Localidad'
        ,'Nombre'
        ,'Almacen'
        ,'Planear'
        ,'Localidad General'
        ,'Fecha Ultima Modificación'
        ,'Modificado Por'
        );
        $contenidos=array(
          //  'LOC_LocalidadId',

        'LOC_CodigoLocalidad'
        ,'LOC_Nombre'
        ,'LOC_ALM_AlmacenId'
        ,'LOC_Planear'
        ,'LOC_LocalidadGeneral'
        ,'LOC_FechaUltimaModificacion'
        ,'LOC_EMP_ModificadoPorId'

        );

        $results=\DB::select(\DB::raw(
            "
select
LOC_LocalidadId
,LOC_CodigoLocalidad
,LOC_Nombre
,ALM_Nombre AS LOC_ALM_AlmacenId --,LOC_ALM_AlmacenId
--,CMM_Valor AS LOC_CMM_CtaPredInvId--,LOC_CMM_CtaPredInvId
,LOC_Planear
,LOC_LocalidadGeneral
,LOC_FechaUltimaModificacion
,EMP_Nombre AS LOC_EMP_ModificadoPorId --,LOC_EMP_ModificadoPorId

from Localidades
LEFT JOIN Almacenes on LOC_ALM_AlmacenId =ALM_AlmacenId
--LEFT JOIN ControlesMaestrosMultiples ON LOC_CMM_CtaPredInvId = CMM_ControlId
LEFT JOIN Empleados on LOC_EMP_ModificadoPorId = EMP_EmpleadoId

WHERE LOC_Eliminado = 0

order by LOC_LocalidadId
            "
        ));


       /* $results = \DB::table('Localidades')->where('LOC_Eliminado', '0')->where('LOC_LocalidadGeneral','0')
            ->join('Almacenes', 'Localidades.LOC_ALM_AlmacenId', '=', 'Almacenes.ALM_AlmacenId')
            ->get();*/


        $htmlT=$request->input('nuevoinput');
        // dd($htmlT);
        $tipoReporte=$request->input('tipoReporte');

        // dd($request->all());
        if($tipoReporte=="excel"){
            Excel::create('Reporte App', function($excel)use($encabezados,$contenidos,$results)
            {
                $excel->sheet('Sheetname', function($sheet)use($encabezados,$contenidos,$results)
                {
                    $sheet->loadView('Inventario.Localidades.plantillasPdfExcel.createExcelBlade',compact('encabezados','contenidos','results'));

                });
            })->download('xlsx');
        }



        else{
            $name  = 'Reporte.pdf';

            $pdf=App::make('dompdf.wrapper');




            $pdf->loadView('Inventario.Localidades.plantillasPdfExcel.createPdfBlade',compact('htmlT','date'));
            // return view ('Transportes.TransportesUnidades.createPdfBlade',compact('htmlT','imagen','imagen2'));
            // $font = Font_Metrics::get_font("helvetica", "bold");

            return $pdf->stream($name);
        }


    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */

    public function show()
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
    public function edit()
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
            'Codigo de localidad',
            'Nombre de la localidad',
            'Nombre del Almacen'

        );
        $contenidosBase=array(
            'LOC_LocalidadId',
            'LOC_CodigoLocalidad',
            'LOC_Nombre',
            'ALM_Nombre'

        );






        $encabezados= array();
        $contenidos=array();


        if($request->all()!=null){
            $ar = array();
            $ar[0]=$request->get('CodigoModalBA');
            $ar[1]=$request->get('NombreModalBA');
            $ar[2]=$request->get('AlmacenModalBA');


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
        $results = \DB::table('Localidades')->where('LOC_Eliminado', '0')->where('LOC_LocalidadGeneral','0')
            ->join('Almacenes', 'Localidades.LOC_ALM_AlmacenId', '=', 'Almacenes.ALM_AlmacenId')
            ->get();


        $almacen =\DB::table('Almacenes')->where('ALM_Eliminado', '0')->orderBy('ALM_Nombre','ASC')->lists('ALM_Nombre', 'ALM_AlmacenId');
        $almaceneS = array('' => 'Seleccione') + $almacen;


        return view('Inventario.Localidades.indexLocalidad',compact('results','encabezados','contenidos','almaceneS'));
    }


}
