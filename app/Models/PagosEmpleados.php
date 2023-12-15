<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagosEmpleados extends Model {

    protected $table = 'PagosEmpleados';

    protected $primaryKey = 'PAE_PagoEmpleadoId';

    public $timestamps = false;

    public $incrementing = false;

}
