<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenesCompraRecibos extends Model {

    protected $table = 'OrdenesCompraRecibos';

    public $timestamps = false;

    protected $primaryKey = 'OCRC_ReciboId';

    public $incrementing=false;

}
