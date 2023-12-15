<?php namespace App\Http\Controllers\Inventario;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\DiasNoLaborales;
use Response;

class CalendarioPisoController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{

        return view('Inventario.CalendarioPiso.create');

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

    public function getEventos(){

        //date_default_timezone_set('America/Mexico_City');
        $ano=date('Y');

        //$consulta = DiasNoLaborales::select('DNL_DiaNoLaborableId AS id','DNL_Fecha AS start')->get();

        $fechaInicio = $ano."0101";
        $fechaFin = $ano."1231";

        $consulta = \DB::select(
            \DB::raw(
                "SELECT DNL_DiaNoLaborableId AS id, DNL_Fecha AS start, 'Día No Laboral' AS title, 1 AS allDay
                FROM DiasNoLaborales
                WHERE DNL_Eliminado = 0
                UNION ALL
                SELECT NewId(), THEDATE, 'Día No Laboral' AS title, 1 AS allDay FROM(
                    SELECT THEDATE, DATEPART(DW,thedate) as NUMERO_DIA
                    FROM dbo.ExplodeDates('".$fechaInicio."','".$fechaFin."') as d
                    WHERE thedate not in (
                        SELECT DNL_Fecha
                        FROM DiasNoLaborales
                        --WHERE DNL_Eliminado = 0
                    )
                ) AS TEMP
                WHERE NUMERO_DIA = 7"
            )
        );

        $json = $consulta;//->toArray();

        return (json_encode($json));
    }

    public function insertaFecha(){

        \DB::beginTransaction();

        try {

            $fecha = $_POST['start'];
            $id = $_POST['id'];

            $rest = substr($fecha, 4, 11);
            $dia = substr($rest, 4, -5);
            $mes = substr($rest, 0, 3);
            $ano = substr($rest, -4);

            if($mes == "Jan")$mes = "01";
            if($mes == "Feb")$mes = "02";
            if($mes == "Mar")$mes = "03";
            if($mes == "Apr")$mes = "04";
            if($mes == "May")$mes = "05";
            if($mes == "Jun")$mes = "06";
            if($mes == "Jul")$mes = "07";
            if($mes == "Aug")$mes = "08";
            if($mes == "Sep")$mes = "09";
            if($mes == "Oct")$mes = "10";
            if($mes == "Nov")$mes = "11";
            if($mes == "Dec")$mes = "12";

            $registraFecha = $ano."-".$mes."-".$dia;

            $resultconsulta = \DB::select(
                \DB::raw(
                    "SELECT * FROM DiasNoLaborales WHERE DNL_Fecha = '".$registraFecha."'"
                )
            );

            $cuentaResultconsulta = count($resultconsulta);

            if($cuentaResultconsulta > 0)
            {

                \DB::table('DiasNoLaborales')->where('DNL_Fecha', '=', $registraFecha)
                    ->update(
                        array(
                            'DNL_Eliminado' => 0
                        )
                    );

            }
            else
            {

                \DB::table('DiasNoLaborales')->insert(

                    array(

                        'DNL_DiaNoLaborableId' => $id,
                        'DNL_Fecha' => $registraFecha

                    )

                );

            }

            //return (json_decode($registraFecha));

            $mensaje = 'Se registró con éxito.';

            \DB::commit();

            return ['Status' => 'Valido', 'Mensaje' => $mensaje, 'RegistraFecha' => $registraFecha];

        }
        catch (\Exception $e) {

            \DB::rollback();

            return ['Status' => 'Error', 'Mensaje' => 'No se registró. Ocurrió un error al realizar el proceso. <br><br><br>Error: <br>'.$e->getMessage()];

        }

    }

