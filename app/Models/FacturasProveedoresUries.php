<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacturasProveedoresUries extends Model {

    protected $table = 'FacturasProveedoresUries';

    protected $primaryKey = 'FPU_UriesId';

    public $timestamps= false;

    public $incrementing = false;
}
