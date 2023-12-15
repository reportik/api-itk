<?php namespace App\Models;
/**
 * Created by PhpStorm.
 * User: WIL
 * Date: 06/02/2020
 * Time: 05:12 PM
 */
use Illuminate\Database\Eloquent\Model;
class CotizacionPlanEntrega extends Model{

    protected $table = 'CotizacionPlanEntrega';

    protected $primaryKey = 'CPE_PlanEntregaId';

    public $timestamps= false;

    public $incrementing = false;
}