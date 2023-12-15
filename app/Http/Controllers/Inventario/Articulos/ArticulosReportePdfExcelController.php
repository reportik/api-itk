<?php namespace App\Http\Controllers\Inventario\Articulos;


use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Facades\Excel;
use View;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Response;


class ArticulosReportePdfExcelController extends Controller {

    public function index()
    {

    }
    public function create()
    {
        //
    }


    /*
     * La funcion de store la uso para pasar la vista html que se pasara a PDF o EXCEL
     */
    public function store(Request $request)
    {
        $date= date('d/m/Y   g:i a');


        $encabezados =array(
           // 'Id',
            'Código'
        ,'Nombre'
        ,'Familia'
        ,'Categoría'
        ,'Subcategoria'
        ,'Marca'
        ,'Precio'
        ,'UMInventario'
        ,'IVA'
        ,'Departamento'
        ,'Pasta'
        ,'Presentacion'
        ,'Peso Bruto'
        ,'Peso Neto'
        ,'Descuento Empleado'
        ,'Loc. PredEntrada'
        ,'Loc. PredSalida'
        ,'Seguimiento Loc. Mult.'
        ,'Permitir Cambio Almacen'
        ,'Crear Localidad Almacenaje'
        ,'Ocultar LocalidadesCant. Cero'
        ,'SeguimientoLotMult'
        //,'Codigo Ciclo'
        ,'Cant Minima Orden'
        ,'Cant Maxima Orden'
        ,'Cant Multiple Orden'
        ,'No. Dias Abastecimiento'
        ,'Cant. Orden Economica'
        ,'Cant Punto Orden'
        ,'UMConversionOC'
        ,'UMConversionOV'
        ,'Cantidad A Mano'
        ,'Fecha Ultimo Ajuste'
        ,'Cantidad Ultimo Ajuste'
        ,'Fecha Creación'
        ,'CreadoPor'
        ,'Manejo Inventario'
        ,'Tipo Artticulo'
        ,'Cta. Inventario'
        ,'Cta. Venta'
        ,'Cta. Costo Venta'
        ,'Obsoleto'
        ,'Tipo Costo'
        ,'Indirecto Material Historico'
        ,'Fecha Inicio Indirectos En Costo Historico'
        ,'Costo Material Estandar'
        ,'Fecha Inicio Indirectos En Costo Estandar'
        ,'Ultima Modificación Costo Estandar'
        ,'Indirecto Material Estandar'
        ,'Valor Indirecto Material Estandar'
        ,'Ultimo Monto Indirecto Material Costo Estandar'
        ,'Ultimo Costo Promedio'
        ,'Fecha Inicio Indirectos En Costo Promedio'
        ,'IndirectoMterialPromedio'
        ,'Valor Indirecto Material Promedio'
        ,'Ultimo Monto Indirecto Material Costo Promedio'
        ,'Ultimo Costo Ultimo'
        ,'Fecha Inicio Indirectos En Costo Ultimo'
        ,'Indirecto Material Ultimo'
        ,'Valor Indirecto Material Ultimo'
        ,'Ultimo Monto Indirecto Material Costo Ultimo'
        ,'Monto Mano Obra Estandar'
        ,'Monto Indirecto Variable Estandar'
        ,'Monto Planta Externa Estandar'
        ,'GLN'
        ,'Consignable'
        ,'Cantidad Inventario Acumulado'
        ,'Fecha Asignacion Cant. Inv. Acumulado'
        ,'Articulo Primo'
        ,'Cantidad Lotes Mostrar'
        ,'Dias Vigencia'
        ,'Manejar Decimales Venta Ruta'
        ,'OmitirOT'
        ,'Deduccion Retroactiva Material'
        ,'Deduccion Retroactiva Mano Obra'
        ,'InspeccionOC'
        ,'InspeccionOT'
        ,'Costo Mano Obra Promedio'
        ,'Costo Indirectos Variables Promedio'
        ,'Costo Indirectos Fijos Promedio'
        ,'Costo Planta Externa Promedio'
        ,'Costo Mano ObraUltimo'
        ,'Costo Indirectos Variables Ultimo'
        ,'Costo Planta Externa Ultimo'
        ,'Ultima Modificacion Costo Promedio'
        ,'Ultima Modificacion Costo Ultimo'
        ,'Porcentaje Comision'
        ,'Comentarios'
        ,'Omitir PlaneacionOC'
        ,'Precio Negociado'
        ,'Fraccion Arancelaria'
        ,'Iva Predeterminado'
        ,'Politica Ordenes'
        ,'Degustable'
        ,'ConversionOT'
        ,'Empaque'
        ,'Cantidad UMEmpaque En Caja'
        ,'Cantidad Cajas En Pallet'
        ,'Dias Vida Anaquel'
        );

        $contenidos=array(
           // 'ART_ArticuloId',
            'ART_CodigoArticulo'
        ,'ART_Nombre'
        ,'ART_AFAM_FamiliaId'
        ,'ART_ACAT_CategoriaId'
        ,'ART_CMM_SubcategoriaId'
        ,'ART_ARTM_MarcaId'
        ,'ART_Precio'
        ,'ART_CMUM_UMInventarioId'
        ,'ART_IVA'
        ,'ART_Departamento'
        ,'ART_Pasta'
        ,'ART_Presentacion'
        ,'PesoBruto'
        ,'PesoNeto'
        ,'ART_DescuentoEmpleado'
        ,'ART_LOC_LocPredEntradasId'
        ,'ART_LOC_LocPredSalidasId'
        ,'ART_SeguimientoLocMult'
        ,'ART_PermitirCambioAlmacen'
        ,'ART_CrearLocAlmacenaje'
        ,'ART_OcultarLocsCantCero'
        ,'ART_SeguimientoLotMult'
       // ,'ART_CMM_CodigoCicloId'
        ,'ART_CantMinimaOrden'
        ,'ART_CantMaximaOrden'
        ,'ART_CantMultipleOrden'
        ,'ART_NoDiasAbastecimiento'
        ,'ART_CantOrdenEconomica'
        ,'ART_CantPuntoOrden'
        ,'ART_CMUM_UMConversionOCId'
        ,'ART_CMUM_UMConversionOVId'
        ,'ART_CantidadAMano'
        ,'ART_FechaUltimoAjuste'
        ,'ART_CantidadUltimoAjuste'
        ,'ART_FechaCreacion'
        ,'ART_EMP_CreadoPorId'
        ,'ART_CMM_ManejoInventarioId'
        ,'ART_ATP_TipoId'
        ,'ART_CMM_CtaInventarioId'
        ,'ART_CMM_CtaVentaId'
        ,'ART_CMM_CtaCostoVentaId'
        ,'ART_Obsoleto'
        ,'ART_CMM_TipoCostoId'
        ,'ART_CMM_IndirectoMaterialHistoricoId'
        ,'ART_FechaInicioIndirectosEnCostoHistorico'
        ,'ART_CostoMaterialEstandar'
        ,'ART_FechaInicioIndirectosEnCostoEstandar'
        ,'ART_UltimaModificacionCostoEstandar'
        ,'ART_CMM_IndirectoMaterialEstandarId'
        ,'ART_ValorIndirectoMaterialEstandar'
        ,'ART_UltimoMontoIndirectoMaterialCostoEstandar'
        ,'ART_UltimoCostoPromedio'
        ,'ART_FechaInicioIndirectosEnCostoPromedio'
        ,'ART_CMM_IndirectoMaterialPromedioId'
        ,'ART_ValorIndirectoMaterialPromedio'
        ,'ART_UltimoMontoIndirectoMaterialCostoPromedio'
        ,'ART_UltimoCostoUltimo'
        ,'ART_FechaInicioIndirectosEnCostoUltimo'
        ,'ART_CMM_IndirectoMaterialUltimoId'
        ,'ART_ValorIndirectoMaterialUltimo'
        ,'ART_UltimoMontoIndirectoMaterialCostoUltimo'
        ,'ART_MontoManoObraEstandar'
        ,'ART_MontoIndirectoVariableEstandar'
        ,'ART_MontoPlantaExternaEstandar'
        ,'ART_GLN'
        ,'ART_Consignable'
        ,'ART_CantidadInventarioAcumulado'
        ,'ART_FechaAsignacionCantInvAcumulado'
        ,'ART_ArticuloPrimoId'
        ,'ART_CantidadLotesMostrar'
        ,'ART_DiasVigencia'
        ,'ART_ManejarDecimalesVentaRuta'
        ,'ART_OmitirOT'
        ,'ART_DeduccionRetroactivaMaterial'
        ,'ART_DeduccionRetroactivaManoObra'
        ,'ART_InspeccionOC'
        ,'ART_InspeccionOT'
        ,'ART_CostoManoObraPromedio'
        ,'ART_CostoIndirectosVariablesPromedio'
        ,'ART_CostoIndirectosFijosPromedio'
        ,'ART_CostoPlantaExternaPromedio'
        ,'ART_CostoManoObraUltimo'
        ,'ART_CostoIndirectosVariablesUltimo'
        ,'ART_CostoPlantaExternaUltimo'
        ,'ART_UltimaModificacionCostoPromedio'
        ,'ART_UltimaModificacionCostoUltimo'
        ,'ART_PorcentajeComision'
        ,'ART_Comentarios'
        ,'ART_OmitirPlaneacionOC'
        ,'ART_PrecioNegociado'
        ,'ART_FraccionArancelaria'
        ,'ART_CMM_IVAPredeterminadoId'
        ,'ART_CMM_PoliticaOrdenesId'
        ,'ART_Degustable'
        ,'ART_CMUM_UMConversionOTId'
        ,'ART_CMUM_UMEmpaqueId'
        ,'ART_CantidadUMEmpaqueEnCaja'
        ,'ART_CantidadCajasEnPallet'
        ,'ART_DiasVidaAnaquel'
        );


        $results=\DB::select(\DB::raw(
        "
        select
 ART_CodigoArticulo -- 'Código del Artículo',
 ,ART_Nombre --          'Nombre',
 ,AFAM_Nombre AS ART_AFAM_FamiliaId --,ART_AFAM_FamiliaId--          'Familia',
 ,ACAT_Nombre as ART_ACAT_CategoriaId --,ART_ACAT_CategoriaId --          'Categoría',
  -- Subcategoria
 ,Subcategoria.CMM_Valor AS ART_CMM_SubcategoriaId --,ART_CMM_SubcategoriaId
 ,ARTM_Nombre AS ART_ARTM_MarcaId --,ART_ARTM_MarcaId --          'Marca'

 ,ART_Precio
 ,UMInventario.CMUM_Nombre AS ART_CMUM_UMInventarioId--,ART_CMUM_UMInventarioId
 ,ART_IVA
 ,ART_Departamento
 ,ART_Pasta
 ,ART_Presentacion
 , PesoBruto=(select AET_Valor from ArticulosEspecificaciones
INNER JOIN ControlesMaestrosMultiples on AET_CMM_ArticuloEspecificaciones = CMM_ControlId
where CMM_Valor ='PESO BRUTO' and AET_ART_ArticuloId=ART_ArticuloId
)
,PesoNeto=(select  AET_Valor from ArticulosEspecificaciones
INNER JOIN ControlesMaestrosMultiples on AET_CMM_ArticuloEspecificaciones = CMM_ControlId
where CMM_Valor ='PESO NETO' and AET_ART_ArticuloId=ART_ArticuloId
)
 ,ART_DescuentoEmpleado
 ,LocEntrada.LOC_Nombre AS ART_LOC_LocPredEntradasId--,ART_LOC_LocPredEntradasId
 ,LocSalida.LOC_Nombre AS ART_LOC_LocPredSalidasId --,ART_LOC_LocPredSalidasId
 ,ART_SeguimientoLocMult
 ,ART_PermitirCambioAlmacen
 ,ART_CrearLocAlmacenaje
 ,ART_OcultarLocsCantCero
 ,ART_SeguimientoLotMult
 --,CodigoCiclo.CMM_Valor AS ART_CMM_CodigoCicloId --,ART_CMM_CodigoCicloId
 ,ART_CantMinimaOrden
 ,ART_CantMaximaOrden
 ,ART_CantMultipleOrden
 ,ART_NoDiasAbastecimiento
 ,ART_CantOrdenEconomica
 ,ART_CantPuntoOrden
 ,UMConversion.CMUM_Nombre as ART_CMUM_UMConversionOCId --,ART_CMUM_UMConversionOCId
 ,UMConversionOV.CMUM_Nombre AS ART_CMUM_UMConversionOVId--,ART_CMUM_UMConversionOVId
 ,ART_CantidadAMano
 ,ART_FechaUltimoAjuste
 ,ART_CantidadUltimoAjuste
 ,ART_FechaCreacion
 ,ECreadoPor.EMP_Nombre as ART_EMP_CreadoPorId--,ART_EMP_CreadoPorId
 ,ManejoInventario.CMM_Valor AS ART_CMM_ManejoInventarioId --,ART_CMM_ManejoInventarioId
 ,ATP_Descripcion as ART_ATP_TipoId--,ART_ATP_TipoId
 ,CtaInventario.CMM_Valor  as ART_CMM_CtaInventarioId --,ART_CMM_CtaInventarioId
 ,CtaVenta.CMM_Valor as ART_CMM_CtaVentaId --,ART_CMM_CtaVentaId
 ,CtaCostoVenta.CMM_Valor AS ART_CMM_CtaCostoVentaId--,ART_CMM_CtaCostoVentaId
 ,ART_Obsoleto
 ,TipoCosto.CMM_Valor AS ART_CMM_TipoCostoId --,ART_CMM_TipoCostoId
 ,IndirectoMaterialHistorico.CMM_Valor AS ART_CMM_IndirectoMaterialHistoricoId --,ART_CMM_IndirectoMaterialHistoricoId
 ,ART_FechaInicioIndirectosEnCostoHistorico
 ,ART_CostoMaterialEstandar
 ,ART_FechaInicioIndirectosEnCostoEstandar
 ,ART_UltimaModificacionCostoEstandar
 ,IndirectoMaterialEstandar.CMM_Valor as ART_CMM_IndirectoMaterialEstandarId --,ART_CMM_IndirectoMaterialEstandarId
 ,ART_ValorIndirectoMaterialEstandar
 ,ART_UltimoMontoIndirectoMaterialCostoEstandar
 ,ART_UltimoCostoPromedio
 ,ART_FechaInicioIndirectosEnCostoPromedio
 ,IndirectoMterialPromedio.CMM_Valor AS ART_CMM_IndirectoMaterialPromedioId --,ART_CMM_IndirectoMaterialPromedioId
 ,ART_ValorIndirectoMaterialPromedio
 ,ART_UltimoMontoIndirectoMaterialCostoPromedio
 ,ART_UltimoCostoUltimo
 ,ART_FechaInicioIndirectosEnCostoUltimo
 ,IndirectoMaterialUltimo.CMM_Valor AS ART_CMM_IndirectoMaterialUltimoId --,ART_CMM_IndirectoMaterialUltimoId
 ,ART_ValorIndirectoMaterialUltimo
 ,ART_UltimoMontoIndirectoMaterialCostoUltimo
 ,ART_MontoManoObraEstandar
 ,ART_MontoIndirectoVariableEstandar
 ,ART_MontoPlantaExternaEstandar
 ,ART_GLN
 ,ART_Consignable
 ,ART_CantidadInventarioAcumulado
 ,ART_FechaAsignacionCantInvAcumulado
 ,ART_ArticuloPrimoId
 ,ART_CantidadLotesMostrar
 ,ART_DiasVigencia
 ,ART_ManejarDecimalesVentaRuta
 ,ART_OmitirOT
 ,ART_DeduccionRetroactivaMaterial
 ,ART_DeduccionRetroactivaManoObra
 ,ART_InspeccionOC
 ,ART_InspeccionOT
 ,ART_CostoManoObraPromedio
 ,ART_CostoIndirectosVariablesPromedio
 ,ART_CostoIndirectosFijosPromedio
 ,ART_CostoPlantaExternaPromedio
 ,ART_CostoManoObraUltimo
 ,ART_CostoIndirectosVariablesUltimo
 ,ART_CostoPlantaExternaUltimo
 ,ART_UltimaModificacionCostoPromedio
 ,ART_UltimaModificacionCostoUltimo
 ,ART_PorcentajeComision
 ,ART_Comentarios
 ,ART_OmitirPlaneacionOC
 ,ART_PrecioNegociado
 ,ART_FraccionArancelaria
 ,IvaPredeterminado.CMM_Valor AS ART_CMM_IVAPredeterminadoId --,ART_CMM_IVAPredeterminadoId
 ,PoliticaOrdenes.CMM_Valor AS ART_CMM_PoliticaOrdenesId --,ART_CMM_PoliticaOrdenesId

 ,ART_Degustable
 ,ConversionOT.CMUM_Nombre AS ART_CMUM_UMConversionOTId --,ART_CMUM_UMConversionOTId
 ,Empaque.CMUM_Nombre AS ART_CMUM_UMEmpaqueId--,ART_CMUM_UMEmpaqueId
 ,ART_CantidadUMEmpaqueEnCaja
 ,ART_CantidadCajasEnPallet
 ,ART_DiasVidaAnaquel


 from Articulos

 left join ArticulosFamilias on ART_AFAM_FamiliaId = AFAM_FamiliaId
 LEFT JOIN ArticulosCategorias ON ART_ACAT_CategoriaId = ACAT_CategoriaId
 --SUBCATEGORIA
 left join ControlesMaestrosMultiples as Subcategoria on Articulos.ART_CMM_SubcategoriaId = Subcategoria.CMM_ControlId

 LEFT JOIN ArticulosMarcas on ART_ARTM_MarcaId = ARTM_MarcaId
 LEFT JOIN ControlesMaestrosUM AS UMInventario on Articulos.ART_CMUM_UMInventarioId = UMInventario.CMUM_UnidadMedidaId
 LEFT JOIN Localidades as LocEntrada on Articulos.ART_LOC_LocPredEntradasId = LocEntrada.LOC_LocalidadId
 LEFT JOIN Localidades as LocSalida on Articulos.ART_LOC_LocPredSalidasId = LocSalida.LOC_LocalidadId
 --LEFT JOIN ControlesMaestrosMultiples AS CodigoCiclo on Articulos.ART_CMM_CodigoCicloId = CodigoCiclo.CMM_ControlId
 LEFT JOIN ControlesMaestrosUM AS UMConversion on Articulos.ART_CMUM_UMConversionOCId= UMConversion.CMUM_UnidadMedidaId
 LEFT JOIN ControlesMaestrosUM AS UMConversionOV on Articulos.ART_CMUM_UMConversionOVId=UMConversionOV.CMUM_UnidadMedidaId
 LEFT JOIN Empleados as ECreadoPor on Articulos.ART_EMP_CreadoPorId = ECreadoPor.EMP_EmpleadoId
 LEFT JOIN ControlesMaestrosMultiples as ManejoInventario on Articulos.ART_CMM_ManejoInventarioId = ManejoInventario.CMM_ControlId
 LEFT JOIN ArticulosTipos ON  Articulos.ART_ATP_TipoId = ArticulosTipos.ATP_TipoId
 LEFT JOIN ControlesMaestrosMultiples AS CtaInventario on ART_CMM_CtaInventarioId = CtaInventario.CMM_ControlId
 LEFT JOIN ControlesMaestrosMultiples as CtaVenta on Articulos.ART_CMM_CtaVentaId=CtaVenta.CMM_ControlId
 left join ControlesMaestrosMultiples as CtaCostoVenta on Articulos.ART_CMM_CtaCostoVentaId = CtaCostoVenta.CMM_ControlId
 LEFT JOIN ControlesMaestrosMultiples as TipoCosto on Articulos.ART_CMM_TipoCostoId = TipoCosto.CMM_ControlId
 Left join ControlesMaestrosMultiples as IndirectoMaterialHistorico on Articulos.ART_CMM_IndirectoMaterialHistoricoId = IndirectoMaterialHistorico.CMM_ControlId
 LEFT JOIN ControlesMaestrosMultiples as IndirectoMaterialEstandar on Articulos.ART_CMM_IndirectoMaterialEstandarId = IndirectoMaterialEstandar.CMM_ControlId
 left join ControlesMaestrosMultiples as IndirectoMterialPromedio on Articulos.ART_CMM_IndirectoMaterialPromedioId = IndirectoMterialPromedio.CMM_ControlId
 LEFT JOIN ControlesMaestrosMultiples as IndirectoMaterialUltimo on Articulos.ART_CMM_IndirectoMaterialUltimoId = IndirectoMaterialUltimo.CMM_ControlId
 left join ControlesMaestrosMultiples as IvaPredeterminado on Articulos.ART_CMM_IVAPredeterminadoId = IvaPredeterminado.CMM_ControlId
 LEFT JOIN ControlesMaestrosMultiples as PoliticaOrdenes on Articulos.ART_CMM_PoliticaOrdenesId = PoliticaOrdenes.CMM_ControlId
 left join ControlesMaestrosUM as ConversionOT on Articulos.ART_CMUM_UMConversionOTId = ConversionOT.CMUM_UnidadMedidaId
 left Join ControlesMaestrosUM as Empaque on Articulos.ART_CMUM_UMConversionOTId = Empaque.CMUM_UnidadMedidaId


 where ART_Eliminado  =0
 ORDER BY ART_ArticuloId
        "
        ));




        $htmlT=$request->input('nuevoinput');
        // dd($htmlT);
        $tipoReporte=$request->input('tipoReporte');

        // dd($request->all());
        if($tipoReporte=="excel"){



            Excel::create('Reporte App', function($excel)use($encabezados,$contenidos,$results)
            {
                $excel->sheet('Sheetname', function($sheet)use($encabezados,$contenidos,$results)
                {
                    $sheet->loadView('Inventario.Articulos.plantillasPdfExcel.createExcelBlade',compact('encabezados','contenidos','results'));

                });
            })->download('xlsx');
        }

        else{




            $name  = 'Reporte.pdf';

            $pdf=App::make('dompdf.wrapper');


            $pdf->loadView('Inventario.Articulos.plantillasPdfExcel.createPdfBlade',compact('htmlT','date'));

            return $pdf->stream($name);
        }

    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {


    }

    public function update(Request $request, $id)
    {

    }


    public function destroy($id)
    {

    }

}

