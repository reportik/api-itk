<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventarioFisico extends Model {

    protected $table = 'InventarioFisico';

    protected $primaryKey = 'IF_InventarioFisicoId';

    public $timestamps = false;

    public $incrementing = false;

}
