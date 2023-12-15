<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProveedoresSucursales extends Model {

    protected $table = 'ProveedoresSucursales';

    protected $primaryKey = 'PSUC_SucursalId';

    public $timestamps= false;

    public $incrementing = false;
}
