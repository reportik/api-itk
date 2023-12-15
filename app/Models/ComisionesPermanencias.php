<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComisionesPermanencias extends Model {

    protected $table = 'ComisionesPermanencias';

    protected $primaryKey = 'CP_EMP_EmpleadoId';

    public $timestamps = false;

    public $incrementing = false;

}
