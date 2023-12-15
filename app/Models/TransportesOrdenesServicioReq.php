<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportesOrdenesServicioReq extends Model {

    protected $table = 'TransportesOrdenesServicioReq';

    protected $primaryKey = 'TOSR_RequeridaId';

    public $timestamps= false;

    public $incrementing = false;

    public static function buscaPorId($id) {
        $result = \DB::table('TransportesOrdenesServicioReq')
            ->where('TOSR_TOSD_DetalleId', '=', $id)->get();

        if(empty($result)){
            $result = array();
        }

        return $result;
    }

}