<?php namespace App\Models\RPT;

use Illuminate\Database\Eloquent\Model;
use DB;
class RPT_CHECADOR_ESTATUS extends Model
{
    protected $table = 'RPT_Checador_ConfigEstatus';
    protected $primaryKey = 'CHE_Id';
    public $timestamps = false;
    protected $fillable = [
        'CHE_Id'
        ,'CHE_Tipo'
        ,'CHE_Estatus'
        ,'CHE_ConGoceSueldo'
        ,'CHE_Observaciones'
        ,'CHE_Activo'
    ];
}
