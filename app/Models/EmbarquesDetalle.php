<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmbarquesDetalle extends Model {

    protected $table = 'EmbarquesDetalle';

    protected $primaryKey = 'EMBD_EmbarqueDetalleId';

    public $timestamps = false;

    public $incrementing = false;

    /**
     * Permite obtener un arreglo de la tabla EmbarquesDetalle
     * a través del identificador del embarque.
     *
     * @author Juan Gómez Gálvez
     * @param string $embarqueId identificador de la factura
     */
    public static function buscaPorEmbarqueId($embarqueId) {
        $result = \DB::table('EmbarquesDetalle')->
        where('EMBD_EMB_EmbarqueId', '=', $embarqueId)->get();

        return $result;
    }

    /**
     * Permite actualizar el movimiento id
     * a través del identificador de la tabla.
     *
     * @author Juan Gómez Gálvez
     * @param string $detalleId identificador del detalle
     * @param string $movtoId identificado del movimiento de inventario
     */
    public static function actualizaMovtoPorDetalleId($detalleId, $movtoId) {
        \DB::statement("
        UPDATE EmbarquesDetalle
        SET EMBD_TRAM_TraspasoMovtoId = '$movtoId'
        WHERE EMBD_EmbarqueDetalleId = '$detalleId'");
    }
}
