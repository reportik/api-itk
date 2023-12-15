<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class GeneralController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */

    public static function  buscaReferenciasUso($idBuscar, $nombreTabla){
        $array=[$idBuscar, $nombreTabla];

         $busReferenUso=GeneralController::ejecutaProcedure($array);

        if($busReferenUso==null){
            return false;
        }
        else{
            return true;
        }

    }
    public static function  ejecutaProcedure($array){
       // \DB::connection()->enableQueryLog();
       // $insert = DB::select( "EXEC dbo.muliix-project ?", array( $array ) );
        $proce= \DB::select("EXEC buscaReferenciasUso ?,?",$array);
        return $proce;

      // $query="EXEC"+$procedimiento;
       // dd(\DB::getQueryLog());
       //return $proce;
       /* if($array!=null){

        }*/


    }







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

}
