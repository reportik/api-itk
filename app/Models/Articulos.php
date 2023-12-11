<?php namespace Muliix\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Created by PhpStorm.
 * User: Muliix-01
 * Date: 07/05/2015
 * Time: 11:58 AM
 */

class Articulos extends Model {

    protected $table = 'Articulos';

    protected $primaryKey = 'ART_ArticuloId';

    public $timestamps = false;

    public $incrementing=false;

} 