<?php namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Sistema\DataBaseSession;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\LotesLocalidades;
use Response;

class LotesLocalidadesController extends Controller {

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

    public static function buscaPorLoteIdYLocalidadId($LoteId,$LocalidadId)
    {
        $sub = \DB::table('LotesLocalidades')
            ->where('LOTL_LOT_LoteId', '=', $LoteId)
            ->where('LOTL_LOC_LocalidadId', '=', $LocalidadId)
            ->where('LOTL_Eliminado', '=', 0)
            ->get();

        return $sub;
    }

    public static function buscaIdUltimoInsertado(){
        $ultimo = LotesLocalidades::orderby('LOTL_FechaUltimaModificacion', 'DESC')->get();

        return $ultimo;
    }

    public static function restauraPorId($objeto,$CantidadPorAjustar){

        try{

            //date_default_timezone_set('America/Mexico_City');
            $hoy=date('d/m/Y H:i:s');
            $lote=$objeto;
            \DB::table('LotesLocalidades')->where('LOTL_LOT_LoteId', '=', $lote[0]->LOTL_LOT_LoteId)
                ->where('LOTL_LOC_LocalidadId', '=', $lote[0]->LOTL_LOC_LocalidadId)
                ->update(
                    array(
                        'LOTL_Eliminado' => 0,
                        'LOTL_Cantidad' => $CantidadPorAjustar,
                        'LOTL_FechaUltimaModificacion' => $hoy,
                        'LOTL_FechaAsignacion' => $hoy
                        //'LOCA_EMP_ModificadoPor' => '',
                    )
                );

            return buscaPorId($lote->LOTL_LoteLocalidadId);

        }
        catch(\Exception $e){

            throw $e;

        }

    }

    public static function buscaPorId($id){
        $lotelocalidad = LotesLocalidades::find($id)->where('LOTL_Eliminado','=','0');
        return $lotelocalidad;
    }

    public static function actualizaPorId($objeto,$CantidadPorAjustar){

        try{

            //date_default_timezone_set('America/Mexico_City');
            $hoy=date('d/m/Y H:i:s');
            $lotelocalidad=$objeto;
            \DB::table('LotesLocalidades')->where('LOTL_LOT_LoteId', '=', $lotelocalidad[0]->LOTL_LOT_LoteId)
                ->where('LOTL_LOC_LocalidadId', '=', $lotelocalidad[0]->LOTL_LOC_LocalidadId)
                ->update(
                    array(
                        'LOTL_Cantidad' => $CantidadPorAjustar,
                        'LOTL_FechaUltimaModificacion' => $hoy
                        //'LOCA_EMP_ModificadoPor' => '',
                    )
                );

        }
        catch(\Exception $e){

            throw $e;

        }

    }

    public static function actualizaPorIdLoteLocalidad($idLoteLocalidad,$CantidadPorAjustar){
        //date_default_timezone_set('America/Mexico_City');
        $hoy=date('d/m/Y H:i:s');
        \DB::table('LotesLocalidades')->where('LOTL_LoteLocalidadId', '=', $idLoteLocalidad)
            ->update(
                array(
                    'LOTL_Cantidad' => $CantidadPorAjustar,
                    'LOTL_FechaUltimaModificacion' => $hoy,
                    'LOTL_EMP_ModificadoPor' => DataBaseSession::getEmpleadoId()
                )
            );
    }

}
