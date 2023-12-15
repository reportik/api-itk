<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuarios extends Model {

    protected $table = 'Usuarios';

    protected $primaryKey = 'USU_UsuarioId';

    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = [


        'USU_EMP_EmpleadoId',
        'USU_Nombre',
        'USU_Contrasenia',
        'USU_Activo',
        'USU_Timestamp',
        'USU_PER_PermisoId'
    ];
    /**
     * Get the employee associated with the user.
     */
    public function empleado()
    {
        return $this->hasOne('App\Models\Empleados', 'EMP_EmpleadoId', 'USU_EMP_EmpleadoId');
    }
}