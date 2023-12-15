<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Embarques extends Model {

    protected $table = 'Embarques';

    protected $primaryKey = 'EMB_EmbarqueId';

    public $timestamps = false;

    public $incrementing = false;

    /**
     * Permite obtener el identificador del embarque
     * a través del identificador de la factura.
     *
     * @author Juan Gómez Gálvez
     * @param string $facturaId identificador de la factura
     */
    public static function buscaPorFacturaId($facturaId) {
        $result = \DB::table('Embarques')->
        where('EMB_FTR_FacturaId', '=', $facturaId)->get();

        if(!empty($result)){
            $result = $result[0]->EMB_EmbarqueId;
        } else {
            $result = '';
        }

        return $result;
    }
}
