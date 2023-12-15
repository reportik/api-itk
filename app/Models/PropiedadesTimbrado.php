<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropiedadesTimbrado extends Model {

    protected $table = 'PropiedadesTimbrado';

    protected $primaryKey = 'PRT_ControlId';

    public $timestamps = false;

    public $incrementing = false;

    public static function buscaPorControl($control){
        $result = \DB::table('PropiedadesTimbrado')->
        where('PRT_Control', '=', $control)->get();

        if(!empty($result)){
            $result = $result[0]->PRT_Valor;
        } else {
            $result = '';
        }

        return $result;
    }

    public static function buscaPorControlYColumna($control, $columna){
        try {
            $result = \DB::table('PropiedadesTimbrado')->
            where('PRT_Control', '=', $control)->get();

            if(!empty($result)){
                $columna = 'PRT_'.$columna;
                $result = $result[0]->$columna;
            } else {
                $result = '';
            }

            return $result;
        } catch (\Exception $e){
            throw $e;
        }
    }
}
