<?php namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Sistema\DataBaseSession;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as NewRequest;
use App\Mapeos\Controles\ControlesMaestrosMultiples;
use App\Models\Articulos;
use App\Models\ControlesMaestros;
use App\Models\Inventario\Articulos\Categoria;
use App\Models\Inventario\Articulos\Familia;
use App\Models\Lotes;
use App\Models\LotesPreliminares;
use App\Models\Ventas\ListasPrecios\CMMult;
use App\Models\Ventas\Promociones\ArticulosMarcas;
use Response;

class LotesController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {

        $marcas=array(''=>'Seleccione Marca') + ArticulosMarcas::select('ARTM_MarcaId',  \DB::raw("ARTM_Codigo + ' - ' + ARTM_Nombre AS FULL_NAME"))
                ->orderBy('ARTM_Codigo','ASC')
                ->lists('FULL_NAME', 'ARTM_MarcaId')->all();
        $familias=array(''=>'Seleccione Familia') + Familia::select('AFAM_FamiliaId',  \DB::raw("AFAM_Codigo + ' - ' + AFAM_Nombre AS FULL_NAME"))
                ->orderBy('AFAM_Codigo','ASC')
                ->lists('FULL_NAME', 'AFAM_FamiliaId')->all();
        $categorias=array(''=>'Seleccione Categoría') + Categoria::select('ACAT_CategoriaId',  \DB::raw("ACAT_Codigo + ' - ' + ACAT_Nombre AS FULL_NAME"))
                ->orderBy('ACAT_Codigo','ASC')
                ->lists('FULL_NAME', 'ACAT_CategoriaId')->all();
        $subcategorias=array(''=>'Seleccione Sub - Categoría') + CMMult::where('CMM_Control', '=', 'CMM_INV_SubcategoriaArticulos')
                ->get()
                ->lists('CMM_Valor', 'CMM_ControlId')->all();
        $articulos=array(''=>'Seleccione Articulos') + Articulos::select('ART_ArticuloId', \DB::raw("ART_CodigoArticulo + ' - ' + ART_Nombre AS FULL_NAME"))
                ->orderBy('ART_Nombre','ASC')
                ->lists('FULL_NAME', 'ART_ArticuloId')->all();

        $encabezados =array(
            'Id',
            'Número de Lote - Preliminar',
            'Articulo',
            'Código de Lote - Preliminar',
            'Fecha',
            'Estatus'
        );

        $contenidos=array(
            'LOTP_LotePreliminarId',
            'LOTP_NumeroLotePreliminar',
            'ART_Nombre',
            'LOTP_CodigoLotePreliminar',
            'LOTP_FechaLotePreliminar',
            'CMM_Valor'
        );

        $results=\DB::table('LotesPreliminares')
            ->join('Articulos','LOTP_ART_ArticuloId','=','ART_ArticuloId')
            ->join('ControlesMaestrosMultiples', 'LOTP_CMM_EstatusLotePreliminarId', '=', 'CMM_ControlId')
            ->where('LOTP_Eliminado','=',0)
            ->whereRaw("LOTP_FechaLotePreliminar >= convert(char(6), dateadd(month, -1, getdate()), 112) + '01'")
            ->select($contenidos)
            ->orderBy('LOTP_FechaLotePreliminar','DESC')
            ->get();

        //date_default_timezone_set('America/Mexico_City');
        $fecha=date('d/m/Y');

