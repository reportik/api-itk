<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DevolucionCompraDetalle extends Model {

    protected $table = 'DevolucionCompraDetalle';

    protected $primaryKey = 'DCOD_DevolucionCompraDetalleId';

    public $timestamps = false;

    public $incrementing = false;

}
