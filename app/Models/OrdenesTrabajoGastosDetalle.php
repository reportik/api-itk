<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenesTrabajoGastosDetalle extends Model {

    protected $table = 'OrdenesTrabajoGastosDetalle';

    protected $primaryKey = 'OTGD_OrdenTrabajoGastosDetalleId';

    public $timestamps = false;

    public $incrementing = false;

}
