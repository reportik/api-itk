<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstructurasArticulos extends Model {

    protected $table = 'EstructurasArticulos';

    public $timestamps = false;

    protected $primaryKey = 'EAR_EstructuraId';

    public $incrementing=false;

}
