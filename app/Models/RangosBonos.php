<?php
/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 09/09/2015
 * Time: 05:26 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class RangosBonos extends Model{

    protected $table = 'RangosBonos';

    protected $primaryKey = 'RAB_RangoBonoId';

    public $timestamps = false;

    public $incrementing = false;

} 