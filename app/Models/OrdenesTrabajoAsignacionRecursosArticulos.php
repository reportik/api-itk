<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenesTrabajoAsignacionRecursosArticulos extends Model {

    protected $table = 'OrdenesTrabajoAsignacionRecursosArticulos';

    protected $primaryKey = 'OTARA_AsignacionArticuloId';

    public $timestamps = false;

    public $incrementing = false;

}
