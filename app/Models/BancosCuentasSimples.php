<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BancosCuentasSimples extends Model {

    protected $table = 'BancosCuentasSimples';

    protected $primaryKey = 'BCS_BancoCuentaId';

    public $timestamps = false;

    public $incrementing=false;

    public static function buscaIdPorBancoId($bancoId){
        $result = \DB::select(\DB::raw("
                SELECT BCS_BancoCuentaId
                FROM BancosCuentasSimples
                WHERE BCS_BAN_BancoId = '$bancoId' "));

        if(!empty($result)){
            $result = $result[0]->BCS_BancoCuentaId;
        } else {
            $result = '';
        }

        return $result;
    }

} 