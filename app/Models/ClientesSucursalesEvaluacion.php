<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientesSucursalesEvaluacion extends Model {

    protected $table = 'ClientesSucursalesEvaluacion';

    protected $primaryKey = 'CSE_EvaluacionId';

    public $timestamps= false;

    public $incrementing = false;

}
