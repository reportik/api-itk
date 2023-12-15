<?php namespace App\Models;
/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 14/05/2015
 * Time: 01:37 PM
 */

use Illuminate\Database\Eloquent\Model;

class Localidades extends Model{

    protected $table = 'Localidades';

    protected $primaryKey = 'LOC_LocalidadId';

    public $timestamps = false;

    public $incrementing = false;

} 