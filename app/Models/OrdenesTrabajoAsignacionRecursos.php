<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenesTrabajoAsignacionRecursos extends Model {

    protected $table = 'OrdenesTrabajoAsignacionRecursos';

    protected $primaryKey = 'OTAR_AsignacionId';

    public $timestamps = false;

    public $incrementing = false;

}
