<?php namespace App\Models;
/**
 * Created by PhpStorm.
 * User: WIL
 * Date: 06/02/2020
 * Time: 05:14 PM
 */
use Illuminate\Database\Eloquent\Model;
class CotizacionCondicionesComerciales extends Model {

    protected $table = 'CotizacionCondicionesComerciales';

    protected $primaryKey = 'CCC_CondicionComercialId';

    public $timestamps= false;

    public $incrementing = false;
}