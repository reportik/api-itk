<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListasPreciosVentas extends Model{

    protected $table = 'ListasPreciosVentas';

    protected $primaryKey = 'LPV_ListaPrecioVentaId';

    public $timestamps = false;

    public $incrementing = false;

} 