<?php
/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 14/05/2015
 * Time: 01:37 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class CanalesDistribucion extends Model{

    protected $table = 'CanalesDistribucion';

    protected $primaryKey = 'CAD_CanalDistribucionId';

    public $timestamps = false;

    public $incrementing = false;

} 