<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contactos_Proveedores extends Model {

    protected $table = 'ProveedoresContactos';

    protected $primaryKey = 'PCON_ContactoId';

    public $timestamps= false;

    public $incrementing = false;

}
