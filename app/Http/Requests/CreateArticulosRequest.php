<?php namespace App\Http\Requests;

use App\Http\Requests\Request;

class CreateArticulosRequest extends Request {

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
            //->string('name', 50)->nullable()->change();
            'ART_CodigoArticulo'=>'required',
            'ART_Nombre'=>'required',
            'ART_AFAM_FamiliaId'=>'required'
            //'ART_Precio'=>'',
            /*'ART_Imagen'=>'nullable()->change()',
            'ART_Activo'=>'nullable()->change()',
            'ART_CMUM_UMInventarioId'=>'nullable()->change()',
            'ART_FechaUltimaModificacion'=>'nullable()->change()',
            'ART_EMP_ModificadoPor'=>'nullable()->change()',
            'ART_IVA'=>'nullable()->change()',
            'ART_DefinidoPorUsuario1'=>'nullable()->change()',
            'ART_DefinidoPorUsuario2'=>'nullable()->change()',
            'ART_DefinidoPorUsuario3'=>'nullable()->change()',
            'ART_DefinidoPorUsuario4'=>'nullable()->change()',
            'ART_DefinidoPorUsuario5'=>'nullable()->change()',
            'ART_DescuentoEmpleado'=>'nullable()->change()',
            'ART_LOC_LocPredEntradasId'=>'nullable()->change()',
            'ART_LOC_LocPredSalidasId'=>'nullable()->change()',
            'ART_SeguimientoLocMult'=>'nullable()->change()',
            'ART_PermitirCambioAlmacen'=>'nullable()->change()',
            'ART_CrearLocAlmacenaje'=>'nullable()->change()',
            'ART_OcultarLocsCantCero'=>'nullable()->change()',
            'ART_SeguimientoLotMult'=>'nullable()->change()',
            'ART_CMM_CodigoCicloId'=>'nullable()->change()',
            'ART_CMM_PoliticaOrdenesId'=>'nullable()->change()',
            'ART_CantMinimaOrden'=>'nullable()->change()',
            'ART_CantMaximaOrden'=>'nullable()->change()',
            'ART_CantMultipleOrden'=>'nullable()->change()',
            'ART_NoDiasAbastecimiento'=>'nullable()->change()',
            'ART_CantOrdenEconomica'=>'nullable()->change()',
            'ART_CantPuntoOrden'=>'nullable()->change()',
            'ART_CMUM_UMConversionOCId'=>'nullable()->change()',
            'ART_CMUM_UMConversionOVId'=>'nullable()->change()',
            'ART_CantidadAMano'=>'nullable()->change()',
            'ART_FechaUltimoAjuste'=>'nullable()->change()',
            'ART_CantidadUltimoAjuste'=>'nullable()->change()',
            'ART_Eliminado'=>'nullable()->change()',
            'ART_FechaCreacion'=>'nullable()->change()',
            'ART_EMP_CreadoPorId'=>'nullable()->change()',
            'ART_Timestamp'=>'nullable()->change()',
            'ART_AFAM_FamiliaId'=>'nullable()->change()',
            'ART_ACAT_CategoriaId'=>'nullable()->change()',
            'ART_CMM_ManejoInventarioId'=>'nullable()->change()',
            'ART_ATP_TipoId'=>'nullable()->change()',
            'ART_CMM_CtaInventarioId'=>'nullable()->change()',
            'ART_CMM_CtaVentaId'=>'nullable()->change()',
            'ART_CMM_CtaCostoVentaId'=>'nullable()->change()',
            'ART_Obsoleto'=>'nullable()->change()',
            'ART_CMM_TipoCostoId'=>'nullable()->change()',
            'ART_CMM_IndirectoMaterialHistoricoId'=>'nullable()->change()',
            'ART_FechaInicioIndirectosEnCostoHistorico'=>'nullable()->change()',
            'ART_CostoMaterialEstandar'=>'nullable()->change()',
            'ART_FechaInicioIndirectosEnCostoEstandar'=>'nullable()->change()',
            'ART_UltimaModificacionCostoEstandar'=>'nullable()->change()',
            'ART_CMM_IndirectoMaterialEstandarId'=>'nullable()->change()',
            'ART_ValorIndirectoMaterialEstandar'=>'nullable()->change()',
            'ART_UltimoMontoIndirectoMaterialCostoEstandar'=>'nullable()->change()',
            'ART_UltimoCostoPromedio'=>'nullable()->change()',
            'ART_FechaInicioIndirectosEnCostoPromedio'=>'nullable()->change()',
            'ART_CMM_IndirectoMaterialPromedioId'=>'nullable()->change()',
            'ART_ValorIndirectoMaterialPromedio'=>'nullable()->change()',
            'ART_UltimoMontoIndirectoMaterialPromedio'=>'nullable()->change()',
            'ART_UltimoCostoUltimo'=>'nullable()->change()',
            'ART_FechaInicioIndirectosEnCostoUltimo'=>'nullable()->change()',
            'ART_CMM_IndiretoMaterialUltimoId'=>'nullable()->change()',
            'ART_ValorIndirectoMaterialUltimo'=>'nullable()->change()',
            'ART_UltimoMontoIndirectoMaterialCostoUltimo'=>'nullable()->change()',
            'ART_MontoManoObraEstandar'=>'nullable()->change()',
            'ART_MontoIndirectoVariableEstandar'=>'nullable()->change()',
            'ART_MontoPlantaExternaEstandar'=>'nullable()->change()',
            'ART_GLN'=>'nullable()->change()',
            'ART_Consignable'=>'nullable()->change()',
            'ART_CMM_IVAPredeterminado'=>'nullable()->change()',
            'ART_CantidadInventarioAcumulado'=>'nullable()->change()',
            'ART_FechaAsignacionCantInvAcumulado'=>'nullable()->change()',
            'ART_ArticuloPrimoId'=>'nullable()->change()',
            'ART_CantidadLotesMostrar'=>'nullable()->change()',
            'ART_DiasVigencia'=>'nullable()->change()',
            'ART_ManejarDecimalesVentaRuta'=>'nullable()->change()',
            'ART_OmitirOT'=>'nullable()->change()',
            'ART_DeduccionRetroactivaMaterial'=>'nullable()->change()',
            'ART_DeduccionRetroactivaManoObra'=>'nullable()->change()',
            'ART_InspeccionOC'=>'nullable()->change()',
            'ART_InspeccionOT'=>'nullable()->change()',
            'ART_CostoManoObraPromedio'=>'nullable()->change()',
            'ART_CostoIndirectosVariablesPromedio'=>'nullable()->change()',
            'ART_CostoIndirectosFijosPromedio'=>'nullable()->change()',
            'ART_CostoPlantaExternaPromedio'=>'nullable()->change()',
            'ART_CostoManoObraUltimo'=>'nullable()->change()',
            'ART_CostoIndirectosVariablesUltimo'=>'nullable()->change()',
            'ART_CostoIndirectosFijosUltimo'=>'nullable()->change()',
            'ART_CostoPlantaExternaUltimo'=>'nullable()->change()',
            'ART_UltimaModificacionCostoPromedio'=>'nullable()->change()',
            'ART_UltimaModificacionCostoUltimo'=>'nullable()->change()',
            'ART_PorcentajeComision'=>'nullable()->change()',
            'ART_Comentarios'=>'nullable()->change()',
            'ART_OmitirPlaneacionOC'=>'nullable()->change()',
            'ART_PrecioNegociado'=>'nullable()->change()',
            'ART_FraccionArancearia'=>'nullable()->change()',
            'ART_CMM_MarcaId'=>'nullable()->change()',
            'ART_ATP_TipoId'=>'required()->change()',
            'ART_CMUM_UMInventarioId'=>'required()->change()',
            'ART_CuentaInventario'=>'required()->change()'*/
		];
	}

}
