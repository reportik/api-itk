<?php namespace App\Http\Controllers\Inventario\ReporteCruceInventario;

use App\Http\Controllers\Sistema\DataBaseSession;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Rutas;

use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Facades\Excel;

class ReporteCruceInventarioController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		//dd('My Lost Lenore');

        //date_default_timezone_set('America/Mexico_City');

        $Reportes = $this->TiposReportes();

        $idCedi = DataBaseSession::getCediAsignadoId();

        $nombreCedi = \DB::table('Departamentos')
            ->where('DEP_DeptoId', '=', $idCedi)
            ->where('DEP_Eliminado', '=', 0)
            ->get()[0]->DEP_Nombre;

        if(strpos($nombreCedi, 'Matriz') !== false) {

            $rutas = Rutas::select('RUT_Codigo AS ID', 'RUT_Codigo')
                ->whereIn('RUT_Codigo', ['RDM', 'RGDL052', 'RGDL053', 'RGDL054', 'RGDL055', 'RGDL056', 'RGDL057', 'RGDL058', 'RGDL059', 'RGDL010', 'RGDL021', 'RGDL037'])
                ->orderby('RUT_Codigo')
                ->get()
                ->lists('ID', 'RUT_Codigo');

        }
        else{
            $rutas = [];
        }

        $date = date('d/m/Y');

        return view('Inventario.ReporteCruceInventario.Index', compact('Reportes', 'rutas', 'date'));
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
		//
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

