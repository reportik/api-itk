<?php
/**

 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class RPTEmpleados extends Model{

    protected $table = 'RPT_EmpleadoCamposAdicionales';

    protected $primaryKey = 'ECA_EmpleadoId';

    public $timestamps = false;

    public $incrementing = false;

} 