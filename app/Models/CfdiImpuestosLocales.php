<?php
/**
 * User: Jorge
 * Date: 09/12/2020
 * Time: 05:26 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class CfdiImpuestosLocales extends Model{

    protected $table = 'CfdiImpuestosLocales';

    protected $primaryKey = 'CFDIL_CfdiImpuestosLocalesId';

    public $timestamps = false;

    public $incrementing = false;

} 