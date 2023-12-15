<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportesDisparadoresVehiculos extends Model{

    protected $table = 'TransportesDisparadoresVehiculos';

    protected $primaryKey = 'TDV_TipoDisparadorId';

    public $timestamps = false;

    public $incrementing = false;
}