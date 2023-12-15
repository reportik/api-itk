<?php
/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 14/05/2015
 * Time: 02:11 PM
 */

namespace App\Models\AdmonSistema;


use Illuminate\Database\Eloquent\Model;

class ControlMaestroEsquema extends Model{

    protected $table = 'ControlesMaestrosEsquema';

    protected $primaryKey = 'CME_EsquemaId';

    public $timestamps = false;

    public $incrementing = false;

}