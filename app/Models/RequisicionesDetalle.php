<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequisicionesDetalle extends Model {

    protected $table = 'RequisicionesDetalle';

    protected $primaryKey = 'REQD_PartidaId';

    public $timestamps = false;

    public $incrementing = false;

    public static function buscaPartidasAutorizadasPorRequisicionId($requisicionId) {
        $result = \DB::table('RequisicionesDetalle')
            ->where('REQD_Eliminado', '=', 0)
            ->where('REQD_CMM_EstadoPartidaId', '=', '58ACB0EC-F923-4050-AA4D-8538C0A72343') // Abierta
            ->where('REQD_REQ_RequisicionId', '=', $requisicionId)
            ->get();

        if(empty($result)){
            $result = array();
        }

        return $result;
    }

}
