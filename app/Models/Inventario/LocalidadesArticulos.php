<?php namespace App\Models\inventario;

use Illuminate\Database\Eloquent\Model;

/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 07/05/2015
 * Time: 11:58 AM
 */



class LocalidadesArticulos extends Model {

    protected $table = 'LocalidadesArticulo';

    protected $primaryKey = 'LOCA_LocalidadArticuloId';

    public $timestamps = false;

    public $incrementing = false;

} 