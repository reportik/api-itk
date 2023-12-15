<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenesCompraEtiquetasDetalle extends Model {

    protected $table = 'OrdenesCompraEtiquetasDetalle';

    public $timestamps = false;

    protected $primaryKey = 'OCED_EtiquetaDetalledId';

    public $incrementing=false;

}
