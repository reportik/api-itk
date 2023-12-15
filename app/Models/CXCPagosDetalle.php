<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Sistema\DAOGeneralController;

class CXCPagosDetalle extends Model {

    protected $table = 'CXCPagosDetalle';

    protected $primaryKey = 'CXCPD_DetalleId';

    public $timestamps = false;

    public $incrementing=false;

    /**
     * Permite identificar si existen pagos
     * a través del identificador de la factura.
     *
     * @author Juan Gómez Gálvez
     * @param string $facturaId identificador de la factura
     * @return boolean true si existen pagos
     */
    public static function buscaPagosPorFacturaId($facturaId) {
        $result = \DB::table('CXCPagos')
            ->join('CXCPagosDetalle', 'CXCP_CXCPagoId', '=', 'CXCPD_CXCP_CXCPagoId')
            ->where('CXCPD_FTR_FacturaId', '=', $facturaId)
            ->where('CXCP_Eliminado', '=', 0)
            ->get();

        if(count($result) > 0){
            return true;
        }

        return false;
    }

    /**
     * Permite identificar si existen pagos
     * a través del identificador de la factura
     * ignorando los pagos de notas de credito de rapel
     * que no son timbradas
     *
     * @author Juan Gómez Gálvez
     * @param string $facturaId identificador de la factura
     * @return boolean true si existen pagos
     */
    public static function buscaPagosSinRapelPorFacturaId($facturaId) {
        $dao = new DAOGeneralController();
        $result = $dao->getEjecutaConsulta("
                    SELECT *
                    FROM CXCPagos
                    INNER JOIN CXCPagosDetalle ON CXCP_CXCPagoId = CXCPD_CXCP_CXCPagoId
                    WHERE CXCPD_FTR_FacturaId = '$facturaId' AND CXCP_Eliminado = 0
                          AND CXCPD_CXCP_CXCPagoId NOT IN (
                                                            SELECT DISTINCT 
                                                                CXCP_CXCPagoId
                                                            FROM CXCPagos
                                                            INNER JOIN CXCPagosDetalle ON CXCP_CXCPagoId = CXCPD_CXCP_CXCPagoId
                                                            INNER JOIN NotasCredito ON CXCPD_NC_NotaCreditoId = NC_NotaCreditoId AND NC_DescuentoRapel = 1 AND NC_NotaElectronica = 0
                                                            WHERE CXCP_Eliminado = 0 ) ");

        if(count($result) > 0){
            return true;
        }

        return false;
    }

    /**
     * Permite buscar el monto aplicado
     * a través del identificador de la factura.
     *
     * @author Juan Gómez Gálvez
     * @param string $facturaId identificador de la factura
     * @return array con montos aplicado por detalle
     */
    public static function buscaDetallesPagoPorFacturaId($facturaId) {
        $result = \DB::table('CXCPagosDetalle')->
        where('CXCPD_FTR_FacturaId', '=', $facturaId)->get();

        return $result;
    }

    /**
     * Permite obtener el identificador del pago
     * a través del identificador de la factura.
     *
     * @author Juan Gómez Gálvez
     * @param string $facturaId identificador de la factura
     * @return string id del pago
     */
    public static function getPagoIdPorFacturaId($facturaId) {
        $result = \DB::table('CXCPagosDetalle')->
        where('CXCPD_FTR_FacturaId', '=', $facturaId)->get();

        if(!empty($result)){
            $result = $result[0]->CXCPD_CXCP_CXCPagoId;
        } else {
            $result = '';
        }

        return $result;
    }

    /**
     * Permite eliminar fisicamente un registro de la tabla CXCPagosDetalle
     * a través del identificador de la factura.
     *
     * @author Juan Gómez Gálvez
     * @param string $facturaId identificador de la factura
     */
    public static function eliminaPorId($detalleId) {
        \DB::statement("
        DELETE CXCPagosDetalle
        WHERE CXCPD_DetalleId = '$detalleId'");
    }

    /**
     * Permite obtener el total de registros
     * a través del identificador del pago Id.
     *
     * @author Juan Gómez Gálvez
     * @param string $pagoId identificador del pago
     * @return number total de detalles por pago
     */
    public static function totalDetallesPorPagoId($pagoId){
        $resulSet = \DB::select(\DB::raw("
                                            SELECT COUNT(*) AS TOTAL_REGISTROS
                                            FROM CXCPagosDetalle
                                            WHERE CXCPD_CXCP_CXCPagoId = '$pagoId' ")) ;
        return $resulSet[0]->TOTAL_REGISTROS;
    }

    /**
     * Permite obtener el total de monto
     * a través del identificador de la factura.
     *
     * @author Juan Gómez Gálvez
     * @param string $facturaId identificador de la factura
     * @return number total del monto pago
     */
    public static function totalPagosPorFacturaId($facturaId){
        $monto = 0;
        $resulSet = \DB::select(\DB::raw("
                                            SELECT SUM(ISNULL(CXCPD_MontoAplicado, 0.00)) AS TOTAL
                                            FROM CXCPagosDetalle
                                            WHERE CXCPD_FTR_FacturaId = '$facturaId' ")) ;

        if(!empty($resulSet)){
            $monto = $resulSet[0]->TOTAL;
        }

        return $monto;
    }
}