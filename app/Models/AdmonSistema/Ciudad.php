<?php namespace App\Models\AdmonSistema;
use Illuminate\Database\Eloquent\Model;

/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 18/05/2015
 * Time: 05:51 PM
 */

class Ciudad extends Model{

    protected $table = 'Ciudades';

    protected $primaryKey = 'CIU_CiudadId';

    public $timestamps = false;

    public $incrementing = false;

} 