<?php namespace App\Models\inventario;

use Illuminate\Database\Eloquent\Model;

/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 07/05/2015
 * Time: 11:58 AM
 */



class TraspasosLocalidades extends Model {

    protected $table = 'TraspasosLocalidades';

    protected $primaryKey = 'TRLOC_TraspasoLocalidadId';

    public $timestamps = false;

    public $incrementing = false;

} 