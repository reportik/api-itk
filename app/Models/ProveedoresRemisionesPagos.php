<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProveedoresRemisionesPagos extends Model {

    protected $table = 'ProveedoresRemisionesPagos';

    protected $primaryKey = 'PRP_ProveedorRemisionPagoId';

    public $timestamps= false;

    public $incrementing = false;

    public static function buscaPorProveedorId($id) {
        $result = \DB::table('ProveedoresRemisionesPagos')
            ->where('PRP_Eliminado', '=', 0)
            ->where('PRP_PRO_ProveedorId', '=', $id)->get();

        if(empty($result)){
            $result = array();
        }

        return $result;
    }

}
