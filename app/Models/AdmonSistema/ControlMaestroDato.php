<?php
/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 14/05/2015
 * Time: 04:30 PM
 */

namespace App\Models\AdmonSistema;


use Illuminate\Database\Eloquent\Model;

class ControlMaestroDato extends Model{

    protected $table = 'ControlesMaestrosDatos';

    protected $primaryKey = 'CMD_DatoId';

    public $incrementing = false;

    public $timestamps = false;


} 