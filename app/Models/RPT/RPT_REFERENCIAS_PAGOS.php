<?php namespace App\Models\RPT;

use Illuminate\Database\Eloquent\Model;
use DB;
class RPT_REFERENCIAS_PAGOS extends Model
{
    protected $table = 'RPT_ReferenciasCorreoPagos';
    protected $primaryKey = 'RCP_Id';
    public $timestamps = false;
    protected $fillable = [
        'RCP_Id'
        ,'RCP_PRO_ProveedorId'
        ,'RCP_CorreoEnviado'
        ,'RCP_CuentaClabe'
        ,'RCP_CuentaCargo'
        ,'RCP_DescripcionCuentaCargo'
        ,'RCP_CuentaAbono'
        ,'RCP_DescripcionCuentaAbono'
        ,'RCP_Importe'
        ,'RCP_Concepto'
        ,'RCP_FechaAplicacion'
        ,'RCP_IVA'
        ,'RCP_BancoOrigen'
        ,'RCP_BancoDestino'
        ,'RCP_ReferenciaOperacion'
        ,'RCP_ClaveABA_SWIFT'
        ,'RCP_TipoCambio'
        ,'RCP_ImporteDivisa'
        ,'RCP_ImporteUSD'
        ,'RCP_Ciudad'
        ,'RCP_País'
        ,'RCP_Estatus'
        ,'RCP_TipoOperacion'
        ,'RCP_ReferenciaArchivo'
        ,'RCP_ReferenciaEmisor'
        ,'RCP_IdentificadorFiscalBeneficiario'
        ,'RCP_Email'
        ,'RCP_ClaveABA_SWIFTIntermediaria'
        ,'RCP_BancoIntermediario'
        ,'RCP_CuentaIntermediaria'
        ,'RCP_TipoPago'
        ,'RCP_ClaveRastreo'
        ,'RCP_FechaHoraAlta'
        ,'RCP_FechaHoraLiquidacion'
        ,'RCP_RFCOrdenante'
        ,'RCP_Observaciones'
        ,'RCP_TipoAplicacion'
        ,'RCP_SaldoActualizado'
        ,'RCP_FechaRCP'
        ,'RCP_IdRCP'
        ,'RCP_Correo'
        ,'RCP_FP_CodigoFactura'
    ];
}
