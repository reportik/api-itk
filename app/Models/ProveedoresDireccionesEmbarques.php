<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProveedoresDireccionesEmbarques extends Model {

    protected $table = 'ProveedoresDireccionesEmbarques';

    protected $primaryKey = 'PDE_DireccionEmbarqueId';

    public $timestamps= false;

    public $incrementing = false;
}
