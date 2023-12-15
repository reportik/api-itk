<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenesTrabajoGastos extends Model {

    protected $table = 'OrdenesTrabajoGastos';

    protected $primaryKey = 'OTG_OrdenTrabajoGastosId';

    public $timestamps = false;

    public $incrementing = false;

}
