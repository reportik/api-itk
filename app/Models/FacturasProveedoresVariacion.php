<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacturasProveedoresVariacion extends Model {

    protected $table = 'FacturasProveedoresVariacion';

    protected $primaryKey = 'FPV_VariacionId';

    public $timestamps= false;

    public $incrementing = false;

}