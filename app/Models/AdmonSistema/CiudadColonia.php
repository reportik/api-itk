<?php
/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 18/05/2015
 * Time: 06:03 PM
 */

namespace App\Models\AdmonSistema;


use Illuminate\Database\Eloquent\Model;

class CiudadColonia extends Model{

    protected $table = 'CiudadesColonias';

    protected $primaryKey = 'CIUC_ColoniaId';

    public $timestamps = false;

    public $incrementing = false;

} 