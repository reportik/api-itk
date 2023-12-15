<?php namespace App\Models\Inventario\InventarioFisico;

use Illuminate\Database\Eloquent\Model;

class InventarioFisico extends Model {

    protected $table = 'InventarioFisico';

    public $timestamps = false;

    protected $primaryKey = 'IF_InventarioFisicoId';

    public $incrementing=false;

    protected $fillable = [
        'IF_FechaInventario',
        'IF_ConEtiquetas',
        'IF_EMP_EmpleadoCreadorId',
        'IF_EMP_ModificadoPorId',
        'IF_Bloqueado'
    ];

}
