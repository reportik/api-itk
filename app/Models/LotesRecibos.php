<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LotesRecibos extends Model {

    protected $table = 'LotesRecibos';

    protected $primaryKey = 'LOTR_LoteReciboId';

    public $timestamps = false;

    public $incrementing = false;

}
