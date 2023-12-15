<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotasCreditoDetalle extends Model {

    protected $table = 'NotasCreditoDetalle';

    protected $primaryKey = 'NC_DetalleId';

    public $timestamps= false;

    public $incrementing = false;

}