    public function consultaFecha(){

        $fecha = $_POST['start'];

        $rest = substr($fecha, 4, 11);
        $dia = substr($rest, 4, -5);
        $mes = substr($rest, 0, 3);
        $ano = substr($rest, -4);

        if($mes == "Jan")$mes = "01";
        if($mes == "Feb")$mes = "02";
        if($mes == "Mar")$mes = "03";
        if($mes == "Apr")$mes = "04";
        if($mes == "May")$mes = "05";
        if($mes == "Jun")$mes = "06";
        if($mes == "Jul")$mes = "07";
        if($mes == "Aug")$mes = "08";
        if($mes == "Sep")$mes = "09";
        if($mes == "Oct")$mes = "10";
        if($mes == "Nov")$mes = "11";
        if($mes == "Dec")$mes = "12";

        $consultaFecha = $ano."-".$mes."-".$dia;
        $consultaFechaConvertida = strtotime($consultaFecha);

        //date_default_timezone_set('America/Mexico_City');
        $anoo=date('Y');

        $fechaInicio = $anoo."0101";
        $fechaFin = $anoo."1231";

        $resultconsulta = \DB::select(
            \DB::raw(
                "SELECT DNL_Fecha AS start, 'Día No Laboral' AS title, 1 AS allDay
                FROM DiasNoLaborales
                WHERE DNL_Eliminado = 0
                UNION ALL
                SELECT THEDATE,'Día No Laboral' AS title, 1 AS allDay FROM(
                    SELECT THEDATE, DATEPART(DW,thedate) as NUMERO_DIA
                    FROM dbo.ExplodeDates('".$fechaInicio."','".$fechaFin."') as d
                    WHERE thedate not in (
                        SELECT DNL_Fecha
                        FROM DiasNoLaborales
                        --WHERE DNL_Eliminado = 0
                    )
                ) AS TEMP
                WHERE NUMERO_DIA = 7
                ORDER BY DNL_Fecha ASC"
                //"SELECT * FROM DiasNoLaborales WHERE DNL_Fecha = '".$consultaFecha."'"
            )
        );

        $ban = 0;
        $cuentaResult = count($resultconsulta);

        if($cuentaResult > 0)
        {

            for($x = 0; $x < $cuentaResult; $x ++)
            {

                $rest = substr($resultconsulta[$x]->start, 0, 10);
                $fechaRestConvertida = strtotime($rest);
                if($fechaRestConvertida == $consultaFechaConvertida)
                {

                    //dd($fechaRestConvertida." -- ".$consultaFechaConvertida);
                    $ban = 1;
                    break;

                }

            }

        }

        return (json_encode($ban));

    }

    public function eliminaFecha(){

        \DB::beginTransaction();

        try {

            $fecha = $_POST['start'];

            $rest = substr($fecha, 4, 11);
            $dia = substr($rest, 4, -5);
            $mes = substr($rest, 0, 3);
            $ano = substr($rest, -4);

            if($mes == "Jan")$mes = "01";
            if($mes == "Feb")$mes = "02";
            if($mes == "Mar")$mes = "03";
            if($mes == "Apr")$mes = "04";
            if($mes == "May")$mes = "05";
            if($mes == "Jun")$mes = "06";
            if($mes == "Jul")$mes = "07";
            if($mes == "Aug")$mes = "08";
            if($mes == "Sep")$mes = "09";
            if($mes == "Oct")$mes = "10";
            if($mes == "Nov")$mes = "11";
            if($mes == "Dec")$mes = "12";

            $eliminaFecha = $ano."-".$mes."-".$dia;

            $resultconsulta = \DB::select(
                \DB::raw(
                    "SELECT * FROM DiasNoLaborales WHERE DNL_Fecha = '".$eliminaFecha."'"
                )
            );

            $cuentaResultConsulta = count($resultconsulta);

            if($cuentaResultConsulta > 0)
            {

                \DB::table('DiasNoLaborales')->where('DNL_Fecha', '=', $eliminaFecha)
                    ->update(
                        array(
                            'DNL_Eliminado' => 1
                        )
                    );

            }
            else
            {

                \DB::table('DiasNoLaborales')->insert(

                    array(

                        'DNL_Fecha' => $eliminaFecha,
                        'DNL_Eliminado' => 1

                    )

                );

            }

            //return (json_decode($eliminaFecha));

            $mensaje = 'Se eliminó con éxito.';

            \DB::commit();

            return ['Status' => 'Valido', 'Mensaje' => $mensaje, 'RegistraFecha' => $eliminaFecha];

        }
        catch (\Exception $e) {

        \DB::rollback();

        return ['Status' => 'Error', 'Mensaje' => 'No se eliminó. Ocurrió un error al realizar el proceso. <br><br><br>Error: <br>'.$e->getMessage()];

        }

    }

