<?php namespace App\Models\RPT;

use Illuminate\Database\Eloquent\Model;
use DB;
class RPT_CHECADOR_REPORTE extends Model
{
    protected $table = 'RPT_Checador_ReporteEmpleado';
    protected $primaryKey = 'CHR_Id';
    public $timestamps = false;
    protected $fillable = [
        'CHR_Id'
        ,'CHR_Fecha'
        ,'CHR_EMP_Empleado'
        ,'CHR_CHE_Estatus'
        ,'CHR_CHI_Id'
        ,'CHR_HorasTrabajo'
        ,'CHR_HorasExtra'
        ,'CHR_TiempoRetardo'
        ,'CHR_HoraEntrada'
        ,'CHR_HoraSalida'
        
    ];
}
