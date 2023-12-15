<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacturasProveedoresDistribucionGastoDetalle extends Model {

    protected $table = 'FacturasProveedoresDistribucionGastoDetalle';

    protected $primaryKey = 'FPDD_DistribucionGastoDetalleId';

    public $timestamps= false;

    public $incrementing = false;

}