//======================================================================================================================
//======================================================================================================================

    public function GenerarReporteCruceInventario(){

        //dd($_POST);
        $TipoReporte = $_POST['TipoReporte'];
        $NombreReporte = $_POST['NombreReporte'];
        $StartDate = $_POST['StartDate'];
        $EndDate = $_POST['EndDate'];
        $CodRuta = explode(',',$_POST['Ruta']);
        //$FechaImpresion = $this->getFormatoFecha($Fecha);

        $CadRuta = '';
        $ContRuta = count($CodRuta);
        for($x=0; $x<$ContRuta; $x++){
            if($x <= ($ContRuta-2))
                $CadRuta .= "'".$CodRuta[$x]."'".',';
            else
                $CadRuta .= "'".$CodRuta[$x]."'";
        }

        if($_POST['ChkbFactura'] == 'true')
            $where = 'FTR_FechaFactura';

        else
            $where = 'EMB_FechaEmbarque';

        $consulta = $this->ConsultasReportes($this->getFormatoFecha($StartDate), $this->getFormatoFecha($EndDate), $CadRuta, $where)[$TipoReporte];
        //dd($consulta);

        $result = \DB::select(\DB::raw($consulta));
        $this->Traslate_Excel($result, $TipoReporte, $NombreReporte);
        return json_encode(array());
    }
    public function getFormatoFecha($fecha)
    {
        $datos = explode(' ', $fecha);
        $fecha = explode('/', $datos[0]);
        $fecha = $fecha[2].$fecha[1].$fecha[0];
        return $fecha;
    }
    public function Traslate_Excel($Data, $TipoReporte, $NombreReporte){

        set_time_limit(0);
        ini_set('memory_limit', '512M');

        //date_default_timezone_set('America/Mexico_City');

        $encabezados = $this->EncabezadosReportes()[$TipoReporte];
        $contenidos = $this->ContenidosReportes()[$TipoReporte];

        if(count($Data) == 0)
            dd('No Se Encontraron Resultados');

        Excel::create('Reporte Cruce Inventario - '. $NombreReporte .'('.date('dmY[hisa]').')', function($excel)use($Data, $contenidos, $encabezados){

            $excel->sheet('', function($sheet)use($Data, $contenidos, $encabezados){
                $sheet->loadView('Inventario.ReporteCruceInventario.PlantillasExcel.PlantillaCruceInventario', compact('Data', 'contenidos', 'encabezados'));
            });
        })->download('xlsx');
    }
    public function TiposReportes(){

        $Aux = array(
            'CruceInventario' => 'Reporte de Cruce Inventario'
        );

        return $Aux;
    }
    public function EncabezadosReportes(){

        $encabezados = array(

            'CruceInventario' => array('N° FACTURA', 'FECHA FACTURA', 'CODIGO EMBARQUE', 'FECHA EMBARQUE',
                'CODIGO ARTICULO', 'PZ', 'KG')
        );

        return $encabezados;
    }
    public function ContenidosReportes(){

        $contenidos = array(

            'CruceInventario' => array('FTR_NumeroFactura', 'FTR_FechaFactura', 'EMB_CodigoEmbarque', 'EMB_FechaEmbarque',
                'ART_CodigoArticulo', 'PZ', 'KG')
        );

        return $contenidos;
    }
    public function ConsultasReportes($StartDate, $EndDate, $Rutas, $where){

        $Consultas = array(

            "CruceInventario" => "SELECT
                    FTR_NumeroFactura,
                    FTR_FechaFactura,
                    EMB_CodigoEmbarque,
                    EMB_FechaEmbarque,
                    ART_CodigoArticulo,
                    ROUND(SUM(CASE WHEN ART_CMUM_UMInventarioId = '70723AED-7F6A-4D9F-BD31-A74584B19A6A' THEN EMBD_CantidadEmbarcada ELSE 0 END), 2) AS PZ,
                    ROUND(SUM(CASE WHEN ART_CMUM_UMInventarioId = '70723AED-7F6A-4D9F-BD31-A74584B19A6A' THEN EMBD_CantidadEmbarcada * ISNULL(AFC_FactorConversion, 0.0) ELSE EMBD_CantidadEmbarcada END), 2) AS KG
                FROM Embarques
                INNER JOIN EmbarquesDetalle ON EMB_EmbarqueId = EMBD_EMB_EmbarqueId
                INNER JOIN TraspasosMovtos ON EMBD_TRAM_TraspasoMovtoId = TRAM_TraspasoMovtoId
                INNER JOIN TraspasosLocalidades ON TRAM_TraspasoMovtoId = TRLOC_TRAM_TraspasoMovtoId
                INNER JOIN LocalidadesArticulo ON TRLOC_LOCA_LocalidadArticuloId = LOCA_LocalidadArticuloId
                INNER JOIN Localidades ON LOCA_LOC_LocalidadId = LOC_LocalidadId
                LEFT  JOIN TransportesUnidades ON LOC_LocalidadId = TUN_LOC_LocalidadId
                LEFT  JOIN Rutas ON TUN_TransporteUnidadId = RUT_TUN_TransporteUnidadId
                INNER JOIN Articulos ON TRAM_ART_ArticuloId = ART_ArticuloId AND LOCA_ART_ArticuloId = ART_ArticuloId
                LEFT  JOIN ArticulosFactoresConversion ON ART_ArticuloId = AFC_ART_ArticuloId AND AFC_CMUM_UnidadMedidaId = '621D5394-CD57-4EBD-8EA9-C81C2124C6DA'
                LEFT  JOIN Facturas ON EMB_FTR_FacturaId = FTR_FacturaId AND FTR_Eliminado = 0
                INNER JOIN Clientes ON EMB_CLI_ClienteId = CLI_ClienteId
                WHERE --LOC_LocalidadId = 'AD69F54E-D428-4960-B721-EB48CC3B6C3F'
                      $where BETWEEN '$StartDate 00:00:00' AND '$EndDate 23:59:59'
                      AND RUT_Codigo IN ($Rutas)
                      --AND FTR_NumeroFactura = 'G1000378'
                GROUP BY
                    FTR_NumeroFactura,
                    FTR_FechaFactura,
                    EMB_CodigoEmbarque,
                    EMB_FechaEmbarque,
                    ART_CodigoArticulo
                HAVING ROUND(SUM(EMBD_CantidadEmbarcada), 2) > 0
                ORDER BY
                    FTR_NumeroFactura,
                    EMB_CodigoEmbarque"
        );

        return $Consultas;
    }
}
