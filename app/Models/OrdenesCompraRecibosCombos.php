<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenesCompraRecibosCombos extends Model {

    protected $table = 'OrdenesCompraRecibosCombos';

    public $timestamps = false;

    protected $primaryKey = 'ORC_ReciboComboId';

    public $incrementing=false;

}
