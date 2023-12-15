<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportesOrdenesServicioDetalle extends Model {

    protected $table = 'TransportesOrdenesServicioDetalle';

    protected $primaryKey = 'TOSD_DetalleId';

    public $timestamps= false;

    public $incrementing = false;

    public static function buscaPorId($id) {
        $result = \DB::table('TransportesOrdenesServicioDetalle')
            ->where('TOSD_TOS_OrdenServicioId', '=', $id)
            ->where('TOSD_Eliminado', '=', '0')
            ->orderby('TOSD_CMM_TRA_TipoPartidaOS')->orderby('TOSD_NumeroLinea')->get();

        if(empty($result)){
            $result = array();
        }

        return $result;
    }
}
