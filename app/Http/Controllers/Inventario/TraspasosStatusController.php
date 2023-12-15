<?php namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Sistema\DataBaseSession;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Response;
use Illuminate\Support\Facades\Request as NewRequest;

class TraspasosStatusController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
        //date_default_timezone_set('America/Mexico_City');
        $fecha=date('d/m/Y');

        return view('Inventario.TraspasosStatus.create', compact('fecha'));
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

        $encabezados = array(
            'Código',
            'Fecha',
            'Ruta',
            'Origen',
            'Destino',
            'Status'
        );

        $contenidos = array(
            'TRS_CodigoSolicitud',
            'CAST(TRS_FechaSolicitud AS DATE) AS TRS_FechaSolicitud',
            'RUT_Nombre',
            'ALM_OR.ALM_Nombre + ' - '+ Origen.LOC_Nombre as Origen',
            'ALM_DES.ALM_Nombre + ' - '+  Destino.LOC_Nombre as Destino',
            'CMM_Valor'
        );

        $results = \DB::select(

            \DB::raw(

                "select
                    --TRS_TraspasoSolicitudId as DT_RowId,
                    TRS_CodigoSolicitud,
                    CAST(TRS_FechaSolicitud AS DATE) AS TRS_FechaSolicitud,
                    RUT_Nombre,
                    ALM_OR.ALM_Nombre + ' - '+ Origen.LOC_Nombre as Origen,
                    ALM_DES.ALM_Nombre + ' - '+  Destino.LOC_Nombre as Destino,
                    CMM_Valor
                from TraspasosSolicitudes
                inner join Localidades Destino on Destino.LOC_LocalidadId = TRS_LOC_LocalidadDestinoId
                inner join Localidades Origen on Origen.LOC_LocalidadId = TRS_LOC_LocalidadOrigenId
                inner join Almacenes ALM_OR on ALM_OR.ALM_AlmacenId = Origen.LOC_ALM_AlmacenId
                inner join Almacenes ALM_DES on ALM_DES.ALM_AlmacenId = Destino.LOC_ALM_AlmacenId
                inner join ControlesMaestrosMultiples on CMM_ControlId = TRS_CMM_EstatusSolicitudId
                left join TransportesUnidades on TUN_LOC_LocalidadId = TRS_LOC_LocalidadOrigenId AND TUN_Eliminado = 0
                left join Rutas on RUT_TUN_TransporteUnidadId = TUN_TransporteUnidadId
                where TRS_Eliminado = 0
                AND TRS_FechaSolicitud >= convert(char(6), dateadd(month, -1, getdate()), 112) + '01'
                order by CAST(TRS_FechaSolicitud AS DATE) desc, TRS_CodigoSolicitud DESC"

            )

        );

        Excel::create('Reporte App', function($excel)use($encabezados,$contenidos,$results)
        {
            $excel->sheet('Sheetname', function($sheet)use($encabezados,$contenidos,$results)
            {
                $sheet->loadView('Inventario.TraspasosStatus.plantillasPdfExcel.createExcelBlade',compact('encabezados','contenidos','results'));
            });
        })->download('xlsx');

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

    public function ajaxResponseListado(){

        $FechaInicio = NewRequest::input('fechaDesde');
        $FechaFinal = NewRequest::input('fechaHasta');

        $consulta = \DB::select(

            \DB::raw(

                "select
                    TRS_TraspasoSolicitudId as DT_RowId,
                    TRS_CodigoSolicitud,
                    CAST(TRS_FechaSolicitud AS DATE) AS TRS_FechaSolicitud,
                    RUT_Nombre,
                    ALM_OR.ALM_Nombre + ' - '+ Origen.LOC_Nombre as Origen,
                    ALM_DES.ALM_Nombre + ' - '+  Destino.LOC_Nombre as Destino,
                    CMM_Valor
                from TraspasosSolicitudes
                inner join Localidades Destino on Destino.LOC_LocalidadId = TRS_LOC_LocalidadDestinoId
                inner join Localidades Origen on Origen.LOC_LocalidadId = TRS_LOC_LocalidadOrigenId
                inner join Almacenes ALM_OR on ALM_OR.ALM_AlmacenId = Origen.LOC_ALM_AlmacenId
                inner join Almacenes ALM_DES on ALM_DES.ALM_AlmacenId = Destino.LOC_ALM_AlmacenId
                inner join ControlesMaestrosMultiples on CMM_ControlId = TRS_CMM_EstatusSolicitudId
                left join TransportesUnidades on TUN_LOC_LocalidadId = TRS_LOC_LocalidadOrigenId AND TUN_Eliminado = 0
                left join Rutas on RUT_TUN_TransporteUnidadId = TUN_TransporteUnidadId
                where TRS_Eliminado = 0
                AND CAST(TRS_FechaSolicitud AS DATE) BETWEEN '".$FechaInicio."' AND '".$FechaFinal."'
                ".(DataBaseSession::isPermisoCorporativo() ? "" : " AND TRS_LOC_LocalidadOrigenId IN ( SELECT LOC_LocalidadId FROM Localidades WHERE LOC_ALM_AlmacenId IN (".DataBaseSession::getAlmacenesPorCediId()."))")."
                order by CAST(TRS_FechaSolicitud AS DATE) desc, TRS_CodigoSolicitud DESC"

            )

        );

        $ajaxData = array();
        $ajaxData['data'] = $consulta;
        $ajaxData['options'] = array();

        return (json_encode($ajaxData));

    }

}
