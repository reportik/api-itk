<?php
/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 09/09/2015
 * Time: 05:26 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class CajasVenta extends Model{

    protected $table = 'CajasVenta';

    protected $primaryKey = 'CAV_CajaVentaId';

    public $timestamps = false;

    public $incrementing = false;

} 