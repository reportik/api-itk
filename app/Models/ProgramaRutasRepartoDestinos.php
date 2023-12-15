<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramaRutasRepartoDestinos extends Model {

    protected $table = 'ProgramaRutasRepartoDestinos';

    protected $primaryKey = 'PRPD_ProgramaDestinoId';

    public $timestamps = false;

    public $incrementing = false;

}
