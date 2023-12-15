<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticulosTipos extends Model {

    protected $table = 'ArticulosTipos';

    protected $primaryKey = 'ATP_TipoId';

    public $timestamps = false;

    public $incrementing=false;

} 