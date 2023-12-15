<?php
/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 18/05/2015
 * Time: 05:33 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Forecast extends Model{

    protected $table = 'Forecast';

    protected $primaryKey = 'FOR_ForecastId';

    public $timestamps = false;

    public $incrementing = false;

} 