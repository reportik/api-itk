<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenesTrabajoDetalleArticulos extends Model {

    protected $table = 'OrdenesTrabajoDetalleArticulos';

    protected $primaryKey = 'OTDA_OrdenesTrabajoDetalleArticulosId';

    public $timestamps = false;

    public $incrementing = false;

}
