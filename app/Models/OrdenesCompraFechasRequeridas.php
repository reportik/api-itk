<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class OrdenesCompraFechasRequeridas extends Model{

    protected $table = 'OrdenesCompraFechasRequeridas';
    
    protected $primaryKey = 'OCFR_FechaRequeridaId';

    public $timestamps = false;

    public $incrementing = false;

} 