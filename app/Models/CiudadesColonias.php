<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 07/05/2015
 * Time: 11:58 AM
 */

class CiudadesColonias extends Model {

    protected $table = 'CiudadesColonias';

    protected $primaryKey = 'CIUC_ColoniaId';

    public $timestamps = false;

    public $incrementing=false;

} 