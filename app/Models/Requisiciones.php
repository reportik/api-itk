<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Requisiciones extends Model {

    protected $table = 'Requisiciones';

    protected $primaryKey = 'REQ_RequisicionId';

    public $timestamps = false;

    public $incrementing = false;

}
