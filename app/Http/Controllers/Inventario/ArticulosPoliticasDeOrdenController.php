<?php namespace App\Http\Controllers\Inventario;

use App\Http\Requests;
use App\Http\Controllers\Controller;

//use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request;
use App\Models\ControlesMaestros;
use Response;

class ArticulosPoliticasDeOrdenController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
        return view('Inventario.ArticulosPoliticasDeOrden.create');
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

            $arreglo = Request::input('array');
            $cuentaArreglo = count($arreglo);

            if($cuentaArreglo > 0)
            {

                for($x = 0; $x < $cuentaArreglo; $x ++)
                {

                    \DB::table('Articulos')->where('ART_ArticuloId', '=', $arreglo[$x][2])->update(

                        array(

                            'ART_CantMinimaOrden' => $arreglo[$x][0],
                            'ART_CantMaximaOrden' => $arreglo[$x][1]

                        )

                    );

                }

            }

            $mensaje = 'Las Politicas de Orden se registraron con éxito.';

            \DB::commit();

            return ['Status' => 'Valido', 'Mensaje' => $mensaje];

        }
        catch (\Exception $e) {

            \DB::rollback();

            return ['Status' => 'Error', 'Mensaje' => 'No se registraron las Politicas de Orden. Ocurrió un error al realizar el proceso. <br><br><br>Error: <br>'.$e->getMessage()];

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

        $result = \DB::table('Articulos')->whereRaw("ART_Eliminado = 0 AND ART_Activo = 1")->orderby('ART_Nombre','ASC')->get();

        return Response::json($result);

    }

}
