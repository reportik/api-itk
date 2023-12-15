<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehiculosBitacora extends Model {

    protected $table = 'VehiculosBitacora';

    protected $primaryKey = 'VEB_VehiculoBitacoraId';

    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = [

        'VEB_TUN_TransporteUnidadId',
        'VEB_EMP_EmpleadoId',
        'VEB_CMM_NivelCombustibleId',
        'VEB_CMM_VehiculoDestinoId',
        'VEB_KmSalida',
        'VEB_FechaSalida',
        'VEB_KmEntrada',
        'VEB_FechaEntrada',
        'VEB_KmRecorrido',
        'VEB_ComentarioSalida',
        'VEB_ComentarioEntrada',
        'VEB_FechaUltimaModificacion'

    ];

}
