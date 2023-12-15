<?php
/**
 * User: Jorge
 * Date: 08/01/2021
 * Time: 05:26 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class CfdiRelacion extends Model{

    protected $table = 'CfdiRelacion';

    protected $primaryKey = 'CFDR_RelacionId';

    public $timestamps = false;

    public $incrementing = false;

} 