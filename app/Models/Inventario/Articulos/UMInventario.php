<?php namespace App\Models\Inventario\Articulos;

use Illuminate\Database\Eloquent\Model;

class UMInventario extends Model {

    protected $table = 'ControlesMaestrosUM';

    protected $primaryKey = 'CMUM_UnidadMedidaId';

}