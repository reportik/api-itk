<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

/**
 * Created by PhpStorm.
 * User: Juan
 * Date: 02/06/2015
 * Time: 01:56 PM
 */

class MantenimientoPrev extends  Model {

    protected $table = 'MantenimientoPrev';
    protected $primaryKey = 'MANP_MantenimientoPrevId';
    public $timestamps = false;
    public $incrementing = false;

}