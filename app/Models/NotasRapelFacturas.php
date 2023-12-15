<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotasRapelFacturas extends Model {

    protected $table = 'NotasRapelFacturas';

    protected $primaryKey = 'NRF_RapelFacturaId';

    public $timestamps = false;

    public $incrementing=false;
} 