<?php
/**
 * Created by PhpStorm.
 * User: WIL
 * Date: 13/08/2019
 * Time: 04:07 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class CaracteristicasCalidad extends Model
{
    protected $table = 'CotizacionCaracteristicas';

    protected $primaryKey = 'CCAR_CaracteristicaId';

    public $timestamps= false;

    public $incrementing = false;
}