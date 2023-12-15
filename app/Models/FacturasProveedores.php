<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacturasProveedores extends Model {

    protected $table = 'FacturasProveedores';

    protected $hidden = ['FP_Timestamp'];

    protected $primaryKey = 'FP_FacturaProveedorId';

    public $timestamps= false;

    public $incrementing = false;
}
