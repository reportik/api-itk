<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComprobacionCajaChicaCuenta extends Model {

    protected $table = 'ComprobacionCajaChicaCuenta';

    protected $primaryKey = 'CCCU_CuentaId';

    public $timestamps = false;

    public $incrementing = false;

}
