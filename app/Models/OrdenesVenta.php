<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenesVenta extends Model {

    protected $table = 'OrdenesVenta';

    protected $primaryKey = 'OV_OrdenVentaId';

    public $timestamps = false;

    public $incrementing = false;

}
