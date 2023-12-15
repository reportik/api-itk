<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conciliaciones extends Model {

    protected $table = 'Conciliaciones';

    protected $primaryKey = 'CON_ConciliacionId';

    public $timestamps = false;

    public $incrementing = false;

}
