<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class OrdenesCompraDetalle extends Model{

    protected $table = 'OrdenesCompraDetalle';
    
    protected $primaryKey = 'OCD_PartidaId';

    public $timestamps = false;

    public $incrementing = false;

} 