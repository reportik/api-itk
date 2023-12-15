<?php namespace App\Http\Controllers\Inventario\ImprimirTraspaso;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class GetTraspasosController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		//
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
        $TraspasoId = $_POST['TraspasoId'];
        $BanPrint = $_POST['Print'];
        $TypeDoc = $_POST['TypeDoc'];

        if($TypeDoc == 'true') {

            /*
            $Consulta = "TRS_CodigoSolicitud,
                CONVERT(VARCHAR(11), TRS_FechaSolicitud, 103) AS FECHA_SOLICITUD,
                CONVERT(VARCHAR(11), TRAD_FechaTraspaso, 103) AS FECHA_TRASPASO,
                CONVERT(VARCHAR(11), TRAR_FechaRecibo, 103) AS FECHA_RECIBO,
                ART_CodigoArticulo,
                ART_Nombre,
                CMUM_Nombre,
                LOT_CodigoLote,
                TRSD_Cantidad AS CANT_SOLICITADA,
                ABS(SUM(ISNULL(TRLOT_CantidadTraspaso, 0.0)) OVER (PARTITION BY TRLOT_LOTL_LoteLocalidadId)) AS CANT_A_TRASPASAR,
                ISNULL(DEVUELTOS.TRAR_CantidadRecibo, 0.0) AS CANT_DEVOLUCION,

                CASE
					WHEN
					(SELECT COUNT(TRAR_TRAD_TraspasoDetalleId) AS TOT
					FROM TraspasosRecibos WHERE TRAD_TraspasoDetalleId = TRAR_TRAD_TraspasoDetalleId AND TRAR_ReferenciaReciboId IS NULL) > 1
					THEN ISNULL(SUM((RECIBIDOS.TRAR_CantidadRecibo - ABS(ISNULL(DEVUELTOS.TRAR_CantidadRecibo, 0.0)))), 0.0)
					ELSE ISNULL((RECIBIDOS.TRAR_CantidadRecibo - ABS(ISNULL(DEVUELTOS.TRAR_CantidadRecibo, 0.0))), 0.0)
                END AS TOTAL_RECIBIDO,

                (ISNULL((SELECT TOP(1) ARTC_PrecioEmbarque FROM ArticulosCostos WHERE ARTC_ART_ArticuloId = ART_ArticuloId ORDER BY ARTC_FechaCosto DESC), 0.0) *
                ABS(CASE
					WHEN
					(SELECT COUNT(TRAR_TRAD_TraspasoDetalleId) AS TOT
					FROM TraspasosRecibos WHERE TRAD_TraspasoDetalleId = TRAR_TRAD_TraspasoDetalleId AND TRAR_ReferenciaReciboId IS NULL) > 1
					THEN ISNULL(SUM((RECIBIDOS.TRAR_CantidadRecibo - ABS(ISNULL(DEVUELTOS.TRAR_CantidadRecibo, 0.0)))), 0.0)
					ELSE ISNULL((RECIBIDOS.TRAR_CantidadRecibo - ABS(ISNULL(DEVUELTOS.TRAR_CantidadRecibo, 0.0))), 0.0)
                END)) AS VALOR,

				( ISNULL( DESTINO_A.ALM_CodigoAlmacen , '') + '/' + ISNULL( DESTINO_A.ALM_Nombre , '') + ' - ' + ISNULL( DESTINO_L.LOC_Nombre , '')) AS Origen,
				( ISNULL( ORIGEN_A.ALM_CodigoAlmacen , '') + '/' + ISNULL( ORIGEN_A.ALM_Nombre , '') + ' - ' + ISNULL( ORIGEN_L.LOC_Nombre , '')) AS Destino";
            */

            $Consulta = "
                  TRS_TraspasoSolicitudId,
                  TRS_CodigoSolicitud
                , CONVERT(VARCHAR(20), TRS_FechaSolicitud, 103) AS FECHA_SOLICITUD
                , CONVERT(VARCHAR(20), TRAD_FechaTraspaso, 103) AS FECHA_TRASPASO
                , FECHA_RECIBO = CONVERT(VARCHAR(20),(
                        SELECT TOP 1 TRAR_FechaRecibo
                        FROM TraspasosRecibos
                        INNER JOIN TraspasosDetalle i ON TRAR_TRAD_TraspasoDetalleId = TRAD_TraspasoDetalleId
                        INNER JOIN TraspasosMovtos ON TRAR_TRAM_TraspasoMovtoId = TRAM_TraspasoMovtoId
                        WHERE TRAR_Eliminado = 0 AND TRAM_CMM_TipoTransferenciaId = '1267D672-3920-48DA-9FE7-5C35E31C1BF6'
                        AND i.TRAD_TRSD_DetalleId = TRSD_DetalleId
                        ORDER BY
                            TRAR_FechaRecibo DESC
                ), 103)
                , ART_CodigoArticulo
                , ART_Nombre
                , CMUM_Nombre
                , LOT_CodigoLote
                , LOT_NumeroLote
                , TRSD_Cantidad AS CANT_SOLICITADA
                , ISNULL(TRASPASADO, 0.0) AS CANT_A_TRASPASAR
                , ISNULL(DEVUELTO, 0.0) AS CANT_DEVOLUCION
                , ISNULL(RECIBIDO, 0.0) AS TOTAL_RECIBIDO
                , ALM_ORIGEN.ALM_CodigoAlmacen + ' - ' + ALM_ORIGEN.ALM_Nombre + SPACE(1) + LOC_ORIGEN.LOC_CodigoLocalidad + ' - ' + LOC_ORIGEN.LOC_Nombre AS Destino
                , ALM_DESTINO.ALM_CodigoAlmacen + ' - ' + ALM_DESTINO.ALM_Nombre + SPACE(1) + LOC_DESTINO.LOC_CodigoLocalidad + ' - ' + LOC_DESTINO.LOC_Nombre AS Origen
                , ISNULL(RECIBIDO, 0.0) * ARTC_PrecioEmbarque AS VALOR
                , EMP_CodigoEmpleado + ' - ' + EMP_Nombre + SPACE(1) + ISNULL(EMP_PrimerApellido, '') + SPACE(1) + ISNULL(EMP_SegundoApellido, '') AS VENDEDOR
                , TRSD_DetalleId";

            $CadTemplate = 'Inventario.ImprimirTraspaso.PlantillaReporte.TraspasoReporte';
            //$JOIN = 'LEFT  JOIN';
        }

        else{

            /*
            $Consulta = "TRS_CodigoSolicitud,
                CONVERT(VARCHAR(11), TRS_FechaSolicitud, 103) AS FECHA_SOLICITUD,
                CONVERT(VARCHAR(11), TRAD_FechaTraspaso, 103) AS FECHA_TRASPASO,
                CONVERT(VARCHAR(11), TRAR_FechaRecibo, 103) AS FECHA_RECIBO,
                ART_CodigoArticulo,
                ART_Nombre,
                CMUM_Nombre,
                LOT_CodigoLote,

                ABS(CASE WHEN ART_CMUM_UMInventarioId = '70723AED-7F6A-4D9F-BD31-A74584B19A6A'
                THEN SUM(ISNULL(TRLOT_CantidadTraspaso, 0.0)) OVER (PARTITION BY TRLOT_LOTL_LoteLocalidadId)
                ELSE 0 END) AS PIEZAS_A_TRASPASAR,

                ABS(CASE WHEN ART_CMUM_UMInventarioId = '70723AED-7F6A-4D9F-BD31-A74584B19A6A'
                THEN ISNULL(SUM(TRLOT_CantidadTraspaso * ISNULL(AFC_FactorConversion, 0.0)) OVER (PARTITION BY TRLOT_LOTL_LoteLocalidadId), 0.0)
                ELSE ISNULL(SUM(TRLOT_CantidadTraspaso) OVER (PARTITION BY TRLOT_LOTL_LoteLocalidadId), 0.0)
                 END) AS KILOS_A_TRASPASAR,

                (ISNULL((SELECT TOP(1) ARTC_PrecioEmbarque FROM ArticulosCostos WHERE ARTC_ART_ArticuloId = ART_ArticuloId ORDER BY ARTC_FechaCosto DESC), 0.0)
                * ABS(ISNULL(
                    ABS(CASE WHEN ART_CMUM_UMInventarioId <> '70723AED-7F6A-4D9F-BD31-A74584B19A6A'
                    THEN ISNULL(SUM(TRLOT_CantidadTraspaso * ISNULL(AFC_FactorConversion, 0.0)) OVER(PARTITION BY TRLOT_LOTL_LoteLocalidadId), 0.0)
                    ELSE ISNULL(SUM(TRLOT_CantidadTraspaso) OVER(PARTITION BY TRLOT_LOTL_LoteLocalidadId), 0.0) END
                ),0.0))) AS VALOR,

                --(ISNULL((SELECT TOP(1) ARTC_PrecioEmbarque FROM ArticulosCostos WHERE ARTC_ART_ArticuloId = ART_ArticuloId ORDER BY ARTC_FechaCosto DESC), 0.0)
                --* ABS(
                --    CASE
                --        WHEN
                --        (SELECT COUNT(TRAR_TRAD_TraspasoDetalleId) AS TOT
                --        FROM TraspasosRecibos WHERE TRAD_TraspasoDetalleId = TRAR_TRAD_TraspasoDetalleId AND TRAR_ReferenciaReciboId IS NULL) > 1
                --        THEN ISNULL(SUM((RECIBIDOS.TRAR_CantidadRecibo - ABS(ISNULL(DEVUELTOS.TRAR_CantidadRecibo, 0.0)))), 0.0)
                --        ELSE ISNULL((RECIBIDOS.TRAR_CantidadRecibo - ABS(ISNULL(DEVUELTOS.TRAR_CantidadRecibo, 0.0))), 0.0)
                --    END)) AS VALOR,

				( ISNULL( DESTINO_A.ALM_CodigoAlmacen , '') + '/' + ISNULL( DESTINO_A.ALM_Nombre , '') + ' - ' + ISNULL( DESTINO_L.LOC_Nombre , '')) AS Origen,
				( ISNULL( ORIGEN_A.ALM_CodigoAlmacen , '') + '/' + ISNULL( ORIGEN_A.ALM_Nombre , '') + ' - ' + ISNULL( ORIGEN_L.LOC_Nombre , '')) AS Destino";
            */

            $Consulta = "
                  TRS_TraspasoSolicitudId,
                  TRS_CodigoSolicitud
                , CONVERT(VARCHAR(20), TRS_FechaSolicitud, 103) AS FECHA_SOLICITUD
                , CONVERT(VARCHAR(20), TRAD_FechaTraspaso, 103) AS FECHA_TRASPASO
                , FECHA_RECIBO = CONVERT(VARCHAR(20),(
                        SELECT TOP 1 TRAR_FechaRecibo
                        FROM TraspasosRecibos
                        INNER JOIN TraspasosDetalle i ON TRAR_TRAD_TraspasoDetalleId = TRAD_TraspasoDetalleId
                        INNER JOIN TraspasosMovtos ON TRAR_TRAM_TraspasoMovtoId = TRAM_TraspasoMovtoId
                        WHERE TRAR_Eliminado = 0 AND TRAM_CMM_TipoTransferenciaId = '1267D672-3920-48DA-9FE7-5C35E31C1BF6'
                        AND i.TRAD_TRSD_DetalleId = TRSD_DetalleId
                        ORDER BY
                            TRAR_FechaRecibo DESC
                ), 103)
                , ART_CodigoArticulo
                , ART_Nombre
                , CMUM_Nombre
                , LOT_CodigoLote
                , LOT_NumeroLote

                , ABS(CASE WHEN ART_CMUM_UMInventarioId = '70723AED-7F6A-4D9F-BD31-A74584B19A6A'
                  THEN ISNULL(TRASPASADO, 0.0)
                  ELSE 0 END) AS PIEZAS_A_TRASPASAR

                , ABS(CASE WHEN ART_CMUM_UMInventarioId = '70723AED-7F6A-4D9F-BD31-A74584B19A6A'
                  THEN ISNULL(TRASPASADO, 0.0) * ISNULL(AFC_FactorConversion, 0.0)
                  ELSE ISNULL(TRASPASADO, 0.0)
                  END) AS KILOS_A_TRASPASAR

                , ALM_ORIGEN.ALM_CodigoAlmacen + ' - ' + ALM_ORIGEN.ALM_Nombre + SPACE(1) + LOC_ORIGEN.LOC_CodigoLocalidad + ' - ' + LOC_ORIGEN.LOC_Nombre AS Destino
                , ALM_DESTINO.ALM_CodigoAlmacen + ' - ' + ALM_DESTINO.ALM_Nombre + SPACE(1) + LOC_DESTINO.LOC_CodigoLocalidad + ' - ' + LOC_DESTINO.LOC_Nombre AS Origen
                ,ISNULL(TRASPASADO, 0.0) * ARTC_PrecioEmbarque AS VALOR

                , EMP_CodigoEmpleado + ' - ' + EMP_Nombre + SPACE(1) + ISNULL(EMP_PrimerApellido, '') + SPACE(1) + ISNULL(EMP_SegundoApellido, '') AS VENDEDOR
                , TRSD_DetalleId";

            $CadTemplate = 'Inventario.ImprimirTraspaso.PlantillaReporte.TraspasoReporteGuia';
            //$JOIN = 'INNER JOIN';
        }

        /*
        $allConsulta = "SELECT DISTINCT
                $Consulta

            FROM TraspasosSolicitudes
            INNER JOIN TraspasosSolicitudesDetalle ON TRS_TraspasoSolicitudId = TRSD_TRS_TraspasoSolicitudId
            INNER JOIN Articulos ON TRSD_ART_ArticuloId = ART_ArticuloId
            INNER JOIN ControlesMaestrosUM ON ART_CMUM_UMInventarioId = CMUM_UnidadMedidaId
            LEFT  JOIN TraspasosDetalle ON TRSD_DetalleId = TRAD_TRSD_DetalleId AND TRAD_CMM_MotivoDevolucionId IS NULL
            LEFT  JOIN TraspasosMovtos ON TRAD_TRAM_TraspasoMovtoId = TRAM_TraspasoMovtoId
            LEFT  JOIN TraspasosRecibos AS RECIBIDOS ON TRAD_TraspasoDetalleId = TRAR_TRAD_TraspasoDetalleId AND TRAR_ReferenciaReciboId IS NULL
            LEFT  JOIN (SELECT TRAR_ReferenciaReciboId AS REFRENCIA, TRAR_CantidadRecibo FROM TraspasosRecibos) AS DEVUELTOS ON REFRENCIA = TRAR_TraspasoReciboId
            $JOIN TraspasosLotes ON TRAD_TRAM_TraspasoMovtoId = TRLOT_TRAM_TraspasoMovtoId
            LEFT  JOIN LotesLocalidades ON TRLOT_LOTL_LoteLocalidadId = LOTL_LoteLocalidadId
            LEFT  JOIN Lotes ON LOTL_LOT_LoteId = LOT_LoteId
            INNER JOIN Localidades ORIGEN_L ON TRS_LOC_LocalidadOrigenId = ORIGEN_L.LOC_LocalidadId
            INNER JOIN Almacenes ORIGEN_A ON ORIGEN_L.LOC_ALM_AlmacenId = ORIGEN_A.ALM_AlmacenId
            INNER JOIN Localidades DESTINO_L ON TRS_LOC_LocalidadDestinoId = DESTINO_L.LOC_LocalidadId
            INNER JOIN Almacenes DESTINO_A ON DESTINO_L.LOC_ALM_AlmacenId = DESTINO_A.ALM_AlmacenId
            LEFT  JOIN ArticulosFactoresConversion ON TRAM_ART_ArticuloId = AFC_ART_ArticuloId AND AFC_CMUM_UnidadMedidaId = '621D5394-CD57-4EBD-8EA9-C81C2124C6DA'

            WHERE TRS_TraspasoSolicitudId = '$TraspasoId'

            GROUP BY TRS_CodigoSolicitud, TRS_FechaSolicitud, TRAD_FechaTraspaso, TRAR_FechaRecibo,
            ART_CodigoArticulo, ART_Nombre, CMUM_Nombre, LOT_CodigoLote, TRSD_Cantidad, ART_ArticuloId,
            DEVUELTOS.TRAR_CantidadRecibo, RECIBIDOS.TRAR_CantidadRecibo, TRLOT_LOTL_LoteLocalidadId,
            DESTINO_A.ALM_CodigoAlmacen, DESTINO_A.ALM_Nombre, DESTINO_L.LOC_Nombre, TRLOT_CantidadTraspaso,
            ORIGEN_A.ALM_CodigoAlmacen, ORIGEN_A.ALM_Nombre, ORIGEN_L.LOC_Nombre
            , TRAR_TRAD_TraspasoDetalleId, TRAD_TraspasoDetalleId, ART_CMUM_UMInventarioId, AFC_FactorConversion
            , TRLOT_TraspasoLoteId

            ORDER BY ART_CodigoArticulo";

        */

        $allConsulta = "SELECT DISTINCT
            $Consulta

            FROM TraspasosSolicitudes
            INNER JOIN TraspasosSolicitudesDetalle ON TRS_TraspasoSolicitudId = TRSD_TRS_TraspasoSolicitudId AND TRSD_Eliminado = 0
            INNER JOIN (SELECT
                            ART_ArticuloId,
                            ART_CodigoArticulo,
                            ART_Nombre,
                            ART_CMUM_UMInventarioId,
                            ARTC_PrecioEmbarque = (SELECT TOP 1 ARTC_PrecioEmbarque
                                                    FROM ArticulosCostos
                                                    WHERE ARTC_ART_ArticuloId = ART_ArticuloId
                                                    ORDER BY
                                                        ARTC_FechaCosto DESC )
                        FROM Articulos
                        ) AS Articulos ON TRSD_ART_ArticuloId = ART_ArticuloId
            LEFT  JOIN ArticulosFactoresConversion ON ART_ArticuloId = AFC_ART_ArticuloId AND AFC_CMUM_UnidadMedidaId = '621D5394-CD57-4EBD-8EA9-C81C2124C6DA'
            INNER JOIN ControlesMaestrosUM ON TRSD_CMUM_UnidadMedidaId = CMUM_UnidadMedidaId
            LEFT  JOIN (
                        SELECT
                            TRAD_TRSD_DetalleId,
                            LOT_LoteId,
                            LOT_CodigoLote,
                            LOT_NumeroLote,
                            TRAD_FechaTraspaso,
                            SUM(ABS(TRLOT_CantidadTraspaso)) AS TRASPASADO
                        FROM TraspasosDetalle
                        INNER JOIN TraspasosLotes ON TRAD_TRAM_TraspasoMovtoId = TRLOT_TRAM_TraspasoMovtoId
                        INNER JOIN LotesLocalidades ON TRLOT_LOTL_LoteLocalidadId = LOTL_LoteLocalidadId
                        INNER JOIN Lotes ON LOTL_LOT_LoteId = LOT_LoteId
                        WHERE TRAD_CMM_MotivoDevolucionId IS NULL AND TRAD_Eliminado = 0
                        GROUP BY
                            TRAD_TRSD_DetalleId,
                            TRAD_FechaTraspaso,
                            LOT_LoteId,
                            LOT_CodigoLote,
                            LOT_NumeroLote
                    ) AS Traspasado ON TRSD_DetalleId = TRAD_TRSD_DetalleId
            LEFT  JOIN (
                        SELECT
                            TRSD_DetalleId AS DEVOLUCION_DETALLE_ID,
                            SUM(ABS(TRLOT_CantidadTraspaso)) AS DEVUELTO
                        FROM TraspasosDevolucionesDetalle
                        INNER JOIN TraspasosDetalle ON TRDD_TRAD_TraspasoDetalleId = TRAD_TraspasoDetalleId
                        INNER JOIN TraspasosSolicitudesDetalle ON TRSD_DetalleId = TRAD_TRSD_DetalleId
                        INNER JOIN TraspasosLotes ON TRAD_TRAM_TraspasoMovtoId = TRLOT_TRAM_TraspasoMovtoId
                        WHERE TRAD_CMM_MotivoDevolucionId IS NOT NULL AND TRAD_Eliminado = 0
                        GROUP BY
                            TRSD_DetalleId
                    ) AS DEVUELTO ON TRSD_DetalleId = DEVOLUCION_DETALLE_ID
            LEFT  JOIN (
                        SELECT
                            TRAD_TRSD_DetalleId AS DETALLE_ID,
                            SUM(TRAR_CantidadRecibo) AS RECIBIDO
                        FROM TraspasosRecibos
                        INNER JOIN TraspasosDetalle ON TRAR_TRAD_TraspasoDetalleId = TRAD_TraspasoDetalleId
                        WHERE TRAR_Eliminado = 0
                        GROUP BY
                            TRAD_TRSD_DetalleId
                    ) AS RECIBIDO ON TRSD_DetalleId = DETALLE_ID
            INNER JOIN Localidades LOC_ORIGEN ON TRS_LOC_LocalidadOrigenId = LOC_ORIGEN.LOC_LocalidadId
            INNER JOIN Almacenes ALM_ORIGEN ON LOC_ORIGEN.LOC_ALM_AlmacenId = ALM_ORIGEN.ALM_AlmacenId
            INNER JOIN Localidades LOC_DESTINO ON TRS_LOC_LocalidadDestinoId = LOC_DESTINO.LOC_LocalidadId
            INNER JOIN Almacenes ALM_DESTINO ON LOC_DESTINO.LOC_ALM_AlmacenId = ALM_DESTINO.ALM_AlmacenId
            LEFT  JOIN TransportesUnidades ON LOC_ORIGEN.LOC_LocalidadId = TUN_LOC_LocalidadId AND TUN_Activo = 1 AND TUN_Eliminado = 0
            LEFT  JOIN Rutas ON RUT_TUN_TransporteUnidadId = TUN_TransporteUnidadId AND RUT_Activo = 1 AND RUT_Eliminado = 0
            LEFT  JOIN Empleados ON RUT_EMP_VendedorId = EMP_EmpleadoId AND EMP_Activo = 1 AND EMP_Eliminado = 0
            WHERE TRS_Eliminado = 0 AND TRS_TraspasoSolicitudId = '$TraspasoId'
            ORDER BY
                TRS_CodigoSolicitud,
                ART_CodigoArticulo";

