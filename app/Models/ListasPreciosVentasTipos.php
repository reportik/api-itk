<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListasPreciosVentasTipos extends Model {

    protected $table = 'ListasPreciosVentasTipos';

    protected $primaryKey = 'LPVT_ListaPrecioVentaTipoId';

    public $timestamps = false;

    public $incrementing = false;

}
