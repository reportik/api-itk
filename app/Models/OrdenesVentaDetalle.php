<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenesVentaDetalle extends Model {

    protected $table = 'OrdenesVentaDetalle';

    protected $primaryKey = 'OVD_DetalleId';

    public $timestamps = false;

    public $incrementing = false;

    /**
     * Permite obtener un arreglo de la tabla OrdenesVentaDetalle
     * a través del identificador del embarque.
     *
     * @author Juan Gómez Gálvez
     * @param string $embarqueId identificador del embarque
     */
    public static function buscaPorEmbarqueId($embarqueId) {
        $result = \DB::table('OrdenesVentaDetalle')
            ->select('OVD_DetalleId', 'OVD_OV_OrdenVentaId', 'OVD_NumeroLinea', 'OVD_ART_ArticuloId',
            'OVD_Concepto', 'OVD_CMUM_UnidadMedidaId', 'OVD_CMUM_Nombre', 'OVD_PorcentajeSobrePrecio',
                'OVD_CMIVA_IVAId', 'OVD_CMIVA_Porcentaje', 'OVD_PorcentajeDescuento', 'OVD_PROM_PromocionId',
            'OVD_CMM_TipoPartidaId', 'OVD_CantidadRequerida', 'OVD_PrecioUnitario', 'OVD_AFC_FactorConversionId',
            'OVD_AFC_FactorConversion', 'OVD_CMM_PreciosComboPromocionId')
            ->join('EmbarquesDetalle', 'OVD_DetalleId', '=', 'EMBD_OVD_DetalleId')
        ->where('EMBD_EMB_EmbarqueId', '=', $embarqueId)->get();

        return $result;
    }

}
