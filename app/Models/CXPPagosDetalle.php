<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CXPPagosDetalle extends Model {

    protected $table = 'CXPPagosDetalle';

    protected $primaryKey = 'CXPPD_DetalleId';

    public $timestamps = false;

    public $incrementing=false;
}
