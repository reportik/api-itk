<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedores extends Model {

    protected $table = 'Proveedores';

    protected $primaryKey = 'PRO_ProveedorId';

    public $timestamps= false;

    public $incrementing = false;
}
