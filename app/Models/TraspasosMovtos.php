<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 07/05/2015
 * Time: 11:58 AM
 */

class TraspasosMovtos extends Model {

    protected $table = 'TraspasosMovtos';

    protected $primaryKey = 'TRAM_TraspasoMovtoId';

    public $timestamps = false;

    public $incrementing=false;
} 