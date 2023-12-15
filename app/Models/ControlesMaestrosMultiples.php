<?php
/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 14/05/2015
 * Time: 01:37 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ControlesMaestrosMultiples extends Model{

    protected $table = 'ControlesMaestrosMultiples';

    protected $primaryKey = 'CMM_ControlId';

    public $timestamps = false;

    public $incrementing = false;

    public static function buscaPorNombre($control) {
        $result = \DB::table('ControlesMaestrosMultiples')->
        where('CMM_Control', '=', $control)->get();

        if(empty($result)){
            $result = array();
        }

        return $result;
    }

}
