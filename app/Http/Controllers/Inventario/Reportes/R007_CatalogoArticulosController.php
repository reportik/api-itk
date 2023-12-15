<?php namespace App\Http\Controllers\Inventario\Reportes;

/**
 * User: Beto 
 */
use DB;
use PHPExcel_Worksheet_Drawing;
use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade as PDF;
use App\Models\LOG;

class R007_CatalogoArticulosController extends Controller {


    function __construct(){
    }

    public function index()
    {   
        $tipos = array();
        $familias = array();
        $categorias = array();
        $sub_categorias = array();
        return view('Inventario.Reportes.R007_CatalogoArticulos',compact('tipos', 'familias', 'categorias', 'sub_categorias'));
    }

    public function xls($data, $tipo){ 
             
        if ($tipo == '8418208F-EC34-41E8-9802-83B4404764DA') {//si es MP           
            $path = public_path() . '/Reportes/Inventario/R007CatalogoArticulos.xlsx';
        } else {
            $path = public_path() . '/Reportes/Inventario/R007CatalogoArticulospt.xlsx';
        }
            Excel::load($path, function ($excel) use ($data, $tipo) {
            $excel->sheet('Reporte', function ($sheet) use ($data, $tipo) {

                $sheet->cell('C4', function ($cell) {
                    $cell->setValue(AppHelper::instance()->getHumanDate(date("Y-m-d")));
                });
                $sheet->cell('G1', function ($cell) use ($data){
                    $cell->setValue(count($data));
                });
                
                $index = 7;
                $cont_precio = 0;
                $cont_imagen = 0;
                foreach ($data as $row) {
                    
                    $objDrawing = null;
                                         
                    if ($tipo == '8418208F-EC34-41E8-9802-83B4404764DA') {//si es MP  
                        if (number_format($row->precio_udi, 2) === "0.00") {
                            $cont_precio ++;                      
                        }         
                        $renglon = [ $row->codigo, $row->nombre, $row->um_inv, $row->precio_udi, $row->fc, $row->precio_com, $row->um_com, $row->Moneda_com, $row->tipo, $row->familia, $row->categoria, $row->sub_categoria, ];
                    } else {
                        if (number_format($row->precio_art, 2) === "0.00") {
                            $cont_precio ++;                      
                        }
                        $renglon = [ $row->codigo, $row->nombre, $row->um_inv, $row->precio_art, $row->tipo, $row->familia, $row->categoria, $row->sub_categoria, ];
                    }

                        $sheet->getRowDimension($index)->setRowHeight(55);    
                        $objDrawing = new PHPExcel_Worksheet_Drawing;
                        $path_imagen = 'img/articulos/'.$row->IMAGEN;
                        $final_path_imagen = public_path($path_imagen);
                        if (\File::exists($final_path_imagen) && @imagecreatefrompng($final_path_imagen) == true) {
                            $image = true;
                        }else {
                            if (@imagecreatefromjpeg($final_path_imagen) == true) {
                                $image = true;
                            } else {                   
                                $image = false;
                                $log = LOG::firstOrNew(
                                        ['LOG_user' => 0,
                                        'LOG_tipo' => 'info',
                                        'LOG_descripcion' => 'App Imagen Error / No existe - '.$row->IMAGEN,
                                        'LOG_cod_error' => 'R007-ErrImg']
                                    );
                                $log->LOG_fecha = date("Y-m-d H:i:s");
                                $log->save();
                            }
                        }                   
                        if (!$image) {                            
                            $cont_imagen ++;
                            $objDrawing->setPath(public_path('img/articulos/nodisponible.png'));
                        }else {
                            $objDrawing->setPath($path_imagen);                            
                        }
                        $objDrawing->setCoordinates('A'.$index);

                        $objDrawing->setWidthAndHeight(148,74);
                        $objDrawing->setResizeProportional(true);

                        $objDrawing->setWorksheet($sheet);
                        array_unshift($renglon, ''); 
                       
                   
                    $sheet->row($index, $renglon);
                                
                    $index++;
                }
                $sheet->cell('G2', function ($cell) use($cont_precio){
                    $cell->setValue($cont_precio);
                });
                
                $sheet->cell('G3', function ($cell) use($cont_imagen) {
                    $cell->setValue($cont_imagen);
                });
                
            });
        })
            ->setFilename('007 Catalogo Articulos')
            ->export('xlsx',[ 'Set-Cookie' => 'xlscook=done; path=/' ]);
    }

