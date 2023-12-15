<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientesVisitasDetalle extends Model {

    protected $table = 'ClientesVisitasDetalle';

    protected $primaryKey = 'CVD_DetalleId';

    public $timestamps= false;

    public $incrementing = false;

}
