<?php
/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 15/10/2015
 * Time: 01:08 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ArticulosParametrosCalidad extends Model{

    protected $table = 'ArticulosParametrosCalidad';

    protected $primaryKey = 'APC_ArticuloParametroCalidadId';

    public $timestamps = false;

    public $incrementing = false;


} 