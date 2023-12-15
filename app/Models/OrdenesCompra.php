<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenesCompra extends Model {

    protected $table = 'OrdenesCompra';

    public $timestamps = false;

    protected $primaryKey = 'OC_OrdenCompraId';

    public $incrementing=false;

}
