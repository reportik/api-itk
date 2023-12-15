<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenesTrabajoAsignacionRecursosEmpleados extends Model {

    protected $table = 'OrdenesTrabajoAsignacionRecursosEmpleados';

    protected $primaryKey = 'OTARE_AsignacionId';

    public $timestamps = false;

    public $incrementing = false;

}
