<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonedasParidad extends Model {

    protected $table = 'MonedasParidad';

    protected $primaryKey = 'MONP_ParidadMonedaId';

    public $timestamps = false;

    public $incrementing = false;


}
