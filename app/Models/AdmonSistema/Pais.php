<?php
/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 18/05/2015
 * Time: 05:09 PM
 */

namespace App\Models\AdmonSistema;


use Illuminate\Database\Eloquent\Model;

class Pais extends Model{

    protected $table = 'Paises';

    protected $primaryKey = 'PAI_PaisId';

    public $timestamps = false;

    public $incrementing = false;
} 