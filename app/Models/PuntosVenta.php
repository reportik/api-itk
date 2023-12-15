<?php
/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 14/05/2015
 * Time: 01:37 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class PuntosVenta extends Model{

    protected $table = 'PuntosVenta';

    protected $primaryKey = 'PV_PuntoVentaId';

    public $timestamps = false;

    public $incrementing = false;

    public static function isExisteCodigoPuntoVenta($codigoPV){

        $numCodigosPV = \DB::table("PuntosVenta")->select(\DB::raw("count(*) as NUM"))
            ->where("PV_Codigo", "=", trim($codigoPV))
            ->get();

        if($numCodigosPV[0]->NUM != null && $numCodigosPV[0]->NUM > 0){
            return true;
        }

        return false;

    }

    public static function isExisteNombrePuntoVenta($nombrePV){

        $numNombresPV = \DB::table("PuntosVenta")->select(\DB::raw("count(*) as NUM"))
            ->where("PV_Codigo", "=", $nombrePV)
            ->get();

        if($numNombresPV[0]->NUM != null && $numNombresPV[0]->NUM > 0){
            return true;
        }

        return false;

    }

} 