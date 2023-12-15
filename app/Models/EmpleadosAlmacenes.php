<?php
/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 09/09/2015
 * Time: 05:26 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class EmpleadosAlmacenes extends Model{

    protected $table = 'EmpleadosAlmacenes';

    protected $primaryKey = 'EAL_EmpleadoAlmacenId';

    public $timestamps = false;

    public $incrementing = false;

    public static function buscaPorEmpleadoId($empleadoId) {
        $result = \DB::table('EmpleadosAlmacenes')->select('EAL_ALM_AlmacenId')->
        where('EAL_EMP_EmpleadoId', '=', $empleadoId)->get();

        if(empty($result)){
            $result = array();
        }

        return $result;
    }

}