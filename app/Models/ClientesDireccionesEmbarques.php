<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientesDireccionesEmbarques extends Model {

    protected $table = 'ClientesDireccionesEmbarques';

    protected $primaryKey = 'CDE_DireccionEmbarqueId';

    public $timestamps = false;

    public $incrementing = false;
}
