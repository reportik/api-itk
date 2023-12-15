<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportesOrdenesServicio extends Model {

    protected $table = 'TransportesOrdenesServicio';

    protected $primaryKey = 'TOS_OrdenServicioId';

    public $timestamps= false;

    public $incrementing = false;

    protected $fillable = [

        'TOS_Codigo',
        'TOS_PRO_ProveedorId',
        'TOS_FechaOrdenServicio',
        'TOS_FechaCreacion'
    ];

}