        return view('Inventario.Lotes.create', compact('familias', 'categorias','marcas', 'subcategorias','articulos','fecha'));
    }

    public function buscaLotesPreliminares(){

        $FechaInicio = NewRequest::input('fechaDesde');
        $FechaFinal = NewRequest::input('fechaHasta');

        $consultaLotes = \DB::select(
            \DB::raw(
                "SELECT
                    LOTP_LotePreliminarId AS DT_RowId,
                    LOTP_NumeroLotePreliminar,
                    ART_Nombre,
                    LOTP_CodigoLotePreliminar,
                    CAST(LOTP_FechaLotePreliminar AS DATE) AS LOTP_FechaLotePreliminar,
                    CMM_Valor
                FROM LotesPreliminares
                INNER JOIN Articulos ON LOTP_ART_ArticuloId = ART_ArticuloId
                INNER JOIN ControlesMaestrosMultiples ON CMM_ControlId = LOTP_CMM_EstatusLotePreliminarId
                WHERE LOTP_Eliminado = 0
                AND CAST(LOTP_FechaLotePreliminar AS DATE) BETWEEN '".$FechaInicio."' AND '".$FechaFinal."'
                ORDER BY LOTP_FechaLotePreliminar DESC"
            )
        );
        $ajaxData = array();
        $ajaxData['data'] = $consultaLotes;
        $ajaxData['options'] = array();
        return (json_encode($ajaxData));

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

        \DB::beginTransaction();

        try {

            $idEmpleado = DataBaseSession::getEmpleadoId();

            $totalArticulosSeleccionados = $_POST['CCON_contador'];

            for($x = 1; $x <= $totalArticulosSeleccionados; $x ++)
                //for($x = 1; $x <= 100000000; $x ++)
            {

                $numeroLote = substr($_POST['LOT_CodigoLote'.$x], 0, 3);
                $fechaLote = $_POST['IF_FechaInventario2'];

                \DB::table('LotesPreliminares')->insert(

                    array(

                        'LOTP_NumeroLotePreliminar' => $numeroLote,
                        'LOTP_ART_ArticuloId' => $_POST['ART_ArticuloId'.$x],
                        'LOTP_CodigoLotePreliminar' => $_POST['LOT_CodigoLote'.$x],
                        'LOTP_FechaLotePreliminar' => $fechaLote,
                        'LOTP_EMP_ModificadoPorId' => $idEmpleado,
                        'LOTP_CMM_EstatusLotePreliminarId' => ControlesMaestrosMultiples::$CMM_EstatusLote_Abierto

                    )

                );

            }

            //return json_encode(array());

            $mensaje = 'Los Pre - Lotes se registraron con éxito.';

            \DB::commit();

            return ['Status' => 'Valido', 'Mensaje' => $mensaje];

        }
        catch (\Exception $e) {

            \DB::rollback();

            return ['Status' => 'Error', 'Mensaje' => 'No se registraron Los Pre - Lotes. Ocurrió un error al realizar el proceso. <br><br><br>Error: <br>'.$e->getMessage()];

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

        $res = LotesPreliminares::find($id);

        //dd($res->LOTP_ART_ArticuloId);
        $articulos=array(''=>'') + Articulos::select('ART_ArticuloId', \DB::raw("ART_CodigoArticulo + ' - ' + ART_Nombre AS FULL_NAME"))
                ->orderBy('ART_Nombre','ASC')
                ->lists('FULL_NAME', 'ART_ArticuloId');

        $controles=array(''=>'') + \App\Models\Inventario\Articulos\CMMult::select('CMM_ControlId','CMM_Valor')
                ->orderBy('CMM_Valor','ASC')
                ->lists('CMM_Valor','CMM_ControlId');

        return view('Inventario.Lotes.editar', compact('res','id','articulos','controles'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        \DB::beginTransaction();

        try {

            //date_default_timezone_set('America/Mexico_City');
            $hoy=date('d/m/Y');

            $idEmpleado = DataBaseSession::getEmpleadoId();

            \DB::table('LotesPreliminares')->where('LOTP_LotePreliminarId', '=', $id)
                ->update(
                    array(
                        'LOTP_EMP_ModificadoPorId' => $idEmpleado,
                        'LOTP_FechaUltimaModificacion' => $hoy,
                        'LOTP_CMM_EstatusLotePreliminarId' => ControlesMaestrosMultiples::$CMM_EstatusLote_Empacado
                    )
                );

            //return json_encode(array());

            $mensaje = 'Los Pre - Lote han sido cerrado con éxito.';

            \DB::commit();

            return ['Status' => 'Valido', 'Mensaje' => $mensaje];

        }
        catch (\Exception $e) {

            \DB::rollback();

            return ['Status' => 'Error', 'Mensaje' => 'No se cerraron Los Pre - Lotes. Ocurrió un error al realizar el proceso. <br><br><br>Error: <br>'.$e->getMessage()];

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
        //
    }

    public static function consultarcantidaddecimales(){
        $result=ControlesMaestros::select('CMA_Valor')
            ->where('CMA_Control','=','CMA_INV_DecimalesCantidades')
            ->get();
        return Response::json($result);
    }

    public static function consultarcantidaddecimalesgeneral(){
        $result=ControlesMaestros::select('CMA_Valor')
            ->where('CMA_Control','=','CMA_CSVP_DecimalesCantidades')
            ->get();
        return Response::json($result);
    }

    public static function ConsultarUltimoNumeroLote(){
        $result = Lotes::orderby('LOT_NumeroLote','DESC')->first()->LOT_NumeroLote;

        return Response::json($result);
    }

    public static function ConsultarUltimaFechaRegistrada(){
        $consultaUltimaFechaLote = Lotes::orderby('LOT_FechaLote','DESC')->first()->LOT_FechaLote;

        return Response::json($consultaUltimaFechaLote);
    }

    public static function ConsultarLotesFechasSeleccionadas($f1,$f2){
        $consultaLotesCreados = \DB::select(
            \DB::raw(
                "SELECT LOT_CodigoLote, LOT_CantidadOriginal, LOT_FechaLote, ART_CodigoArticulo, ART_Nombre
                FROM Lotes INNER JOIN Articulos ON LOT_ART_ArticuloId = ART_ArticuloId
                WHERE LOT_FechaLote BETWEEN '".$f1."' AND '".$f2."' ORDER BY LOT_FechaLote,LOT_NumeroLote ASC"
            )
        );

        return Response::json($consultaLotesCreados);
    }

    public function ConcultaArticulosFiltro($ART_ARTM_MarcaId,$ART_AFAM_FamiliaId,$ART_ACAT_CategoriaId,$ART_CMM_SubcategoriaId,$ART_ArticuloId){

        $cadena = "";

        if($ART_ARTM_MarcaId != "")
        {

            if($cadena == "")
            {

                $cadena = " WHERE ART_ARTM_MarcaId = '".$ART_ARTM_MarcaId."'";

            }
            else
            {

                $cadena = $cadena." OR ART_ARTM_MarcaId = '".$ART_ARTM_MarcaId."'";

            }

        }
        if($ART_AFAM_FamiliaId != "")
        {

            if($cadena == "")
            {

                $cadena = " WHERE ART_AFAM_FamiliaId = '".$ART_AFAM_FamiliaId."'";

            }
            else
            {

                $cadena = $cadena." OR ART_AFAM_FamiliaId = '".$ART_AFAM_FamiliaId."'";

            }

        }
        if($ART_ACAT_CategoriaId != "")
        {

            if($cadena == "")
            {

                $cadena = " WHERE ART_ACAT_CategoriaId = '".$ART_ACAT_CategoriaId."'";

            }
            else
            {

                $cadena = $cadena." OR ART_ACAT_CategoriaId = '".$ART_ACAT_CategoriaId."'";

            }

        }
        if($ART_CMM_SubcategoriaId != "")
        {

            if($cadena == "")
            {

                $cadena = " WHERE ART_CMM_SubcategoriaId = '".$ART_CMM_SubcategoriaId."'";

            }
            else
            {

                $cadena = $cadena." OR ART_CMM_SubcategoriaId = '".$ART_CMM_SubcategoriaId."'";

            }

        }

        $cadenaFin = "";
        if($ART_ArticuloId != "")
        {

            $articulosIds = "";
            //$arregloArticulos = explode(',',$ART_ArticuloId);
            $cuentaArreglo = count($ART_ArticuloId);

            for($x = 0; $x < $cuentaArreglo; $x ++)
            {

                //dd($arregloArticulos[$x]);
                if($ART_ArticuloId[$x] != "")
                {

                    if($articulosIds == "")
                    {

                        $articulosIds = "'".$ART_ArticuloId[$x]."'";

                    }
                    else
                    {

                        $articulosIds = $articulosIds.",'".$ART_ArticuloId[$x]."'";

                    }

                }

            }
            $cadenaFin = "ART_ArticuloId IN (".$articulosIds.")";

        }

        $cadenaInicio = "SELECT ART_ArticuloId,ART_CodigoArticulo,ART_Nombre,ART_Departamento,ART_Pasta,ART_Presentacion FROM Articulos";
        if($cadena != "")
        {

            $cadena = $cadenaInicio.$cadena;

            if($cadena != "" && $cadenaFin != "")
            {

                $cadena = "SELECT DISTINCT * FROM (".$cadena." OR ".$cadenaFin.") AS Consulta ORDER BY ART_Nombre ASC";

            }

        }
        else
        {

            $cadena = "SELECT ART_ArticuloId,ART_CodigoArticulo,ART_Nombre,ART_Departamento,ART_Pasta,ART_Presentacion FROM Articulos WHERE ".$cadenaFin;

        }


        $result = \DB::select(
            \DB::raw(
                $cadena." AND ART_Eliminado = 0 AND ART_Activo = 1"
            )
        );

        return $result;

    }

    public function ConcultaArticulosFiltro2($ART_ARTM_MarcaId,$ART_AFAM_FamiliaId,$ART_ACAT_CategoriaId,$ART_CMM_SubcategoriaId,$ART_ArticuloId){

        $cadena = "";

        if($ART_ARTM_MarcaId != "null")
        {

            if($cadena == "")
            {

                $cadena = " WHERE ART_ARTM_MarcaId = '".$ART_ARTM_MarcaId."'";

            }
            else
            {

                $cadena = $cadena." OR ART_ARTM_MarcaId = '".$ART_ARTM_MarcaId."'";

            }

        }
        if($ART_AFAM_FamiliaId != "null")
        {

            if($cadena == "")
            {

                $cadena = " WHERE ART_AFAM_FamiliaId = '".$ART_AFAM_FamiliaId."'";

            }
            else
            {

                $cadena = $cadena." OR ART_AFAM_FamiliaId = '".$ART_AFAM_FamiliaId."'";

            }

        }
        if($ART_ACAT_CategoriaId != "null")
        {

            if($cadena == "")
            {

                $cadena = " WHERE ART_ACAT_CategoriaId = '".$ART_ACAT_CategoriaId."'";

            }
            else
            {

                $cadena = $cadena." OR ART_ACAT_CategoriaId = '".$ART_ACAT_CategoriaId."'";

            }

        }
        if($ART_CMM_SubcategoriaId != "null")
        {

            if($cadena == "")
            {

                $cadena = " WHERE ART_CMM_SubcategoriaId = '".$ART_CMM_SubcategoriaId."'";

            }
            else
            {

                $cadena = $cadena." OR ART_CMM_SubcategoriaId = '".$ART_CMM_SubcategoriaId."'";

            }

        }

        $cadenaFin = "";
        if($ART_ArticuloId != "null")
        {

            $articulosIds = "";
            $arregloArticulos = explode(',',$ART_ArticuloId);
            $cuentaArreglo = count($arregloArticulos);

            for($x = 0; $x < $cuentaArreglo; $x ++)
            {

                //dd($arregloArticulos[$x]);
                if($arregloArticulos[$x] != "")
                {

                    if($articulosIds == "")
                    {

                        $articulosIds = "'".$arregloArticulos[$x]."'";

                    }
                    else
                    {

                        $articulosIds = $articulosIds.",'".$arregloArticulos[$x]."'";

                    }

                }

            }
            $cadenaFin = "ART_ArticuloId IN (".$articulosIds.")";

        }

        $cadenaInicio = "SELECT ART_ArticuloId,ART_CodigoArticulo,ART_Nombre,ART_Departamento,ART_Pasta,ART_Presentacion FROM Articulos";
        if($cadena != "")
        {

            $cadena = $cadenaInicio.$cadena;

            if($cadena != "" && $cadenaFin != "")
            {

                $cadena = "SELECT DISTINCT * FROM (".$cadena." OR ".$cadenaFin.") AS Consulta ORDER BY ART_Nombre ASC";

            }

        }
        else
        {

            $cadena = "SELECT ART_ArticuloId,ART_CodigoArticulo,ART_Nombre,ART_Departamento,ART_Pasta,ART_Presentacion FROM Articulos WHERE ".$cadenaFin;

        }

        $result = \DB::select(
            \DB::raw(
                $cadena." AND ART_Eliminado = 0 AND ART_Activo = 1"
            )
        );

        return Response::json($result);

    }

    public function ConsultarArticulosPorMarca($ART_ARTM_MarcaId){

        $result = \DB::select(
            \DB::raw(
                "SELECT ART_ArticuloId,ART_CodigoArticulo,ART_Nombre,ART_Departamento,ART_Pasta,ART_Presentacion
                FROM Articulos WHERE ART_ARTM_MarcaId = '".$ART_ARTM_MarcaId."'"
            )
        );

        return Response::json($result);
    }

    public function ConsultarArticulosPorFamilia($id){

        $result = \DB::select(
            \DB::raw(
                "SELECT ART_ArticuloId,ART_CodigoArticulo,ART_Nombre,ART_Departamento,ART_Pasta,ART_Presentacion
                FROM Articulos WHERE ART_AFAM_FamiliaId = '".$id."'"
            )
        );

        return Response::json($result);

    }

    public function ConsultarArticulosPorCategoria($id){

        $result = \DB::select(
            \DB::raw(
                "SELECT ART_ArticuloId,ART_CodigoArticulo,ART_Nombre,ART_Departamento,ART_Pasta,ART_Presentacion
                FROM Articulos WHERE ART_ACAT_CategoriaId = '".$id."'"
            )
        );

        return Response::json($result);

    }

    public function ConsultarArticulosPorSubCategoria($id){

        $result = \DB::select(
            \DB::raw(
                "SELECT ART_ArticuloId,ART_CodigoArticulo,ART_Nombre,ART_Departamento,ART_Pasta,ART_Presentacion
                FROM Articulos WHERE ART_CMM_SubcategoriaId = '".$id."'"
            )
        );

        return Response::json($result);

    }

    public function ConsultarArticulosPorSubArticulo($id){

        $result = \DB::select(
            \DB::raw(
                "SELECT ART_ArticuloId,ART_CodigoArticulo,ART_Nombre,ART_Departamento,ART_Pasta,ART_Presentacion
                FROM Articulos WHERE ART_ArticuloId = '".$id."'"
            )
        );

        return Response::json($result);

    }

    public function consultarFechaYNumeroInicio(){

        $result = \DB::select(
            \DB::raw(
                "SELECT CMM_Valor,CMM_Referencia FROM ControlesMaestrosMultiples WHERE CMM_Control = 'CMM_NumeracionLote'"
            )
        );

        return Response::json($result);

    }

    public function consultarFechasLaborales($fechaInicio,$fechaFin){

        $result = \DB::select(
            \DB::raw(
                "SELECT THEDATE, DATEPART(DW,thedate) as NUMERO_DIA
                FROM dbo.ExplodeDates('".$fechaInicio."','".$fechaFin."') as d
                WHERE thedate not in (
                    SELECT DNL_Fecha
                    FROM DiasNoLaborales
                    WHERE DNL_Eliminado = 0
                )"
            )
        );

        return Response::json($result);

    }

    public function buscarFechaSeleccionada($fechaSeleccionada){

        $result = \DB::select(
            \DB::raw(
                "SELECT * FROM DiasNoLaborales WHERE DNL_Fecha = '".$fechaSeleccionada."'"
            )
        );

        return Response::json($result);

    }

    public static function buscaLotePorCodigoLote($CodigoLote){
        $sub = \DB::table('Lotes')
            ->where('LOT_CodigoLote', '=', $CodigoLote)
            ->get();

        return $sub;
    }

    public static function buscaLotePorId($loteId){
        $lote = \DB::table('Lotes')
            ->where('LOT_LoteId', '=', $loteId)
            ->get();

        return $lote[0];
    }

    public static function getIdLotePorCodigo($codigoLote){

        $lote = \DB::table('Lotes')
            ->where('LOT_CodigoLote', '=', $codigoLote)
            ->get();

        return count($lote) > 0 ? $lote[0]->LOT_LoteId : null;

    }

    public function consultarArticulosConPreLoteDelDiaSeleccionado(){

        $ART_ARTM_MarcaId = NewRequest::input('Marca');
        $ART_AFAM_FamiliaId = NewRequest::input('Familia');
        $ART_ACAT_CategoriaId = NewRequest::input('Categoria');
        $ART_CMM_SubcategoriaId = NewRequest::input('Subcategoria');
        $ART_ArticuloId = NewRequest::input('Articulo');
        $fechaSeleccionada = NewRequest::input('FechaSeleccionada');
        $fechaInicio = NewRequest::input('FechaInicioCalendario');
        $numeroInicio = NewRequest::input('NumeroInicioCalendario');

        $mensaje = "Valido";
        $dia = "";
        $consultaArticulos = "";
        $buscaFiltrado = "";

        if($fechaInicio != "" && $numeroInicio != "")
        {

            //SACAR NUMERACION DEL DIA SELECCIONADO
            $diasLaborales = \DB::select(
                \DB::raw(
                    "SELECT THEDATE, DATEPART(DW,thedate) as NUMERO_DIA, DNL_Eliminado
                    FROM dbo.ExplodeDates('".$fechaInicio."','".$fechaSeleccionada."') as d
                    LEFT JOIN DiasNoLaborales ON DNL_Fecha = THEDATE
                    WHERE thedate not in (
                        SELECT DNL_Fecha
                        FROM DiasNoLaborales
                        WHERE DNL_Eliminado = 0
                    )"
                )
            );

            $cuentaDiasLaborales = count($diasLaborales);
            if($cuentaDiasLaborales > 0)
            {

                $contadorNumero = $numeroInicio;
                $valorFinalContador = "";

                for($x = 0; $x < $cuentaDiasLaborales; $x ++)
                {

                    $diaDomingo = $diasLaborales[$x]->NUMERO_DIA;
                    $diaEliminado = $diasLaborales[$x]->DNL_Eliminado;

                    $datetime1 = strtotime($fechaSeleccionada);
                    $datetime2 = strtotime($diasLaborales[$x]->THEDATE);

                    if($datetime1 == $datetime2)
                    {

                        $valorFinalContador = $contadorNumero;

                    }
                    if($contadorNumero >= 999)
                    {

                        $contadorNumero = 1;

                    }
                    else
                    {

                        if($diaDomingo != 7)
                        {

                            $contadorNumero++;

                        }
                        elseif($diaDomingo == 7 && $diaEliminado == 1)
                        {

                            $contadorNumero++;

                        }

                    }

                }

                $dia = $valorFinalContador;
                if($dia != "")
                {

                    if((int)$dia < 10)$dia = "00" . $dia;
                    if((int)$dia > 9 && (int)$dia < 100)$dia = "0" . $dia;

                    //CONSULTA ARTICULOS YA REGISTRADOS
                    $consultaArticulos = \DB::select(
                        \DB::raw(
                            "select LOTP_ART_ArticuloId from LotesPreliminares where LOTP_FechaLotePreliminar = '".$fechaSeleccionada."' AND LOTP_Eliminado = 0"
                        )
                    );

                    //CONSULTA FILTRADO DE BUSQUEDA
                    $buscaFiltrado = LotesController::ConcultaArticulosFiltro($ART_ARTM_MarcaId,$ART_AFAM_FamiliaId,$ART_ACAT_CategoriaId,$ART_CMM_SubcategoriaId,$ART_ArticuloId);
                    $cuentaBuscaFiltrado = count($buscaFiltrado);
                    if($cuentaBuscaFiltrado < 1)
                    {

                        $mensaje = "No existen coincidencias del filtrado de búsqueda.";

                    }

                }

            }

        }
        else
        {

            if($fechaInicio == "" && $numeroInicio != "")
            {

                $mensaje = "No existe fecha de Inicio en el calendario.";

            }
            elseif($numeroInicio == "" && $fechaInicio != "")
            {

                $mensaje = "No existe numero de inicio en el calendario.";

            }
            else
            {

                $mensaje = "No existe fecha y numero de inicio en el calendario.";

            }

        }


        return ['Mensaje' => $mensaje, 'Dia'=>$dia, 'ConsultaArticulos'=>$consultaArticulos, 'BuscaFiltrado'=>$buscaFiltrado];

    }

}
