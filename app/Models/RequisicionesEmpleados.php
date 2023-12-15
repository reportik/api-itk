<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequisicionesEmpleados extends Model {

    protected $table = 'RequisicionesEmpleados';

    protected $primaryKey = 'REQE_RequisicionEmpleadoId';

    public $timestamps = false;

    public $incrementing = false;

}
