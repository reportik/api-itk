<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportesVehiculosKilometraje extends Model{

    protected $table = 'TransportesVehiculosKilometraje';

    protected $primaryKey = 'TVK_VehiculoKilometrajeId';

    public $timestamps = false;

    public $incrementing = false;
}