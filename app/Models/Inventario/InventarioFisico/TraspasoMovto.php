<?php namespace App\Models\Inventario\InventarioFisico;

use Illuminate\Database\Eloquent\Model;

class TraspasoMovto extends Model {

    protected $table = 'TraspasosMovtos';

    protected $primaryKey = 'TRAM_TraspasoMovtoId';

    public $timestamps = false;

    public $incrementing = false;

}