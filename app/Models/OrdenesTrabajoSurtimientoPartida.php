<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenesTrabajoSurtimientoPartida extends Model {

    protected $table = 'OrdenesTrabajoSurtimientoPartida';

    protected $primaryKey = 'OTSP_PartidaId';

    public $timestamps = false;

    public $incrementing = false;

}
