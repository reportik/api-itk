<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacturasOrdenesServicio extends Model {

    protected $table = 'FacturasOrdenesServicio';

    protected $primaryKey = 'FOS_FacturaId';

    public $timestamps= false;

    public $incrementing = false;

}
