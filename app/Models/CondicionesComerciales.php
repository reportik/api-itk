<?php namespace App\Models;

/**
 * Created by PhpStorm.
 * User: Wil
 * Date: 11/02/2019
 * Time: 07:12 PM
 */

use Illuminate\Database\Eloquent\Model;

class CondicionesComerciales extends Model{
    protected $table = 'CotizacionCondicionesComerciales';

    protected $primaryKey = 'CCC_CondicionComercialId';

    public $timestamps= false;

    public $incrementing = false;

}