    public function exportar(){    
        ini_set('memory_limit', '-1');
        set_time_limit(0);
       // dd(Request::all());
        $tipo = Request::input('tipo');
        $familias = "'".Request::input('familiasInput'). "'";
        $categorias = "'".Request::input('categoriasInput'). "'";
        $sub_categorias = "'".Request::input('subcategoriasInput'). "'";
        $campos = "";
        $joinz = "";
        $groupby = "";
        if ($tipo == '8418208F-EC34-41E8-9802-83B4404764DA') {//si es MP           
           $campos = " CONVERT (float, CASE WHEN (UMLPC.CMUM_Nombre = UMI.CMUM_Nombre) THEN ISNULL(LPC_PrecioCompra, 0) ELSE ISNULL(LPC_PrecioCompra, 0)/ISNULL(AFC.AFC_FactorConversion, 1) END) as precio_udi, ISNULL(AFC.AFC_FactorConversion, 1) as fc, ISNULL(ControlesMaestrosUM.CMUM_Nombre, UMI.CMUM_Nombre)  as um_fc, ISNULL(LPC_PrecioCompra, 0) as precio_com, UMLPC.CMUM_Nombre  as um_com, Monedas.MON_Nombre Moneda_com,"; 
           $joinz = " left join ArticulosFactoresConversion AFC on AFC_ART_ArticuloId = Articulos.ART_ArticuloId
                    AND AFC_FactorDefault = 0
                    left join ControlesMaestrosUM on AFC.AFC_CMUM_UnidadMedidaId = ControlesMaestrosUM.CMUM_UnidadMedidaId
                    join (
                        select ListaPreciosCompra.* from ListaPreciosCompra
                        inner join Proveedores on LPC_PRO_ProveedorId = PRO_ProveedorId
                        AND PRO_Eliminado = 0
                        inner join (
                            select max(CONVERT(int, LPC_ProvPreProgramado)) as prov_def, ListaPreciosCompra.LPC_ART_ArticuloId  from ListaPreciosCompra 			
                            group by LPC_ART_ArticuloId
                        ) t on t.LPC_ART_ArticuloId = ListaPreciosCompra.LPC_ART_ArticuloId and t.prov_def = ListaPreciosCompra.LPC_ProvPreProgramado
                        WHERE LPC_Eliminado=0
                    ) as Precios on Precios.LPC_ART_ArticuloId = ART_ArticuloId
                    left join ControlesMaestrosUM UMLPC on Precios.LPC_CMUM_UnidadMedidaId = UMLPC.CMUM_UnidadMedidaId
                    left join Monedas on Monedas.MON_MonedaId = CONVERT(nvarchar(max), Precios.LPC_MON_MonedaId) ";
            $groupby = " ISNULL(ControlesMaestrosUM.CMUM_Nombre, UMI.CMUM_Nombre), ISNULL(AFC.AFC_FactorConversion, 1), UMLPC.CMUM_Nombre, LPC_PrecioCompra, Monedas.MON_Nombre,";
        } 
        
        $data = DB::select( "Select 
            Articulos.ART_ArticuloId,
            ART_CodigoArticulo as codigo, 
            ART_Nombre as nombre,
            UMI.CMUM_Nombre as um_inv, 
            ISNULL(ART_Precio, 0) AS precio_art,".$campos.
            "ATP_Descripcion as tipo,
            COALESCE(AFAM_Nombre,'SIN CAPTURA') as familia,
            COALESCE(ACAT_Nombre,'SIN CAPTURA') as categoria,
            COALESCE(SBC.CMM_Valor,'SIN CAPTURA') as sub_categoria,
            ISNULL(ART_Imagen, 'SIN IMAGEN') AS IMAGEN 
            from Articulos 
            left join ArticulosFamilias on ART_AFAM_FamiliaId = AFAM_FamiliaId 
            left join ArticulosCategorias on ART_ACAT_CategoriaId= ACAT_CategoriaId 
            left join ArticulosTipos on ART_ATP_TipoId = ATP_TipoId 
            left join ControlesMaestrosUM UMI on ART_CMUM_UMInventarioId = UMI.CMUM_UnidadMedidaId 
            left join ControlesMaestrosMultiples SBC on ART_CMM_SubcategoriaId = SBC.CMM_ControlId ".$joinz.
            "where ART_Activo = 1 
            AND ATP_TipoId = '".$tipo."'
            AND (AFAM_Nombre in(".$familias.") OR AFAM_Nombre is null)
            AND (ACAT_Nombre in(".$categorias.") OR ACAT_Nombre is null)
            AND (SBC.CMM_Valor in(".$sub_categorias.") OR SBC.CMM_Valor is null)
            group by 
            Articulos.ART_ArticuloId,
            ART_CodigoArticulo, 
            ART_Nombre,
            UMI.CMUM_Nombre, 
            ART_Precio,
            ATP_Descripcion,
            AFAM_Nombre,
            ACAT_Nombre, 
            SBC.CMM_Valor,".$groupby. 
            "ISNULL(ART_Imagen, 'SIN IMAGEN')
            order by ART_Nombre");

            switch (Request::input('type')) {
                case 'excel':
                    self::xls($data, $tipo);
                    break;
                case 'pdf':
                $art_total = '# de Artículos: '. count($data);
                $cont_precio = 0;
                $cont_imagen = 0;
                foreach ($data as $row) {                    
                    if ($tipo == '8418208F-EC34-41E8-9802-83B4404764DA') {//si es MP  
                        if (number_format($row->precio_udi, 2) === "0.00") {
                            $cont_precio ++;                      
                        }                              
                    } else {
                        if (number_format($row->precio_art, 2) === "0.00") {
                            $cont_precio ++;                      
                        }                        
                    }
                        $path_imagen = public_path('img/articulos/'.$row->IMAGEN);
                        
                        if ($row->IMAGEN == 'SIN IMAGEN' || @getimagesize($path_imagen) == false) {
                            $cont_imagen ++;
                        }
                }
                if (($totall = count($data)) > 0) {
                    $art_precio = 'Sin precio: '.$cont_precio.'  '.number_format(($cont_precio *100) / $totall, 1).'%'; 
                    $art_imagen = 'Sin imagen: '.$cont_imagen.'  '.number_format(($cont_imagen *100) / $totall, 1).'%'; 
                }else{
                    $art_precio = '"No hay artículos para los'; 
                    $art_imagen = 'parámetros especificados"'; 
                }
                
                    $pdf = PDF::loadView('Inventario.Reportes.R007PDF', compact('data', 'tipo', 'art_total', 'art_precio', 'art_imagen'));
                    $pdf->setOptions(['isPhpEnabled' => true, 'isRemoteEnabled' => true]);  
                    return $pdf->stream('007 Catalogo Artículos' . ' - ' . date("d/m/Y") . '.Pdf', array("Attachment" => false));
                    break;
            }
    }

    public function combobox(){
    
        $familias = DB::select("select [AFAM_FamiliaId] as [llave], [AFAM_Nombre] as [valor] 
        from [ArticulosFamilias] 
        inner join Articulos on ART_AFAM_FamiliaId = [ArticulosFamilias].[AFAM_FamiliaId] 
        where Articulos.ART_ATP_TipoId in ('".Request::input('tipo')."') AND ART_Activo = 1 
		group by[AFAM_FamiliaId], [AFAM_Nombre]
		order by [AFAM_Nombre] asc");
        
        $categorias = DB::select("select [ACAT_CategoriaId] as [llave], [ACAT_Nombre] as [valor] 
        from [ArticulosCategorias] 
        inner join Articulos on ART_ACAT_CategoriaId = ArticulosCategorias.[ACAT_CategoriaId]
   		where Articulos.ART_ATP_TipoId in ('".Request::input('tipo')."') AND ART_Activo = 1 
		group by[ACAT_CategoriaId], [ACAT_Nombre] order by [ACAT_Nombre] asc");
        
        $sub_categorias = DB::select("select [CMM_ControlId] as [llave], [CMM_Valor] as [valor] 
        from [ControlesMaestrosMultiples] 
        inner join Articulos on ART_CMM_SubcategoriaId = [ControlesMaestrosMultiples].CMM_ControlId 
        where Articulos.ART_ATP_TipoId in ('".Request::input('tipo')."') AND ART_Activo = 1 
		group by[CMM_ControlId], [CMM_Valor] order by [CMM_Valor] asc");
        
        return compact('familias','categorias', 'sub_categorias');
    }
}