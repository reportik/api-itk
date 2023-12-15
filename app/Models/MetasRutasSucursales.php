<?php
/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 14/05/2015
 * Time: 01:37 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class MetasRutasSucursales extends Model{

    protected $table = 'MetasRutasSucursales';

    protected $primaryKey = 'MRS_MetaRutaId';

    public $timestamps = false;

    public $incrementing = false;

} 