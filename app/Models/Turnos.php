<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Turnos extends Model {

    protected $table = 'Turnos';

    protected $primaryKey = 'TUR_TurnoId';

    public $timestamps = false;

    public $incrementing = false;

}
