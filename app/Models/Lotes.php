<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lotes extends Model {

    protected $table = 'Lotes';

    protected $primaryKey = 'LOT_LoteId';

    public $timestamps = false;

    public $incrementing = false;

}
