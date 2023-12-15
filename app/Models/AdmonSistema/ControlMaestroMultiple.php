<?php
/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 14/05/2015
 * Time: 01:37 PM
 */

namespace App\Models\AdmonSistema;


use Illuminate\Database\Eloquent\Model;

class ControlMaestroMultiple extends Model{

    protected $table = 'ControlesMaestrosMultiples';

    protected $primaryKey = 'CMM_ControlId';

    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = [
        "CMM_Control",
        "CMM_Valor"
    ];

} 