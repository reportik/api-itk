<?php namespace App\Models\Inventario\Articulos;

use Illuminate\Database\Eloquent\Model;

/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 07/05/2015
 * Time: 11:58 AM
 */



class Articulo extends Model {

        protected $table = 'Articulos';

        public $timestamps = false;

        protected $primaryKey = 'ART_ArticuloId';

        public $incrementing=false;

        protected $fillable = [
            'ART_CodigoArticulo',
            'ART_Nombre',
            'ART_Precio',
            'ART_Imagen',
            'ART_Activo',
            'ART_CMUM_UMInventarioId',
            'ART_FechaUltimaModificacion',
            'ART_EMP_ModificadoPor',
            'ART_IVA',
            'ART_Degustable',
            'ART_DefinidoPorUsuario1',
            'ART_DefinidoPorUsuario2',
            'ART_DefinidoPorUsuario3',
            'ART_DefinidoPorUsuario4',
            'ART_DefinidoPorUsuario5',
            'ART_DescuentoEmpleado',
            'ART_LOC_LocPredEntradasId',
            'ART_LOC_LocPredSalidasId',
            'ART_SeguimientoLocMult',
            'ART_PermitirCambioAlmacen',
            'ART_CrearLocAlmacenaje',
            'ART_OcultarLocsCantCero',
            'ART_SeguimientoLotMult',
            'ART_CMM_CodigoCicloId',
            'ART_CMM_PoliticaOrdenesId',
            'ART_CantMinimaOrden',
            'ART_CantMaximaOrden',
            'ART_CantMultipleOrden',
            'ART_NoDiasAbastecimiento',
            'ART_CantOrdenEconomica',
            'ART_CantPuntoOrden',
            'ART_CMUM_UMConversionOCId',
            'ART_CMUM_UMConversionOVId',
            'ART_CMUM_UMConversionOTId',
            'ART_CantidadAMano',
            'ART_FechaUltimoAjuste',
            'ART_CantidadUltimoAjuste',
            'ART_Eliminado',
            //'ART_FechaCreacion',
            'ART_EMP_CreadoPorId',
            'ART_Timestamp',
            'ART_AFAM_FamiliaId',
            'ART_ACAT_CategoriaId',
            'ART_CMM_SubcategoriaId',
            'ART_CMM_ManejoInventarioId',
            'ART_ATP_TipoId',
            'ART_CMM_CtaInventarioId',
            'ART_CMM_CtaVentaId',
            'ART_CMM_CtaCostoVentaId',
            'ART_Obsoleto',
            'ART_CMM_TipoCostoId',
            'ART_CMM_IndirectoMaterialHistoricoId',
            'ART_FechaInicioIndirectosEnCostoHistorico',
            'ART_CostoMaterialEstandar',
            'ART_FechaInicioIndirectosEnCostoEstandar',
            'ART_UltimaModificacionCostoEstandar',
            'ART_CMM_IndirectoMaterialEstandarId',
            'ART_ValorIndirectoMaterialEstandar',
            'ART_UltimoMontoIndirectoMaterialCostoEstandar',
            'ART_UltimoCostoPromedio',
            'ART_FechaInicioIndirectosEnCostoPromedio',
            'ART_CMM_IndirectoMaterialPromedioId',
            'ART_ValorIndirectoMaterialPromedio',
            'ART_UltimoMontoIndirectoMaterialPromedio',
            'ART_UltimoCostoUltimo',
            'ART_FechaInicioIndirectosEnCostoUltimo',
            'ART_CMM_IndiretoMaterialUltimoId',
            'ART_ValorIndirectoMaterialUltimo',
            'ART_UltimoMontoIndirectoMaterialCostoUltimo',
            'ART_MontoManoObraEstandar',
            'ART_MontoIndirectoVariableEstandar',
            'ART_MontoPlantaExternaEstandar',
            'ART_GLN',
            'ART_Consignable',
            'ART_ARTM_MarcaId',
            'ART_CMM_IVAPredeterminadoId',
            'ART_CantidadInventarioAcumulado',
            'ART_FechaAsignacionCantInvAcumulado',
            'ART_ArticuloPrimoId',
            'ART_CantidadLotesMostrar',
            'ART_DiasVigencia',
            'ART_ManejarDecimalesVentaRuta',
            'ART_OmitirOT',
            'ART_IncluirEmpaque',
            'ART_DeduccionRetroactivaMaterial',
            'ART_DeduccionRetroactivaManoObra',
            'ART_InspeccionOC',
            'ART_InspeccionOT',
            'ART_CostoManoObraPromedio',
            'ART_CostoIndirectosVariablesPromedio',
            'ART_CostoIndirectosFijosPromedio',
            'ART_CostoPlantaExternaPromedio',
            'ART_CostoManoObraUltimo',
            'ART_CostoIndirectosVariablesUltimo',
            'ART_CostoIndirectosFijosUltimo',
            'ART_CostoPlantaExternaUltimo',
            'ART_UltimaModificacionCostoPromedio',
            'ART_UltimaModificacionCostoUltimo',
            'ART_PorcentajeComision',
            'ART_Comentarios',
            'ART_OmitirPlaneacionOC',
            'ART_PrecioNegociado',
            'ART_FraccionArancearia',
            'ART_CantidadUMEmpaqueEnCaja',
            'ART_CantidadCajasEnPallet',
            'ART_DiasVidaAnaquel',
            'ART_Departamento',
            'ART_Pasta',
            'ART_DiasVidaAnaquel',
            'ART_Presentacion',
            'ART_CMM_ClaveProductoId'


            /*
            'ART_CodigoArticulo',
            'ART_Nombre',
            'ART_Familia',
            'ART_Categoria',
            'ART_SubCategoria',
            'ART_Modelo',
            'ART_TipoArticulo',
            'ART_UMInventario',
            'ART_Revision',
            'ART_Fabricacion',
            'ART_Activo',
            'ART_TallaColor',
            'ART_Precio',
            'ART_CantidadMano',
            'ART_PorcentajeComision',
            'ART_PrecioNegociado',
            'ART_CuentaVenta',
            'ART_CuentaInventario',
            'ART_CuentaCostoVenta',
            'ART_Obsoleto',
            'ART_GLN',
            'ART_Consignado',
            'ART_IVAPredeterminado',
            'ART_Comentarios',
            'ART_Imagen'
            */
        ];
/*
    public function scopeActualizarImagen($query, $id){
        dd($id);
        //$imprime=$query->where('ART_ArticuloId',$id);
        //dd($imprime);
        return $query->where('ART_ArticuloId',$id);
        //return static::query()->find($id);
    }
*/
} 