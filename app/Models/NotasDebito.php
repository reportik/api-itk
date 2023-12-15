<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotasDebito extends Model {

    protected $table = 'NotasDebito';

    protected $primaryKey = 'ND_NotaDebitoId';

    public $timestamps= false;

    public $incrementing = false;
}
