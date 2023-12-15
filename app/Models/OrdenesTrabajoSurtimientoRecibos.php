<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenesTrabajoSurtimientoRecibos extends Model {

    protected $table = 'OrdenesTrabajoSurtimientoRecibos';

    protected $primaryKey = 'OTSR_ReciboId';

    public $timestamps = false;

    public $incrementing = false;

}
