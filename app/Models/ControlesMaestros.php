<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ControlesMaestros extends Model {

    protected $table = 'ControlesMaestros';

    protected $primaryKey = 'CMA_ControlId';

    public $timestamps = false;

    public $incrementing = false;


    public static function buscaPorNombre($control) {
        $result = \DB::table('ControlesMaestros')->
        where('CMA_Control', '=', $control)->get();

        if(!empty($result)){
            $result = $result[0]->CMA_Valor;
        } else {
            $result = '';
        }

        return $result;
    }

}
