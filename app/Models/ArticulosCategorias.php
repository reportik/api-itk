<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticulosCategorias extends Model {

    protected $table = 'ArticulosCategorias';
    protected $primaryKey = 'ACAT_CategoriaId';
    public $timestamps= false;
    public $incrementing = false;

}
