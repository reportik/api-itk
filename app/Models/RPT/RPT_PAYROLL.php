<?php

namespace App\Models\RPT;

use Illuminate\Database\Eloquent\Model;

class RPT_PAYROLL extends Model
{
    protected $table = 'RPT_PAYROLLS';
    protected $primaryKey = 'PAY_PayrollId';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'PAY_EmpleadoCodigo',
        'PAY_NombreArchivo',
        'PAY_RegistroPatronal',
        'PAY_Anio',
        'PAY_Semana',
        'PAY_FechaNomina',
        'PAY_Estatus',
        'PAY_Visto',
        'PAY_FechaVisto',
        'PAY_Descargado',
        'PAY_FechaDescarga',
        'PAY_FechaAceptRech',
        'PAY_Observacion',
        'PAY_Folio',
        'PAY_RutaArchivo',
        'PAY_FechaCreacion',
    ];
}
