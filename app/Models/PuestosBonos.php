<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 07/05/2015
 * Time: 11:58 AM
 */

class PuestosBonos extends Model {

    protected $table = 'PuestosBonos';

    protected $primaryKey = 'PUB_PuestoBonoId';

    public $timestamps = false;

    public $incrementing=false;

} 