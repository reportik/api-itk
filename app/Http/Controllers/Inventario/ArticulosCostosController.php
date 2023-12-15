<?php namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Sistema\DataBaseSession;
use App\Http\Requests;
use App\Http\Controllers\Controller;

//use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request;
use App\Inventario\ArticulosCostos;
use App\Models\ControlesMaestros;
use App\Models\Inventario\Articulos\Articulo;
use Response;

class ArticulosCostosController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{

        return view('inventario.articuloscostos.create');

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

        try
        {
            $bandera = false;
            $mensaje = "";

            $idEmpleado = DataBaseSession::getEmpleadoId();

            //SACAR FECHA DEL DÍA
            //date_default_timezone_set('America/Mexico_City');
            $hoy=date('d/m/Y H:i:s');

            //CONTAR REGISTROS DE TABLA
            $consultaregistros = \App\Models\ArticulosCostos::all();
            $cuentaRegistros = count($consultaregistros);

            //RECUPERAR EL ARREGLO CON DATOS
            $arreglo = Request::input('array');
            $cuentaArreglo = count($arreglo);

            //RECUPERAR DATOS DEL FORMULARIO
            $formulario = Request::input('formulario');

            if($cuentaArreglo > 0)
            {

                for($x = 0; $x < $cuentaArreglo; $x ++)
                {

                    $cadena = explode("-", $formulario[1]['value']);
                    $fechaConvertida = $cadena[2]."-".$cadena[1]."-".$cadena[0];
                    $bandera = 0;

                    for($y = 0; $y < $cuentaRegistros; $y ++){

                        if($arreglo[$x][2] == $consultaregistros[$y]->ARTC_ART_ArticuloId){

                            $bandera = 1;
                            break;

                        }

                    }

                    if($bandera == 1){

                        \DB::table('ArticulosCostos')
                            ->where('ARTC_ART_ArticuloId', '=', $arreglo[$x][2])
                            //->whereRaw("MONTH(ARTC_FechaCosto) = '".$cadena[1]."'")
                            //->whereRaw("YEAR(ARTC_FechaCosto) = '".$cadena[2]."'")
                            ->update(
                                array(

                                    'ARTC_FechaCosto' => $fechaConvertida,
                                    'ARTC_PrecioEmbarque' => $arreglo[$x][0],
                                    'ARTC_PrecioUnitario' => $arreglo[$x][1],
                                    'ARTC_FechaUltimaModificacion' => $hoy,
                                    'ARTC_EMP_ModificadoPorId' => $idEmpleado

                                )
                            );

                    }
                    else{

                        $Art_Cotos = new \App\Models\ArticulosCostos();
                        $Art_Cotos->ARTC_ART_ArticuloId = $arreglo[$x][2];
                        $Art_Cotos->ARTC_FechaCosto = $fechaConvertida;
                        $Art_Cotos->ARTC_PrecioEmbarque = $arreglo[$x][0];
                        $Art_Cotos->ARTC_PrecioUnitario = $arreglo[$x][1];
                        $Art_Cotos->ARTC_EMP_ModificadoPorId = $idEmpleado;
                        $Art_Cotos->save();

                    }

                }

            }

            $mensaje = 'El Artíclo Costo se registró con éxito.';

            \DB::commit();

            //$bandera = true;

            //return json_encode(array());
            return ['Status' => 'Valido', 'Mensaje' => $mensaje];

        }
        catch (\Exception $e) {

            \DB::rollback();

            return ['Status' => 'Error', 'Mensaje' => 'No se registró el Artículo Costo. Ocurrió un error al realizar el proceso. <br><br><br>Error: <br>'.$e->getMessage()];

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

    public static function consultarcantidaddecimales(){

        $result = ControlesMaestros::select('CMA_Valor')
            ->where('CMA_Control','=','CMA_INV_DecimalesCantidades')
            ->get();

        return Response::json($result);

    }

    public static function consultarcantidaddecimalesgeneral(){

        $result = ControlesMaestros::select('CMA_Valor')
            ->where('CMA_Control','=','CMA_CSVP_DecimalesCantidades')
            ->get();

        return Response::json($result);

    }

    public static function allArticulos(){

        $result = \DB::select(
            \DB::raw(
                "SELECT ART_ArticuloId,ART_CodigoArticulo,ART_Nombre
                 FROM Articulos WHERE ART_Eliminado = 0 AND ART_Activo = 1 ORDER BY ART_Nombre ASC"
            )
        );

        return Response::json($result);

    }

    public static function ConsultarArticulosCostos($fecha1,$fecha2){

        $result = \DB::select(
            \DB::raw(
                "WITH ART (
                    ART_ArticuloId,
                    ART_CodigoArticulo,
                    ART_Nombre,
                    ARTC_PrecioEmbarque,
                    ARTC_PrecioUnitario,
                    ARTC_FechaCosto
                )
                AS(
                    SELECT
                        ART_ArticuloId,
                        ART_CodigoArticulo,
                        ART_Nombre,
                        0 AS ARTC_PrecioEmbarque,
                        0 AS ARTC_PrecioUnitario,
                        NULL AS ARTC_FechaCosto
                    FROM Articulos
                    WHERE ART_Eliminado = 0
                    AND ART_ArticuloId NOT IN(
                                            SELECT
                                                ART_ArticuloId
                                            FROM Articulos
                                            LEFT JOIN ArticulosCostos ON ARTC_ART_ArticuloId = ART_ArticuloId
                                            WHERE ARTC_FechaCosto BETWEEN '".$fecha1."' AND '".$fecha2."'
                                            )
                    UNION ALL
                    SELECT
                        ART_ArticuloId,ART_CodigoArticulo,ART_Nombre,ARTC_PrecioEmbarque,ARTC_PrecioUnitario,ARTC_FechaCosto
                    FROM Articulos
                    LEFT JOIN ArticulosCostos ON ARTC_ART_ArticuloId = ART_ArticuloId
                    WHERE ARTC_FechaCosto BETWEEN '".$fecha1."' AND '".$fecha2."'
                )
                SELECT
                    ART_ArticuloId,
                    ART_CodigoArticulo,
                    ART_Nombre,
                    ARTC_PrecioEmbarque,
                    ARTC_PrecioUnitario,
                    ARTC_FechaCosto
                FROM ART
                ORDER BY
                    ART_Nombre"
            )
        );

        return Response::json($result);

    }

}
