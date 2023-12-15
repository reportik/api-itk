<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagosFacturasRapel extends Model {

    protected $table = 'PagosFacturasRapel';

    protected $primaryKey = 'PFR_PagoId';

    public $timestamps = false;

    public $incrementing=false;
} 