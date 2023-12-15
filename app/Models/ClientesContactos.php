<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientesContactos extends Model {

    protected $table = 'ClientesContactos';

    protected $primaryKey = 'CCON_ContactoId';

    public $timestamps = false;

    public $incrementing = false;

}
