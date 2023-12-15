<?php
/**
 * User: Jorge
 * Date: 09/12/2020
 * Time: 05:26 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class CFDIDetalle extends Model{

    protected $table = 'CfdiDetalle';

    protected $primaryKey = 'CFDID_CfdiDetalleId';

    public $timestamps = false;

    public $incrementing = false;

} 