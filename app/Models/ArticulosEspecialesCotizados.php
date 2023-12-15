<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticulosEspecialesCotizados extends Model
{
    protected $table = 'ArticulosEspecialesCotizados';

    protected $primaryKey = 'AEC_ArticuloEspecialId';

    public $timestamps = false;

    public $incrementing = false;
}
