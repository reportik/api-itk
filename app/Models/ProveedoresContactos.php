<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProveedoresContactos extends Model {

    protected $table = 'ProveedoresContactos';

    protected $primaryKey = 'PCON_ContactoId';

    public $timestamps= false;

    public $incrementing = false;

}
