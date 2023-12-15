<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacturasProveedoresDetalle extends Model {

    protected $table = 'FacturasProveedoresDetalle';

    protected $primaryKey = 'FPD_DetalleId';

    public $timestamps= false;

    public $incrementing = false;
}
