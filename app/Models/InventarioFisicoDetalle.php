<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventarioFisicoDetalle extends Model {

    protected $table = 'InventarioFisicoDetalle';

    protected $primaryKey = 'IFD_InventarioFisicoDetId';

    public $timestamps = false;

    public $incrementing = false;

}
