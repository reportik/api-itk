<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProveedoresCriteriosAdmon extends Model {

    protected $table = 'ProveedoresCriteriosAdmon';

    protected $primaryKey = 'PCA_ProveedorCriterioAdmonId';

    public $timestamps= false;

    public $incrementing = false;

    public static function buscaPorProveedorId($id) {
        $result = \DB::table('ProveedoresCriteriosAdmon')
            ->where('PCA_PRO_ProveedorId', '=', $id)->get();

        if(empty($result)){
            $result = array();
        }

        return $result;
    }

}
