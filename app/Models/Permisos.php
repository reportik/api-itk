<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permisos extends Model {

    protected $table = 'Permisos';

    protected $primaryKey = 'PER_PermisoId';

    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = [
        'PER_CodigoPermiso',
        'PER_TipoPermiso'

    ];

}
