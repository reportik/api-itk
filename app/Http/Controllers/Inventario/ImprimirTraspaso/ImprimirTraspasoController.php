<?php namespace App\Http\Controllers\Inventario\ImprimirTraspaso;

use App\Http\Controllers\CFDI\EncabezadoReportePDF;
use App\Http\Controllers\Sistema\DataBaseSession;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request AS NewRequest;


class ImprimirTraspasoController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
        //date_default_timezone_set('America/Mexico_City');

        $fecha = date('d/m/Y');
        return view('Inventario.ImprimirTraspaso.Index', compact('fecha'));
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

    public function getDatosTraspasos(){

        $FechaInicio = NewRequest::input('fechaDesde');
        $FechaFinal = NewRequest::input('fechaHasta');

        $results = \DB::select(\DB::raw(
            "SELECT DISTINCT
                TRS_TraspasoSolicitudId,
                TRS_CodigoSolicitud,
                CONVERT(VARCHAR(11), TRS_FechaSolicitud, 103) AS TRS_FechaSolicitud,
                CONVERT(VARCHAR(11), TRAD_FechaTraspaso, 103) AS TRAD_FechaTraspaso,
                CONVERT(VARCHAR(11), TRAR_FechaRecibo, 103) AS TRAR_FechaRecibo,
                CMM_Valor,

                ( ISNULL( DESTINO_A.ALM_CodigoAlmacen , '') + '/' + ISNULL( DESTINO_A.ALM_Nombre , '') + ' - ' + ISNULL( DESTINO_L.LOC_Nombre , '')) AS Origen,
				( ISNULL( ORIGEN_A.ALM_CodigoAlmacen , '') + '/' + ISNULL( ORIGEN_A.ALM_Nombre , '') + ' - ' + ISNULL( ORIGEN_L.LOC_Nombre , '')) AS Destino

            FROM TraspasosSolicitudes
            INNER JOIN TraspasosSolicitudesDetalle ON TRS_TraspasoSolicitudId = TRSD_TRS_TraspasoSolicitudId
            INNER JOIN TraspasosDetalle ON TRSD_DetalleId = TRAD_TRSD_DetalleId AND TRAD_CMM_MotivoDevolucionId IS NULL

            LEFT JOIN --TraspasosRecibos
            (SELECT TRAR_TRAD_TraspasoDetalleId, TRAR_FechaRecibo,
            ROW_NUMBER() OVER(PARTITION BY TRAR_TRAD_TraspasoDetalleId ORDER BY TRAR_FechaRecibo DESC) AS VECES
            FROM TraspasosRecibos
        	WHERE TRAR_CantidadRecibo > 0) AS TEMP
            ON TRAD_TraspasoDetalleId = TRAR_TRAD_TraspasoDetalleId

            INNER JOIN ControlesMaestrosMultiples ON TRS_CMM_EstatusSolicitudId = CMM_ControlId
            INNER JOIN Localidades ORIGEN_L ON TRS_LOC_LocalidadOrigenId = ORIGEN_L.LOC_LocalidadId
            INNER JOIN Almacenes ORIGEN_A ON ORIGEN_L.LOC_ALM_AlmacenId = ORIGEN_A.ALM_AlmacenId
            INNER JOIN Localidades DESTINO_L ON TRS_LOC_LocalidadDestinoId = DESTINO_L.LOC_LocalidadId
            INNER JOIN Almacenes DESTINO_A ON DESTINO_L.LOC_ALM_AlmacenId = DESTINO_A.ALM_AlmacenId

            WHERE
            ".(DataBaseSession::isPermisoCorporativo() ? " "
                : "( ORIGEN_A.ALM_AlmacenId IN ( ".DataBaseSession::getAlmacenesPorCediId()." )
                OR DESTINO_A.ALM_AlmacenId IN ( ".DataBaseSession::getAlmacenesPorCediId()." ) )  AND ")."
             (CAST(TRS_FechaSolicitud AS DATE) BETWEEN CAST('$FechaInicio' AS DATE) AND CAST('$FechaFinal' AS DATE)
            AND VECES = 1
            OR
            CAST(TRS_FechaSolicitud AS DATE) BETWEEN CAST('$FechaInicio' AS DATE) AND CAST('$FechaFinal' AS DATE)
            AND VECES IS NULL)

            ORDER BY TRS_FechaSolicitud ASC"
        ));

        $ajaxData = array();
        $ajaxData['data'] = $results;
        $ajaxData['options'] = array();

        return (json_encode($ajaxData));
    }
}
