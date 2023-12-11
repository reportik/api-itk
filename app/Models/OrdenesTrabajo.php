<?php namespace Muliix\Models;
use DB;
use Illuminate\Database\Eloquent\Model;
use Muliix\Models\OrdenesTrabajoDetalleArticulos;

class OrdenesTrabajo extends Model {

    protected $table = 'OrdenesTrabajo';

    protected $primaryKey = 'OT_OrdenTrabajoId';

    public $timestamps = false;

    public $incrementing = false;
    
    public function ordenesTrabajoDetalle(){
        $detalle = DB::select("SELECT ART_CodigoArticulo AS ART_CODIGO
        , ART_Nombre AS ART_NOMBRE
        , ART_Precio AS ART_PRECIO
        , OTDA_Cantidad AS OT_CANT
        , OV_CodigoOV AS OV
        , PRY_CodigoEvento AS PROY_CODIGO
        , PRY_NombreProyecto AS PROY_NOMBRE
        , CLI_CodigoCliente AS CLI_CODIGO
        , CLI_RazonSocial AS CLI_NOMBRE
        , UPPER(CMM_Valor) AS OT_ESTATUS
        FROM OrdenesTrabajo 
        INNER JOIN OrdenesTrabajoDetalleArticulos on OT_OrdenTrabajoId = OTDA_OT_OrdenTrabajoId
        INNER JOIN Articulos on ART_ArticuloId = OTDA_ART_ArticuloId
        INNER JOIN OrdenesTrabajoReferencia on OT_OrdenTrabajoId = OTRE_OT_OrdenTrabajoId
        INNER JOIN OrdenesVenta on OTRE_OV_OrdenVentaId = OV_OrdenVentaId
        INNER JOIN Proyectos on OV_PRO_ProyectoId = PRY_ProyectoId
        INNER JOIN Clientes on OV_CLI_ClienteId = CLI_ClienteId
        LEFT JOIN ControlesMaestrosMultiples cmm on cmm.CMM_ControlId = OT_CMM_Estatus
        Where OT_OrdenTrabajoId = ?", [$this->OT_OrdenTrabajoId]);
        
        return $detalle;
    }
}
