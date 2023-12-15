<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacturasComercioExterior extends Model {

    protected $table = 'FacturasComercioExterior';

    protected $primaryKey = 'FCE_ComercioExteriorId';

    public $timestamps = false;

    public $incrementing = false;
}