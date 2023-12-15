<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacturasDetalle extends Model {

    protected $table = 'FacturasDetalle';

    protected $primaryKey = 'FTRD_DetalleId';

    public $timestamps = false;

    public $incrementing = false;

    /**
     * Permite obtener un objeto de la tabla detalles
     * a través del identificador de la factura.
     *
     * @author Juan Gómez Gálvez
     * @param string $facturaId identificador de la factura
     * @return array con los detalles de la factura
     */
    public static function buscaPorFacturaId($facturaId) {
        $result = \DB::select(\DB::raw("
                                        DECLARE @decimalesPoliza INT
                                        SET @decimalesPoliza = (SELECT CMA_Valor FROM ControlesMaestros WHERE CMA_Control LIKE 'CMA_CCNF_DecimalesPolizas')

                                         SELECT
                                            Facturas.*,
                                            FacturasDetalle.*,
                                            FacturasDatos.*,
                                            (ROUND(SUBTOTAL, @decimalesPoliza) - ROUND(DESCUENTO, @decimalesPoliza)) + ROUND(IVA, @decimalesPoliza) AS IMPORTE
                                        FROM Facturas
                                        INNER JOIN (SELECT
                                                        *,
                                                        FTRD_CantidadRequerida * FTRD_PrecioUnitario AS SUBTOTAL,
                                                        FTRD_CantidadRequerida * FTRD_PrecioUnitario * ISNULL(FTRD_PorcentajeDescuento, 0.0) AS DESCUENTO,
                                                        ((FTRD_CantidadRequerida * FTRD_PrecioUnitario) -
                                                        (FTRD_CantidadRequerida * FTRD_PrecioUnitario * ISNULL(FTRD_PorcentajeDescuento, 0.0))) *
                                                        ISNULL(FTRD_CMIVA_Porcentaje, 0.0) AS IVA
                                                    FROM FacturasDetalle ) AS FacturasDetalle ON FTR_FacturaId = FTRD_FTR_FacturaId
                                        INNER JOIN FacturasDatos ON FTR_FacturaId = FTD_FTR_FacturaId
                                        WHERE FTRD_FTR_FacturaId = '$facturaId'
                                         ORDER BY FTRD_NumeroLinea "));

        return $result;
    }

    /**
     * Permite obtener un objeto de la tabla detalles
     * a través del identificador de la factura.
     *
     * @author Juan Gómez Gálvez
     * @param string $facturaId identificador de la factura
     * @return array con los detalles de la factura
     */
    public static function buscaDetallesPorFacturaId($facturaId) {
        $consulta = "
            SELECT 
                  FTRD_DetalleId
                , FTRD_FTR_FacturaId
                , FTRD_NumeroLinea
                , FTRD_ART_ArticuloId
                , FTRD_ART_CodigoArticulo
                , FTRD_ART_Nombre
                , FTRD_CMUM_UnidadMedidaId
                , FTRD_CMIVA_IVAId
                , FTRD_CMIVA_Porcentaje
                , FTRD_PorcentajeDescuento
                , FTRD_CMM_CuentaAbonoId
                , FTRD_InstEspeciales
                , FTRD_CMM_MotivoDescuentoId
                , FTRD_CMM_TipoPartidaId
                , FTRD_DEG_DegustacionId
                , FTRD_CantidadRequerida
                , FTRD_PrecioUnitario
                , FTRD_ReferenciaId
                , FTRD_AFC_FactorConversion
                , FTRD_PROM_PromocionId
                , FTRD_CMM_PreciosComboPromocionId
                , FTRD_CMDE_DescuentoId
                , FTRD_Descuento1
                , FTRD_Descuento2
                , FTRD_Descuento3
                , FTRD_Descuento4
                , FTRD_Descuento5
                , FTRD_CMM_ClaveProductoId
            FROM FacturasDetalle
            WHERE FTRD_FTR_FacturaId = '$facturaId'
        ";

        $result = \DB::select(\DB::raw($consulta));

        return $result;
    }

}
