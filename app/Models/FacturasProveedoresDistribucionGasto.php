<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacturasProveedoresDistribucionGasto extends Model {

    protected $table = 'FacturasProveedoresDistribucionGasto';

    protected $primaryKey = 'FPDG_DistribucionGastoId';

    public $timestamps= false;

    public $incrementing = false;

}