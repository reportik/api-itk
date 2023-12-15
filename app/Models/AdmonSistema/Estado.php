<?php
/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 18/05/2015
 * Time: 05:33 PM
 */

namespace App\Models\AdmonSistema;


use Illuminate\Database\Eloquent\Model;

class Estado extends Model{

    protected $table = 'Estados';

    protected $primaryKey = 'EST_EstadoId';

    public $timestamps = false;

    public $incrementing = false;

} 