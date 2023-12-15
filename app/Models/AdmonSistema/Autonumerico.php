<?php
/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 01/06/2015
 * Time: 07:21 PM
 */

namespace App\Models\AdmonSistema;


use Illuminate\Database\Eloquent\Model;

class Autonumerico extends Model{

    protected $table = 'Autonumericos';

    protected $primaryKey = 'AUT_AutonumericoId';

    public $timestamps = false;

    public $incrementing = false;


} 