<?php namespace App\Models;
/**
 * Created by PhpStorm.
 * User: WIL
 * Date: 06/02/2020
 * Time: 05:10 PM
 */
use Illuminate\Database\Eloquent\Model;
class CotizacionCaracteristicas extends Model{

    protected $table = 'CotizacionCaracteristicas';

    protected $primaryKey = 'CCAR_CaracteristicaId';

    public $timestamps= false;

    public $incrementing = false;

}