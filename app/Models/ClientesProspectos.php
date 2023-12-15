<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientesProspectos extends Model {

    protected $table = 'ClientesProspectos';

    protected $primaryKey = 'CPRO_ClienteProspectoId';

    public $timestamps = false;

    public $incrementing = false;

}
