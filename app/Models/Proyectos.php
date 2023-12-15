<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proyectos extends Model {

    protected $table = 'Proyectos';

    protected $primaryKey = 'PRY_ProyectoId';

    public $timestamps= false;

    public $incrementing = false;

}
