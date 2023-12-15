<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpleadosClientes extends Model {

    protected $table = 'EmpleadosClientes';

    protected $primaryKey = 'EMC_EmpleadoClienteId';

    public $timestamps = false;

    public $incrementing = false;

}
