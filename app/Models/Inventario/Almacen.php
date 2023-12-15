<?php namespace App\Models\inventario;

use Illuminate\Database\Eloquent\Model;


/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 07/05/2015
 * Time: 11:58 AM
 */

  

class Almacen extends Model {

        protected $table = 'Almacenes';

    public $timestamps = false;
    public $incrementing = false;

    protected $primaryKey = 'ALM_AlmacenId';



        protected $fillable = [



            'ALM_CodigoAlmacen',
            'ALM_Nombre',
            'ALM_EMP_ResponsableId',
            'ALM_Direccion',
            'ALM_CIUC_ColoniaId',
            'ALM_CodigoPostal',
            'ALM_CIU_CiudadId',
            'ALM_EST_EstadoId',
            'ALM_PAI_PaisId',
            'ALM_CorreoElectronico',
            'ALM_Telefono',
            'ALM_Extension',
            'ALM_Fax',
            'ALM_AlmacenPredeterminado',
           // 'ALM_CMM_CtaPredInvId',
            'ALM_Planear',
           // 'ALM_Eliminado',
           /* 'ALM_FechaUltimaModificacion',
            'ALM_Timestamp',
            'ALM_EMP_ModificadoPorId',
            'ALM_DefinidoPorUsuario1',
            'ALM_DefinidoPorUsuario2',
            'ALM_DefinidoPorUsuario3',
            'ALM_DefinidoPorUsuario4',
            'ALM_DefinidoPorUsuario5',*/
            //'ALM_DEP_DeptoId'
            'ALM_CMM_TipoAlmacenId',
            'ALM_WIP',
            'ALM_Carnico',
            'ALM_Recibo'




        ];

    public function scopeName($query,$id){


       if(trim($id)){
           return static::query()->find($id);

        }
}



}
