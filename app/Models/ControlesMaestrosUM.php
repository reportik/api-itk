<?php
/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 14/05/2015
 * Time: 01:37 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ControlesMaestrosUM extends Model{

    protected $table = 'ControlesMaestrosUM';

    protected $primaryKey = 'CMUM_UnidadMedidaId';

    public $timestamps = false;

    public $incrementing = false;

    public static function buscaNombreRepetido($nombre, $id) {
        if(!empty($id)){
            $result = \DB::table('ControlesMaestrosUM')
                ->where('CMUM_UnidadMedidaId', '<>', $id)
                ->where('CMUM_Nombre', '=', $nombre)->get();
        } else if(empty($id)){
            $result = \DB::table('ControlesMaestrosUM')
                ->where('CMUM_Nombre', '=', $nombre)->get();
        }

        if(empty($result)){
            $result = array();
        }

        return $result;
    }
} 