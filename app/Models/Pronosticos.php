<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pronosticos extends Model {

    protected $table = 'Pronosticos';

    protected $primaryKey = 'PR_PronosticoId';

    public $timestamps= false;

    public $incrementing = false;

}
