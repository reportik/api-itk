<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpleadosIncidencias extends Model {

    protected $table = 'EmpleadosIncidencias';

    protected $primaryKey = 'EIN_IncidenciaId';

    public $timestamps = false;

    public $incrementing = false;

}
