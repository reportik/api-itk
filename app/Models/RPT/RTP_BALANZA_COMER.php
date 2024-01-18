<?php namespace App\Models\RPT;

use Illuminate\Database\Eloquent\Model;

class RTP_BALANZA_COMER extends Model
{
    protected $table = 'dbo.RPT_BalanzaComprobacionComercializadora';

    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'BC_Ejercicio', 'BC_Cuenta_Id', 'BC_Cuenta_Nombre', 'BC_Saldo_Inicial', 'BC_Saldo_Final', 'BC_Movimiento_01', 'BC_Movimiento_02', 'BC_Movimiento_03', 'BC_Movimiento_04', 'BC_Movimiento_05', 'BC_Movimiento_06', 'BC_Movimiento_07', 'BC_Movimiento_08', 'BC_Movimiento_09', 'BC_Movimiento_10', 'BC_Movimiento_11', 'BC_Movimiento_12', 'BC_Fecha_Actualizado'
    ];
}
