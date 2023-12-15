<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpleadosClientesHistorial extends Model {

    protected $table = 'EmpleadosClientesHistorial';

    protected $primaryKey = 'EMCH_EmpleadoClienteH';

    public $timestamps = false;

    public $incrementing = false;


}
