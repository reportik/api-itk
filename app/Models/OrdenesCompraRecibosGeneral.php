<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenesCompraRecibosGeneral extends Model {

    protected $table = 'OrdenesCompraRecibosGeneral';

    public $timestamps = false;

    protected $primaryKey = 'ORCG_ReciboGeneralId';

    public $incrementing=false;

}
