<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListasPreciosVentasArticulos extends Model{

    protected $table = 'ListasPreciosVentasArticulos';

    protected $primaryKey = 'LPVA_ListaPrecioVentasArtId';

    public $timestamps = false;

    public $incrementing = false;

} 