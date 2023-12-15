<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotasCreditoNoAfectableDetalle extends Model {

    protected $table = 'NotasCreditoNoAfectableDetalle';

    protected $primaryKey = 'NCNAD_DetalleId';

    public $timestamps= false;

    public $incrementing = false;

}
