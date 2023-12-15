<?php
/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 14/05/2015
 * Time: 01:37 PM
 */

namespace App\Models\AdmonSistema;


use Illuminate\Database\Eloquent\Model;

class ControlMaestroIVA extends Model{

    protected $table = 'ControlesMaestrosIVA';

    protected $primaryKey = 'CMIVA_IVAId';

    public $timestamps = false;

    public $incrementing = false;


} 