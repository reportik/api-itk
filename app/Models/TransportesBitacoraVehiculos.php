<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportesBitacoraVehiculos extends Model {

    protected $table = 'TransportesBitacoraVehiculos';

    protected $primaryKey = 'TBV_BitacoraVehiculoId';

    public $timestamps= false;

    public $incrementing = false;
}