<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacturasDatos extends Model {

    protected $table = 'FacturasDatos';

    protected $primaryKey = 'FTD_FacturaDatoId';

    public $timestamps = false;

    public $incrementing = false;

    /**
     * Permite obtener un arreglo de la tabla FacturasDatos
     * a través del identificador de la factura.
     *
     * @author Juan Gómez Gálvez
     * @param string $facturaId identificador de la factura
     */
    public static function buscaPorFacturaId($facturaId) {
        $result = \DB::table('FacturasDatos')->
        where('FTD_FTR_FacturaId', '=', $facturaId)->get();

        return $result;
    }
}
