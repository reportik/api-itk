<?php namespace App\Models;

/**
 * User: WIL
 * Date: 12/11/2021
 * Time: 02:20 PM
 */

use Illuminate\Database\Eloquent\Model;

class CotizacionesEstadisticas extends Model{
    protected $table = 'CotizacionesEstadisticas';

    protected $primaryKey = 'COTE_CotizacionEstadisticaId';

    public $timestamps= false;

    public $incrementing = false;

}