<?php
/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 15/10/2015
 * Time: 01:08 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ArticulosCodigosAlias extends Model{

    protected $table = 'ArticulosCodigosAlias';

    protected $primaryKey = 'ACA_ArticuloCodigoAliasId';

    public $timestamps = false;

    public $incrementing = false;


} 