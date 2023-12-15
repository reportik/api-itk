<?php
/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 20/07/2015
 * Time: 02:31 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class TerminosPago extends Model{

    protected $table = 'TerminosPago';

    protected $primaryKey = 'TEP_TerminoPagoId';

    public $timestamps = false;

    public $incrementing = false;

} 