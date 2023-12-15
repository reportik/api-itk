<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticulosGestionOperativa extends Model {

    protected $table = 'ArticulosGestionOperativa';

    protected $primaryKey = 'AGO_ArticuloGestionId';

    public $timestamps = false;

    public $incrementing = false;

}
