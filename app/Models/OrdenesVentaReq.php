<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class OrdenesVentaReq extends Model
{
    protected $table = 'OrdenesVentaReq';

    protected $primaryKey = 'OVR_RequeridaId';

    public $timestamps = false;

    public $incrementing = false;

}