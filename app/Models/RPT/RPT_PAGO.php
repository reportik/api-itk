<?php namespace App\Models\RPT;

use Illuminate\Database\Eloquent\Model;
use DB;
class RPT_PAGO extends Model
{
    protected $table = 'RPT_PagosConsideradosCXC';
    protected $primaryKey = 'PAGOC_CXCPagoId';
    public $timestamps = false;
    public $consulta = "select OV_CodigoOV,
    FTR_OV_OrdenVentaId,
    cxcp_fechapago,
    cxcp_cxcpagoid,
    CXCP_IdentificacionPago,
    cantidadPagoFactura from (
    Select 
    OV_CodigoOV,
    FTR_OV_OrdenVentaId,
    cxcp_fechapago,
    cxcp_cxcpagoid,
    CXCP_IdentificacionPago ,
    COALESCE(cxcpd_montoaplicado, 0.0) AS cantidadPagoFactura
    From CXCPagos   
    left Join CXCPagosDetalle on CXCP_CXCPagoId = CXCPD_CXCP_CXCPagoId   
    left Join Facturas on FTR_FacturaId = CXCPD_FTR_FacturaId 
    left join OrdenesVenta on FTR_OV_OrdenVentaId = OV_OrdenVentaId
    Where 
    CXCP_Eliminado = 0 
    and CXCP_CMM_FormaPagoId <> 'F86EC67D-79BD-4E1A-A48C-08830D72DA6F'
    AND OV_CMM_EstadoOVId = '3CE37D96-1E8A-49A7-96A1-2E837FA3DCF5' ";

    public $criterio = " AND OV_CodigoOV = ? ";
    public $consulta2 = " GROUP BY
    OV_CodigoOV,
    FTR_OV_OrdenVentaId,
    cxcp_fechapago,
    cxcp_cxcpagoid,
    CXCP_IdentificacionPago,
    CXCPD_MontoAplicado
    ) t 
    LEFT JOIN (SELECT PAGOC_CXCPagoId, PAGOC_OV_CodigoOV FROM   rpt_pagosconsideradoscxc WHERE  pagoc_eliminado = 0) AS PAGOC ON PAGOC.pagoc_cxcpagoid = cxcp_cxcpagoid
    AND PAGOC_OV_CodigoOV = OV_CodigoOV
    Where 
    pagoc_cxcpagoid IS NULL
    order by OV_CodigoOV, CXCP_FechaPago";              
                    
    public function scopeActiveCount($query, $ov)
    {
        $sql = $this->consulta. $this->criterio. $this->consulta2;
        return count(DB::select($sql, [$ov]));
    }
    public function scopeActive($query, $ov)
    {
        $param = [];
        $sql = $this->consulta;
        if ($ov !== 'all') {
            $param = [$ov];
            $sql = $sql . $this->criterio;
        }
        $sql = $sql . $this->consulta2;
       
        return DB::select($sql, $param);
    }
    public function scopePrimero($query)
    {
        $sql = $this->consulta . $this->criterio . $this->consulta2;  
        return $sql;     
        
       
    }
   
}
