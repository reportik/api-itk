<?php
/**
 * User: Jorge
 * Date: 09/12/2020
 * Time: 05:26 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class CFDI extends Model{

    protected $table = 'Cfdi';

    protected $primaryKey = 'CFDI_CfdiId';

    public $timestamps = false;

    public $incrementing = false;

} 