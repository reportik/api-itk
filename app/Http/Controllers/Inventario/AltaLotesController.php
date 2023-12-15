<?php namespace App\Http\Controllers\Inventario;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
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
use Illuminate\Support\Facades\Request as NewRequest;

class AltaLotesController extends Controller {

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
        /*$articulos=array(''=>'Seleccione Articulos') + Articulos::select('ART_ArticuloId', \DB::raw("ART_CodigoArticulo + ' - ' + ART_Nombre AS FULL_NAME"))
                ->whereRaw("ART_Eliminado = 0 AND ART_Activo = 1")
                ->orderBy('ART_Nombre','ASC')
                ->lists('FULL_NAME', 'ART_ArticuloId')->all();*/
        $articulos=array();
        /*$encabezados =array(
            'Id',
            'Número de Lote',
            'Código de Lote',
            'Articulo',
            'Fecha Creación',
            'Fecha Caducidad',
            'Estatus'
        );

        $contenidos=array(
            'LOT_LoteId',
            'LOT_NumeroLote',
            'LOT_CodigoLote',
            'ART_Nombre',
            'LOT_FechaLote',
            'LOT_FechaCaducidad',
            'CMM_Valor'
        );

        $results=\DB::table('Lotes')
            ->join('Articulos','LOT_ART_ArticuloId','=','ART_ArticuloId')
            ->join('ControlesMaestrosMultiples', 'LOT_CMM_EstatusLoteId', '=', 'CMM_ControlId')
            ->where('LOT_Eliminado','=',0)
            ->whereRaw("LOT_FechaLote >= convert(char(6), dateadd(month, -1, getdate()), 112) + '01'")
            ->orderBy('LOT_FechaLote','DESC')
            ->select($contenidos)
            ->get();*/

        //date_default_timezone_set('America/Mexico_City');
        $fecha=date('d/m/Y');

        return view('Inventario.AltaLotes.create', compact('familias', 'categorias','marcas', 'subcategorias','articulos','fecha'));
	}

    public function buscaLotesPorFecha(){

        $FechaInicio = NewRequest::input('fechaDesde');
        $FechaFinal = NewRequest::input('fechaHasta');

        $consultaLotes = \DB::select(
            \DB::raw(
                "SELECT
                    LOT_LoteId AS DT_RowId,
                    LOT_NumeroLote,
                    ART_Nombre,
                    LOT_CodigoLote,
                    CAST(LOT_FechaLote AS DATE) AS LOT_FechaLote,
                    CAST(LOT_FechaCaducidad AS DATE) AS LOT_FechaCaducidad,
                    CMM_Valor
                FROM Lotes
                INNER JOIN Articulos ON LOT_ART_ArticuloId = ART_ArticuloId
                INNER JOIN ControlesMaestrosMultiples ON CMM_ControlId = LOT_CMM_EstatusLoteId
                WHERE LOT_Eliminado = 0
                AND CAST(LOT_FechaLote AS DATE) BETWEEN '".$FechaInicio."' AND '".$FechaFinal."'
                ORDER BY LOT_FechaLote DESC"
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

            //date_default_timezone_set('America/Mexico_City');
            $hoy=date('d/m/Y');

            $totalArticulosSeleccionados = $_POST['CCON_contador'];

            for($x = 1; $x <= $totalArticulosSeleccionados; $x ++)
                //for($x = 1; $x <= 100000000; $x ++)
            {

                $numeroLote = substr($_POST['LOT_CodigoLote'.$x], 0, 3);

                \DB::table('Lotes')->insert(

                    array(

                        'LOT_NumeroLote' => $numeroLote,
                        'LOT_ART_ArticuloId' => $_POST['ART_ArticuloId'.$x],
                        'LOT_CodigoLote' => $_POST['LOT_CodigoLote'.$x],
                        'LOT_CantidadOriginal' => 0,
                        'LOT_FechaLote' => $hoy,
                        'LOT_CMM_EstatusLoteId' => ControlesMaestrosMultiples::$CMM_EstatusLote_Abierto,
                        'LOT_LoteManual' => 1

                    )

                );

            }

            //return json_encode(array());

            $mensaje = 'Los Lotes se registraron con éxito.';

            \DB::commit();

            return ['Status' => 'Valido', 'Mensaje' => $mensaje];

        }
        catch (\Exception $e) {

            \DB::rollback();

            return ['Status' => 'Error', 'Mensaje' => 'No se registraron Los Lotes. Ocurrió un error al realizar el proceso. <br><br><br>Error: <br>'.$e->getMessage()];

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

        return view('Inventario.AltaLotes.editar', compact('res','id','articulos','controles'));

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

            \DB::table('LotesPreliminares')->where('LOTP_LotePreliminarId', '=', $id)
                ->update(
                    array(
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
                $cadena
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

    public function buscarLotes(){

        $arreglo = $_POST['arreglo'];
        $cuentaArreglo = count($arreglo);

        $codigosLotes = "";
        for($x = 0; $x < $cuentaArreglo; $x ++)
        {

            if($codigosLotes == "")
            {

                $codigosLotes = "'".$arreglo[$x][0]."'";

            }
            else
            {

                $codigosLotes = $codigosLotes.",'".$arreglo[$x][0]."'";

            }

        }

        $consultaLotesDeArreglo = \DB::select(\DB::raw("SELECT * FROM Lotes WHERE LOT_CodigoLote IN (".$codigosLotes.")"));
        $cuentaConsulta = count($consultaLotesDeArreglo);

        $ban = false;
        $lotesExistentes = "";
        if($cuentaConsulta > 0)
        {

            for($x = 0; $x < $cuentaConsulta; $x ++)
            {

                if($lotesExistentes == "")
                {

                    $lotesExistentes = $consultaLotesDeArreglo[$x]->LOT_CodigoLote;

                }
                else
                {

                    $lotesExistentes = $lotesExistentes.",".$consultaLotesDeArreglo[$x]->LOT_CodigoLote;

                }


            }
            $ban = true;

        }

        return ['Status' => $ban, 'LotesExistentes' => $lotesExistentes];

    }

}
