<?php namespace App\Models;

/**
 * Created by PhpStorm.
 * User: Wil
 * Date: 21/11/2018
 * Time: 06:13 PM
 */
use Illuminate\Database\Eloquent\Model;

class Cotizaciones extends Model{
    protected $table = 'Cotizaciones';

    protected $primaryKey = 'COT_CotizacionId';

    public $timestamps= false;

    public $incrementing = false;

}