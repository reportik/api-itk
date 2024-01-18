<?php namespace App\Models\RPT;

use Illuminate\Database\Eloquent\Model;
use DB;
class RPT_CHECADOR_INCIDENCIA extends Model
{
    protected $table = 'RPT_Checador_Incidencias';
    protected $primaryKey = 'CHI_Id';
    public $timestamps = false;
    protected $fillable = [
        'CHI_Id'
        ,'CHI_EMP_Empleado'
        ,'CHI_CHE_Id'
        ,'CHI_FechaInicio'
        ,'CHI_FechaTermino'
        ,'CHI_Descripcion'
        ,'CHI_EstatusPermiso'
        ,'CHI_CreadoPor'
        , 'CHI_FechaCreacion'
        , 'CHI_Eliminado'
        
    ];
}
