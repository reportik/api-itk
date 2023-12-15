<?php
/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 30/06/2015
 * Time: 04:33 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ControlesMaestrosRangosPromociones extends Model {

    protected $table = 'ControlesMaestrosRangosPromociones';

    protected $primaryKey = 'CMRP_RangoPromocionId';

    public $timestamps = false;

    public $incrementing = false;

} 