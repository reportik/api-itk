<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticulosCotizados extends Model
{
    protected $table = 'ArticulosCotizados';

    protected $primaryKey = 'AC_ArticuloCotizadoId';

    public $timestamps = false;

    public $incrementing = false;
}
