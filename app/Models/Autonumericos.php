<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Autonumericos extends Model {

    Protected $table = 'Autonumericos';

    Protected $primaryKey = 'AUT_AutonumericoId';

    public $timestamps = false;

    public $incrementing = false;

    public $fillable = ['AUT_Siguiente','AUT_FechaUltimaModificacion'];

    public static function buscaPorReferenciaId($referenciaId) {
        $result = \DB::table('Autonumericos')->
        where('AUT_SUC_SucursalId', '=', $referenciaId)->get();

        if(empty($result)){
            $result = array();
        }

        return $result;
    }

}
