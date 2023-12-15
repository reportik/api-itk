<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotasDebitoDetalle extends Model {

    protected $table = 'NotasDebitoDetalle';

    protected $primaryKey = 'NDD_DetalleId';

    public $timestamps= false;

    public $incrementing = false;
}
