<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotasCreditoNoAfectable extends Model {

    protected $table = 'NotasCreditoNoAfectable';

    protected $primaryKey = 'NCNA_NotaCreditoId';

    public $timestamps= false;

    public $incrementing = false;

}
