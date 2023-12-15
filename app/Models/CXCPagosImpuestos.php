<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CXCPagosImpuestos extends Model {

    protected $table = 'CXCPagosImpuestos';

    protected $primaryKey = 'CXCPI_ImpuestoId';

    public $timestamps = false;

    public $incrementing=false;

}