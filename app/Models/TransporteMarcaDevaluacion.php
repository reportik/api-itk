<?php
/**
 * User: Wilfrido Martínez Gómez
 * Date: 23/01/2021
 * Time: 01:55 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class TransporteMarcaDevaluacion extends Model{

    protected $table = 'TransporteMarcaDevaluacion';

    protected $primaryKey = 'TMD_TransporteMarcaDevaluacionId';

    public $timestamps = false;

    public $incrementing = false;

}
