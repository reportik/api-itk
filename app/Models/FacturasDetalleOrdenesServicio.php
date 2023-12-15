<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacturasDetalleOrdenesServicio extends Model {

    protected $table = 'FacturasDetalleOrdenesServicio';

    protected $primaryKey = 'FDOS_FacturaDetalleOSId';

    public $timestamps= false;

    public $incrementing = false;

}
