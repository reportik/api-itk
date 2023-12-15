<?php namespace App\Models;

/**
 * Created by PhpStorm.
 * User: WIL
 * Date: 18/07/2019
 * Time: 06:26 PM
 */

use Illuminate\Database\Eloquent\Model;

class CotizacionesEstructura extends Model{
    protected $table = 'CotizacionesEstructura';

    protected $primaryKey = 'COE_CotizacionEstructuraId';

    public $timestamps= false;

    public $incrementing = false;

    public static function buscaPorCotizacionId($id) {
        $result = \DB::table('CotizacionesEstructura')
            ->where('COE_COT_CotizacionId', '=', $id)
            ->where('COE_Eliminado', '=', '0')->orderby('COE_Orden')->get();

        if(empty($result)){
            $result = array();
        }

        return $result;
    }
}