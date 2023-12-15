<?php namespace App\Models;

/**
 * Created by PhpStorm.
 * User: WIL
 * Date: 11/07/2019
 * Time: 11:37 AM
 */

use Illuminate\Database\Eloquent\Model;

class CotizacionesDetalles extends Model{
    protected $table = 'CotizacionesDetalles';

    protected $primaryKey = 'COTD_CotizacionDetalleId';

    public $timestamps= false;

    public $incrementing = false;

    public static function buscaPorCotizacionId($id) {
        $result = \DB::table('CotizacionesDetalles')
            ->where('COTD_COT_CotizacionId', '=', $id)
            ->where('COTD_Eliminado', '=', '0')->orderby('COTD_Orden')->get();

        if(empty($result)){
            $result = array();
        }

        return $result;
    }

}