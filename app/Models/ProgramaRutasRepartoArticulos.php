<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramaRutasRepartoArticulos extends Model {

    protected $table = 'ProgramaRutasRepartoArticulos';

    protected $primaryKey = 'PRPA_ProgramaArticuloId';

    public $timestamps = false;

    public $incrementing = false;

}
