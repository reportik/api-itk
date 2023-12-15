<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Maquinas extends Model {

    protected $table = 'Maquinas';

    protected $primaryKey = 'MAQ_MaquinaId';

    public $timestamps = false;

    public $incrementing = false;

}
