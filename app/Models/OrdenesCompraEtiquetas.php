<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenesCompraEtiquetas extends Model {

    protected $table = 'OrdenesCompraEtiquetas';

    public $timestamps = false;

    protected $primaryKey = 'OCE_EtiquetaId';

    public $incrementing=false;

}
