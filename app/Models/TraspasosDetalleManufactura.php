<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TraspasosDetalleManufactura extends Model {

    protected $table = 'TraspasosDetalleManufactura';

    protected $primaryKey = 'TDM_TraspasoDetalleId';

    public $timestamps = false;

    public $incrementing = false;

}
