<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacturasProveedoresImpuesto extends Model {

    protected $table = 'FacturasProveedoresImpuesto';

    protected $primaryKey = 'FPI_ImpuestoId';

    public $timestamps= false;

    public $incrementing = false;

}