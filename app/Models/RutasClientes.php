<?php
/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 14/05/2015
 * Time: 01:37 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class RutasClientes extends Model{

    protected $table = 'RutasClientes';

    protected $primaryKey = 'RC_RutaClienteId';

    public $timestamps = false;

    public $incrementing = false;

}