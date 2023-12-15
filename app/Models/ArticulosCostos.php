<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticulosCostos extends Model {

    protected $table = 'ArticulosCostos';

    protected $primaryKey = 'ARTC_ArticuloCostoId';

    public $timestamps = false;

    public $incrementing = false;

}
