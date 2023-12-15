<?php
namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 12/06/2015
 * Time: 04:30 PM
 */

class ArticuloMarca extends Model{

    protected $table = 'ArticulosMarcas';

    protected $primaryKey = 'ARTM_MarcaId';

    public $timestamps = false;

    public $incrementing = false;

} 