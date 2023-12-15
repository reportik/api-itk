<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticulosMarcas extends Model {

    protected $table = 'ArticulosMarcas';
    protected $primaryKey = 'ARTM_MarcaId';
    public $timestamps= false;
    public $incrementing = false;

}
