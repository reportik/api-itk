<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientesCriteriosAdmon extends Model {

    protected $table = 'ClientesCriteriosAdmon';

    protected $primaryKey = 'CCA_ClienteCriterioAdmonId';

    public $timestamps = false;

    public $incrementing = false;

    public static function buscaPorClienteId($clienteId){
        $result = \DB::table('ClientesCriteriosAdmon')->
        where('CCA_CLI_ClienteId', '=', $clienteId)->get();

        if(empty($result)){
            $result = '';
        }

        return $result;

    }

    public static function buscaTipoListaPorClienteId($clienteId) {
        $result = \DB::table('ClientesCriteriosAdmon')->
        where('CCA_CLI_ClienteId', '=', $clienteId)->get();

        if(empty($result)){
            $result = '';
        } else {
            $result = $result[0]->CCA_CMM_TipoListaId;
        }

        return $result;
    }


}