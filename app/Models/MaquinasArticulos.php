<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaquinasArticulos extends Model {

    protected $table = 'MaquinasArticulos';

    protected $primaryKey = 'MAR_MaquinaArticuloId';

    public $timestamps = false;

    public $incrementing = false;

}
