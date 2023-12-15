<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estados extends Model {

    protected $table = 'Estados';

    protected $primaryKey = 'EST_EstadoId';

    public $timestamps= false;

    public $incrementing = false;

}
