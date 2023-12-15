<?php namespace App\Models\inventario;

use Illuminate\Database\Eloquent\Model;

/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 07/05/2015
 * Time: 11:58 AM
 */



class Localidad extends Model {

    protected $table = 'Localidades';

    protected $primaryKey = 'LOC_LocalidadId';

    public $timestamps = false;

    public $incrementing = false;



    protected $fillable = [
        'LOC_CodigoLocalidad',
        'LOC_Nombre',
        'LOC_ALM_AlmacenId',
        // 'LOC_CMM_CtaPredInvId',
        'LOC_Planear',
        'LOC_LocalidadGeneral',
        'LOC_General',
        'LOC_PisoManufactura'
        // 'LOC_Eliminado'

    ];
    public function scopeName($query,$id){


        if(trim($id)){
            return static::query()->find($id);

        }
    }



}