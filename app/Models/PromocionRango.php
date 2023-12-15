<?php
/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 30/06/2015
 * Time: 04:33 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class PromocionRango extends Model {

    protected $table = 'PromocionesRangos';

    protected $primaryKey = 'PROMR_PromocionRangoId';

    public $timestamps = false;

    public $incrementing = false;

} 