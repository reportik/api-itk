<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaquinasTurnos extends Model {

    protected $table = 'MaquinasTurnos';

    protected $primaryKey = 'MAQT_MaquinasTurnoId';

    public $timestamps = false;

    public $incrementing = false;

}
