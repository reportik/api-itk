<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CXCPagos extends Model {

    protected $table = 'CXCPagos';

    protected $primaryKey = 'CXCP_CXCPagoId';

    public $timestamps = false;

    public $incrementing=false;

    /**
     * Permite actualizar el monto del pago
     * a través del identificador de la tabla.
     *
     * @author Juan Gómez Gálvez
     * @param string $pagoId identificador del pago
     * @param numeric $montoAplicado saldo a descontar del monto total del pago
     */
    public static function actualizaMontoPorId($pagoId, $montoAplicado) {
        \DB::statement("
        UPDATE CXCPagos
        SET CXCP_MontoPago = CXCP_MontoPago - $montoAplicado
        WHERE CXCP_CXCPagoId = '$pagoId'");
    }

    /**
     * Permite eliminar fisicamente un registro de la tabla CXCPagos
     * a través del identificador de la tabla
     *
     * @author Juan Gómez Gálvez
     * @param string $pagoId identificador del pago
     */
    public static function eliminaPorId($pagoId) {
        \DB::statement("
        DELETE CXCPagos
        WHERE CXCP_CXCPagoId = '$pagoId'");
    }

    public static function buscaIdPorDetalleId($detalleId){
        $result = \DB::select(\DB::raw("
                SELECT DISTINCT
                    *
                FROM CXCPagos
                INNER JOIN CXCPagosDetalle ON CXCP_CXCPagoId = CXCPD_CXCP_CXCPagoId
                WHERE CXCPD_DetalleId = '$detalleId' "));

        if(!empty($result)){
            $result = $result[0]->CXCP_CXCPagoId;
        } else {
            $result = '';
        }

        return $result;
    }

} 