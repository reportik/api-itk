<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clientes extends Model {

    protected $table = 'Clientes';

    protected $primaryKey = 'CLI_ClienteId';

    public $timestamps= false;

    public $incrementing = false;

}
