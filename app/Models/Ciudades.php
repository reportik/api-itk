<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ciudades extends Model {

    protected $table = 'Ciudades';

    protected $primaryKey = 'CIU_CiudadId';

    public $timestamps= false;

    public $incrementing = false;

}
