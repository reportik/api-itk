<?php namespace App\Models\AdmonSistema;

use Illuminate\Database\Eloquent\Model;

/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 07/05/2015
 * Time: 11:58 AM
 */



class ControlMaestro extends Model {

    protected $table = 'ControlesMaestros';

    protected $primaryKey = 'CMA_ControlId';

    public $timestamps = false;

    public $incrementing = false;



        protected $fillable = ['CMA_Control',
            'CMA_Valor',
            'CMA_ValorPorDefecto',
            'CMA_TipoDato',
            'CMA_Requerido',
            'CMA_MagnitudCampo',
            'CMA_MPC_ModuloId',
            'CMA_CMM_SeccionId',
            'CMA_Etiqueta',
            'CMA_Sistema',
            'CMA_EquipoSesion',
            'CMA_DefinidoPorUsuario1',
            'CMA_DefinidoPorUsuario2',
            'CMA_DefinidoPorUsuario3',
            'CMA_DefinidoPorUsuario4',
            'CMA_DefinidoPorUsuario5',];



} 