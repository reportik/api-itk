<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProveedoresDireccionesOC extends Model {

    protected $table = 'ProveedoresDireccionesOC';

    protected $primaryKey = 'PDOC_DireccionOCId';

    public $timestamps= false;

    public $incrementing = false;

    public static function buscaPorProveedorId($id) {
        $result = \DB::table('ProveedoresDireccionesOC')
            ->where('PDOC_Eliminado', '=', 0)
            ->where('PDOC_PRO_ProveedorId', '=', $id)->get();

        if(empty($result)){
            $result = array();
        }

        return $result;
    }

}
