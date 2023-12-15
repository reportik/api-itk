<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class OrdenesCompraPedimentos extends Model{

    protected $table = 'OrdenesCompraPedimentos';
    
    protected $primaryKey = 'OCP_PedimentoId';

    public $timestamps = false;

    public $incrementing = false;

} 