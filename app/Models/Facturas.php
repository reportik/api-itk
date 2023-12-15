<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facturas extends Model {

    protected $table = 'Facturas';

    protected $primaryKey = 'FTR_FacturaId';

    public $timestamps = false;

    public $incrementing = false;

    public static function buscaPorCodigo($codigo) {
        $result = \DB::table('Facturas')->
        where('FTR_NumeroFactura', '=', $codigo)->get();

        if(empty($result)){
            $result = '';
        }

        return $result;
    }

    /**
     * Permite eliminar logicamente un registro
     * a través del identificador de la factura.
     *
     * @author Juan Gómez Gálvez
     * @param string $facturaId identificador de la factura
     */
    public static function eliminaPorId($facturaId, $empleadoId) {
        try{
            $ftr = Facturas::find($facturaId);
            $ftr->FTR_Eliminado = 1;
            $ftr->FTR_EMP_ModificadoPorId = $empleadoId;
            $ftr->save();
        }catch (\Exception $e){
            throw $e;
        }
    }
}