    public function actualizaFecha(){

        \DB::beginTransaction();

        try {

            $fecha = $_POST['start'];
            $restadias = $_POST['restadias'];

            $rest = substr($fecha, 4, 11);
            $dia = substr($rest, 4, -5);
            $mes = substr($rest, 0, 3);
            $ano = substr($rest, -4);

            if($mes == "Jan")$mes = "01";
            if($mes == "Feb")$mes = "02";
            if($mes == "Mar")$mes = "03";
            if($mes == "Apr")$mes = "04";
            if($mes == "May")$mes = "05";
            if($mes == "Jun")$mes = "06";
            if($mes == "Jul")$mes = "07";
            if($mes == "Aug")$mes = "08";
            if($mes == "Sep")$mes = "09";
            if($mes == "Oct")$mes = "10";
            if($mes == "Nov")$mes = "11";
            if($mes == "Dec")$mes = "12";

            $fechaNueva = $ano."-".$mes."-".$dia;

            if($restadias < 0)
            {

                $restadias = $restadias * -1;
                $eliminaFecha = strtotime ( '+'.$restadias.' day' , strtotime ( $fechaNueva ) ) ;
                $eliminaFecha = date ( 'Y-m-d' , $eliminaFecha );

            }
            elseif($restadias > 0)
            {

                $restadias = $restadias * -1;
                $eliminaFecha = strtotime ( '+'.$restadias.' day' , strtotime ( $fechaNueva ) ) ;
                $eliminaFecha = date ( 'Y-m-d' , $eliminaFecha );

            }

            //BUSCA QUENO HAYA FECHA EN ESE DIA
            $resultconsulta = \DB::select(
                \DB::raw(
                    "SELECT * FROM DiasNoLaborales WHERE DNL_Fecha = '".$fechaNueva."'"
                )
            );

            $cuentaResultconsulta = count($resultconsulta);

            if($cuentaResultconsulta < 1)
            {

                //INSERTA NUEVA FECHA
                \DB::table('DiasNoLaborales')->insert(

                    array(

                        'DNL_Fecha' => $fechaNueva,

                    )

                );

                //ACTUALIZA ESTATUS DE FECHA ANTERIOR COMO ELIMINADO
                \DB::table('DiasNoLaborales')->where('DNL_Fecha', '=', $eliminaFecha)
                    ->update(
                        array(
                            'DNL_Eliminado' => 1
                        )
                    );

                $mensaje = "SI";

            }
            else
            {

                if($resultconsulta[0]->DNL_Eliminado == 1)
                {

                    //ACTUALIZA ESTATUS DE FECHA ANTERIOR COMO NO ELIMINADO
                    \DB::table('DiasNoLaborales')->where('DNL_Fecha', '=', $fechaNueva)
                        ->update(
                            array(
                                'DNL_Eliminado' => 0
                            )
                        );

                    //ACTUALIZA ESTATUS DE FECHA ANTERIOR COMO ELIMINADO
                    \DB::table('DiasNoLaborales')->where('DNL_Fecha', '=', $eliminaFecha)
                        ->update(
                            array(
                                'DNL_Eliminado' => 1
                            )
                        );

                    $mensaje = "SI";

                }
                else
                {

                    $mensaje = 'NO';

                }

            }

            //return (json_decode($mensaje));

            $mensaje = 'Se Actualizó con éxito.';

            \DB::commit();

            return ['Status' => 'Valido', 'Mensaje' => $mensaje, 'RegistraFecha' => $eliminaFecha];

        }
        catch (\Exception $e) {

            \DB::rollback();

            return ['Status' => 'Error', 'Mensaje' => 'No se Actualizó. Ocurrió un error al realizar el proceso. <br><br><br>Error: <br>'.$e->getMessage()];

        }

    }

}
