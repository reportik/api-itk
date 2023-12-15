<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenesTrabajoRecibo extends Model {

    protected $table = 'OrdenesTrabajoRecibo';

    protected $primaryKey = 'OTR_OrdenTrabajoReciboId';

    public $timestamps = false;

    public $incrementing = false;

}
