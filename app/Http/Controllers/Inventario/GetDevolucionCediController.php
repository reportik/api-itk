<?php namespace App\Http\Controllers\Inventario;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;


class GetDevolucionCediController extends Controller {

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
        $DevolucionId = $_POST['DevolucionId'];

        $Consulta = "SELECT
            DEP_Codigo, DEP_Nombre, EMP_CodigoEmpleado,
            (EMP_Nombre +' '+ EMP_PrimerApellido +' '+ EMP_SegundoApellido) AS RESPONSABLE,
            TRA_TraspasoId, TRA_CodigoTraspaso, TRA_Comentario,
            CONVERT(VARCHAR(15), TRA_FechaTraspaso, 106) AS FECHA,
            ART_CodigoArticulo, ART_Nombre, CMUM_Nombre, DC_CantidadDevuelta,
            ESTATUS.CMM_Valor, MOTIVO.CMM_Valor,
            ISNULL((SELECT TOP(1) ARTC_PrecioEmbarque FROM ArticulosCostos WHERE ARTC_ART_ArticuloId = ART_ArticuloId ORDER BY ARTC_FechaCosto DESC), 0.0) AS COSTO,
            (ISNULL((SELECT TOP(1) ARTC_PrecioEmbarque FROM ArticulosCostos WHERE ARTC_ART_ArticuloId = ART_ArticuloId ORDER BY ARTC_FechaCosto DESC), 0.0)
            * DC_CantidadDevuelta) AS IMPORTE, '' AS TOTAL_LETRAS
            --dbo.Convi_EnLetras((ISNULL((SELECT TOP(1) ARTC_PrecioEmbarque FROM ArticulosCostos WHERE ARTC_ART_ArticuloId = ART_ArticuloId ORDER BY ARTC_FechaCosto DESC), 0.0)
            --* DC_CantidadDevuelta), NULL) AS TOTAL_LETRAS

        FROM Traspasos
        INNER JOIN Departamentos ON TRA_DEP_DepartamentoId = DEP_DeptoId
        INNER JOIN Empleados ON DEP_EMP_EncargadoId = EMP_EmpleadoId
        INNER JOIN ControlesMaestrosMultiples AS ESTATUS ON ESTATUS.CMM_ControlId = TRA_CMM_EstadoTraspasoId
        INNER JOIN ControlesMaestrosMultiples AS MOTIVO ON MOTIVO.CMM_ControlId = TRA_CMM_MotivoTraspasoId
        INNER JOIN DevolucionesCedis ON DC_TRA_TraspasoId = TRA_TraspasoId
        INNER JOIN Articulos ON DC_ART_ArticuloId = ART_ArticuloId
        INNER JOIN ControlesMaestrosUM ON ART_CMUM_UMInventarioId = CMUM_UnidadMedidaId

        WHERE TRA_TraspasoId = '$DevolucionId'

        ORDER BY TRA_CodigoTraspaso";

        //dd($Consulta);

        $results = \DB::select(\DB::raw($Consulta));
        //dd($results);

        if(!$results)
            return $results;

        //date_default_timezone_set('America/Mexico_City');

        require public_path() . '\plugins\tcpdf\PlantillaReporte\REPORTEDEV.php';
        $pdfTemplate =view('Inventario.DevolucionesCedis.PlantillaReporte.DevolucionCedisReporte', compact('results'))->render();
        $pdf = new \REPORTEDEV(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        /// create new PDF document
        //$cadenaFechaE='EXISTENCIA HASTA: '.date('Y/m/d');
        $fechaImpreso='Impreso el: '.date('Y/m/d');
        $pdf->setCodigo($results[0]->TRA_CodigoTraspaso);
        $pdf->setTipoDocumento('DEVOLUCION');
        $pdf->setFechaImpresion(date('d/m/Y h:i:s a'));
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('App');
        $pdf->SetTitle('Reporte Devolucion Cedis');
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
        $pdf->SetTopMargin(38);
        $pdf->AddPage('P', 'A4');
        $pdf->SetFont('times', '', 8, '', 'false');
        // set text shadow effect
        $pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));
        // Print text using writeHTMLCell()
        $pdf->writeHTMLCell(0, 0, '', '', $pdfTemplate, 0, 1, 0, true, '', true);

        $pdf->Output('Reporte Devolucion Cedi.pdf', 'I');
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