//dd($allConsulta);

        $results = \DB::select(\DB::raw($allConsulta));

        //date_default_timezone_set('America/Mexico_City');

        //dd($results);

        $pdfTemplate = view($CadTemplate, compact('results'))->render();
        require_once __DIR__ . '/../../../../../public/plugins/tcpdf/PlantillaReporte/REPORTESTRASP.php';

        /// create new PDF document
        //$cadenaFechaE='EXISTENCIA HASTA: '.date('Y/m/d');

        $pdf = new \REPORTESTRASP(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->setFechaImpresion(date('d/m/Y h:i:s a'));
        $pdf->Vendedor($results[0]->VENDEDOR);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('App');
        $pdf->SetTitle('Reporte Traspasos');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
        $pdf->setFooterData(array(0,64,0), array(0,64,128));

        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // ---------------------------------------------------------
        // Add a page
        // This method has several options, check the source code documentation for more information.
        $pdf->AddPage('P', 'A4');

        $pdf->SetFont('helvetica', '', 8, '', 'false');

        // set text shadow effect
        $pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));

        // Set some content to print
        $html =$pdfTemplate;

        // Print text using writeHTMLCell()
        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

        // ---------------------------------------------------------

        if($BanPrint == 'true'){

            // force print dialog
            $js = 'print(true);';

            // set javascript
            $pdf->IncludeJS($js);
        }

        /*$tbl =
            '<table cellpadding="5" cellspacing="1" border="0">

                <tr>
                    <td align="center" width="240"> Recibió: __________________________________________ </td>
                    <td width="30">  </td>
                    <td align="center" width="240"> Entregó: __________________________________________ </td>
                </tr>

                <tr>
                    <td align="center" width="240">' . $results[0]->VENDEDOR . '</td>
                </tr>

            </table>';

        $y = 280;
        $pdf->writeHTMLCell(80, '', '', $y, $tbl, 0, 0, 0, true, 'J', true);*/

        // Close and output PDF document
        // This method has several options, check the source code documentation for more information.
        $pdf->Output('Reporte de Traspasos.pdf', 'I');
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
