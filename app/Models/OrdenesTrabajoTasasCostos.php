<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenesTrabajoTasasCostos extends Model {

    protected $table = 'OrdenesTrabajoTasasCostos';

    protected $primaryKey = 'OTTC_OrdenTrabajoTasaCostoId';

    public $timestamps = false;

    public $incrementing = false;

}
