<?php
/**
 * User: Jorge
 * Date: 09/12/2020
 * Time: 05:26 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class CfdiImpuestos extends Model{

    protected $table = 'CfdiImpuestos';

    protected $primaryKey = 'CFDII_CfdiImpuestosId';

    public $timestamps = false;

    public $incrementing = false;

} 