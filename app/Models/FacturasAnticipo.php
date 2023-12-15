<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacturasAnticipo extends Model {

    protected $table = 'FacturasAnticipo';

    protected $primaryKey = 'FTRA_AnticipoId';

    public $timestamps = false;

    public $incrementing = false;

}
