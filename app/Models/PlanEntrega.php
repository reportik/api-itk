<?php
/**
 * Created by PhpStorm.
 * User: WIL
 * Date: 13/08/2019
 * Time: 04:08 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class PlanEntrega extends  Model
{
    protected $table = 'CotizacionPlanEntrega';

    protected $primaryKey = 'CPE_PlanEntregaId';

    public $timestamps= false;

    public $incrementing = false;
}