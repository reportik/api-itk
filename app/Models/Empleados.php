<?php
/**
 * Created by PhpStorm.
 * User: Muliix-01
 * Date: 09/09/2015
 * Time: 05:26 PM
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Empleados extends Model{

    protected $table = 'Empleados';

    protected $primaryKey = 'EMP_EmpleadoId';

    public $timestamps = false;

    public $incrementing = false;
} 

