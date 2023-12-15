<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TraspasosRecibos extends Model {

    protected $table = 'TraspasosRecibos';

    protected $primaryKey = 'TRAR_TraspasoReciboId';

    public $timestamps = false;

    public $incrementing = false;

}
