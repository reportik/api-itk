<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProveedoresBancos extends Model {

    protected $table = 'ProveedoresBancos';

    protected $primaryKey = 'PBAN_ProveedorBancoId';

    public $timestamps= false;

    public $incrementing = false;

    public static function buscaBancoPorProveedorId($id) {
        $result = \DB::table('ProveedoresBancos')
            ->where('PBAN_Eliminado', '=', 0)
            ->where('PBAN_PRO_ProveedorId', '=', $id)->get();

        if(empty($result)){
            $result = array();
        }

        return $result;
    }
}
