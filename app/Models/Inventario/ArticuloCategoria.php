<?php
namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 12/06/2015
 * Time: 04:30 PM
 */

class ArticuloCategoria extends Model{

    protected $table = 'ArticulosCategorias';

    protected $primaryKey = 'ACAT_CategoriaId';

    public $timestamps = false;

    public $incrementing = false;

} 