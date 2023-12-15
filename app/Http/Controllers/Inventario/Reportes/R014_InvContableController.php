<?php namespace App\Http\Controllers\Inventario\Reportes;

/**
 * User: Beto 
 */
use DB;
use PHPExcel_Worksheet_Drawing;
use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Http\Request as R;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade as PDF;
use App\Models\LOG;

class R014_InvContableController extends Controller {

    function __construct(){
    }

    public function index()
    {          
        $almacenes = array();
        $localidades = array();
        $opciones = array();
        return view('Inventario.Reportes.R014_InvContable',
        compact('almacenes', 'localidades', 'opciones'));
    }

    public function xls($data, $tipo, $fcorte, $fdesde){ 
             
        if ($tipo == '1') {//Detalle     
            $path = public_path() . '/Reportes/Inventario/R014DetalleInvContable.xlsx';
        } else {
            $path = public_path() . '/Reportes/Inventario/R014ResumenInvContable.xlsx';
        }
            Excel::load($path, function ($excel) use ($data, $tipo, $fcorte, $fdesde) {
            $excel->sheet('Reporte', function ($sheet) use ($data, $tipo, $fcorte, $fdesde) {
                
                $index = 7;
                if ($tipo == '1') { //Detalle 
                    $sheet->cell('E2', function ($cell) {
                        $cell->setValue(AppHelper::instance()->getHumanDate(date("d-m-Y")));
                    });
                    $sheet->cell('E3', function ($cell) use ($fcorte, $fdesde) {
                        $cell->setValue(AppHelper::instance()->getHumanDate_createFromFormat($fcorte));
                    });  
                    foreach ($data as $row) {                                                                             
                        $renglon = [$row->ACAT_Nombre, $row->CLAVE, $row->LOC_Nombre, $row->ART_CodigoArticulo, $row->ART_Nombre, $row->CMUM_Nombre, $row->CANTIDAD, $row->UNITARIO, $row->COSTO_TOTAL]; 
                        $sheet->row($index, $renglon);                                
                        $index++;
                    }
                    $sheet->setCellValue('I5','=SUBTOTAL(9,I7:I'.(count($data)+6).')');                
                } else {
                    $sheet->cell('D2', function ($cell) {
                        $cell->setValue(AppHelper::instance()->getHumanDate(date("d-m-Y")));
                    });
                    $sheet->cell('D3', function ($cell) use ($fcorte, $fdesde) {
                        $cell->setValue(AppHelper::instance()->getHumanDate_createFromFormat($fcorte));
                    });                      
                    foreach ($data as $row) {  
                        $sheet->row($index, [$row->LOC_Nombre, $row->MP, $row->WIP, $row->PT, $row->NOCONTABLE]); //$row->SUBIR,
                        $index++;
                    }    
                    $sheet->setCellValue('B5','=SUBTOTAL(9,B7:B'.(count($data)+6).')'); 
                    $sheet->setCellValue('C5','=SUBTOTAL(9,C7:C'.(count($data)+6).')'); 
                    $sheet->setCellValue('D5','=SUBTOTAL(9,D7:D'.(count($data)+6).')'); 
                    //$sheet->setCellValue('E5','=SUBTOTAL(9,E7:E'.(count($data)+6).')'); 
                }
                $sheet->setCellValue('E5','=SUBTOTAL(9,E7:E'.(count($data)+6).')');                               
            });
        })
            ->setFilename('014 Inv Contable')
            ->export('xlsx',[ 'Set-Cookie' => 'xlscook=done; path=/' ]);
    }
    public function exist($dia, $mes, $anio){
   
       $rs = DB::table('RPT_InventarioContable')
       ->where('IC_Ejercicio', $anio)
       ->where('IC_periodo', $mes)
       ->count();
       $respuesta = false;
       if($rs > 0){
            $respuesta= true;
       }
       return compact('respuesta');
    }
    public function up($data, $periodo, $ejercicio){ 
        
            \DB::beginTransaction();
            try {
                DB::table('RPT_InventarioContable')
                    ->where('IC_Ejercicio', $ejercicio)
                    ->where('IC_periodo', $periodo)
                    ->delete();
                    
                foreach ($data as $row) {
                    ini_set('memory_limit', '-1');
                    set_time_limit(0);
                    //$sheet->setCellValue($row->LOC_Nombre, $row->CLAVE, $row->COSTO_TOTAL);                               
                    if (($row->MP + $row->WIP + $row->PT) > 0 ){
                        DB::table('RPT_InventarioContable')->insert(
                            ['IC_Ejercicio' => $ejercicio,
                            'IC_periodo' => $periodo,
                            'IC_LOC_Nombre' => $row->LOC_Nombre,
                            'IC_CLAVE' => $row->LOC_LocalidadId,
                            'IC_MAT_PRIMA' => $row->MP,
                            'IC_WIP' => $row->WIP,
                            'IC_PROD_TERM' => $row->PT,                    
                            'IC_COSTO_TOTAL' => ($row->MP + $row->WIP + $row->PT) //+ $row->SUBIR + $row->NOCONTABLE                        
                            ]
                        );
                    }                    
                }  
                \DB::commit();
                return json_encode(array());
            } catch (\Exception $e) {
                \DB::rollback();

                header('HTTP/1.1 500 Internal Server Error');
                header('Content-Type: application/json; charset=UTF-8');
                die(json_encode(array("mensaje" => $e->getMessage(),
                    "codigo" => $e->getCode(),
                    "clase" => $e->getFile(),
                    "linea" => $e->getLine())));
            }
            
    }
    public function exportar(){    
        ini_set('memory_limit', '-1');
        set_time_limit(0);
       // dd(Request::all());
        $tipo = Request::input('tipo');
        $fdesde = Request::input('fdesde');
        $fcorte = Request::input('fcorte');
        $almacenesInput = "'".Request::input('almacenesInput'). "'";
        $localidadesInput = "'".Request::input('localidadesInput'). "'";
        $periodo = null;
        $ejercicio = null;
        $periodo = null; 
        $nom_art = ', ART_Nombre';
        if(Request::input('type') == 'up'){
            $periodo = explode('/', $fcorte);
            $ejercicio = $periodo[2];
            $periodo = $periodo[1];  
            $fdesde = '01/'.$periodo.'/'.$ejercicio;
        } else if(Request::input('type') == 'pdf'){
            $nom_art = ', LEFT(ART_Nombre, 43) ART_Nombre '; 
        }
        
        if ($tipo == '1') {//Detalle 
            $sqlstr = "SELECT   Case
                When LOC_LocalidadId = '49D778C5-BF1C-4683-A9B5-46DB602862C8' and (ART_ATP_TipoId <> '0D0B9D8A-C779-4D11-8CF6-C1DA16E4334C')  then '1 MAT PRIMA'
                When LOC_LocalidadId = '49D778C5-BF1C-4683-A9B5-46DB602862C8' and (ART_ATP_TipoId = '0D0B9D8A-C779-4D11-8CF6-C1DA16E4334C') then '3 PROD. TERM.'
                When LOC_LocalidadId = '0547A9FC-4919-459E-920B-15A9A09882AD' then '7 NO CONTABLE'
                When LOC_LocalidadId = '24F8921F-F6BA-47AC-8E93-C035D44F5E99' then '7 NO CONTABLE'
                When LOC_LocalidadId = '581650A9-63D6-43C0-815D-30922AD402D9' then '2 WIP MATERIAL'
                When LOC_LocalidadId = '0D6A6312-1B21-4D49-9A3A-632B89ACBA2D' then '2 WIP MATERIAL'
                When LOC_LocalidadId = '6F3F5BE4-285E-4DF7-B189-A62F0A74AA1B' and (ART_ATP_TipoId <> '0D0B9D8A-C779-4D11-8CF6-C1DA16E4334C') then '1 MAT PRIMA'
                When LOC_LocalidadId = '6F3F5BE4-285E-4DF7-B189-A62F0A74AA1B' and (ART_ATP_TipoId = '0D0B9D8A-C779-4D11-8CF6-C1DA16E4334C') then '3 PROD. TERM.'
                When LOC_LocalidadId = '61AF170D-A584-4AC9-B1AA-5540DB65E6B0' and (ART_ATP_TipoId <> '0D0B9D8A-C779-4D11-8CF6-C1DA16E4334C') then '1 MAT PRIMA'
                When LOC_LocalidadId = '61AF170D-A584-4AC9-B1AA-5540DB65E6B0' and (ART_ATP_TipoId = '0D0B9D8A-C779-4D11-8CF6-C1DA16E4334C') then '3 PROD. TERM.'
                When LOC_LocalidadId = '1FB5AA3F-45E3-4511-B4AE-27941334CDCC' and (ART_ATP_TipoId <> '0D0B9D8A-C779-4D11-8CF6-C1DA16E4334C') then '1 MAT PRIMA'
                When LOC_LocalidadId = '1FB5AA3F-45E3-4511-B4AE-27941334CDCC' and (ART_ATP_TipoId = '0D0B9D8A-C779-4D11-8CF6-C1DA16E4334C') then '3 PROD. TERM.'
                When LOC_LocalidadId = '10E76110-2A50-48E2-A212-3747C9AAEA4F' then '7 NO CONTABLE'
                When LOC_LocalidadId = 'E6FD8AA4-62FA-4B67-BCCE-D549C9E3BABF' then '2 WIP MATERIAL'--'5 X SUBIR OT'
                When LOC_LocalidadId = 'F4B69178-D90C-450C-BA3A-7AEAEC308180' then '2 WIP MATERIAL'--'5 X SUBIR OT'
                When LOC_LocalidadId = '8DCEC3E4-B9C1-4014-9643-5B777473576C' then '2 WIP MATERIAL'
                When LOC_LocalidadId = '62EAAF01-1020-4C75-9503-D58B07FFC6EF' then '3 PROD. TERM.'
                When LOC_LocalidadId = '34EDC394-529F-4EAE-9761-E12C4D838EDE' then '7 NO CONTABLE'
                When LOC_LocalidadId = 'F493D6B8-0372-428D-A742-0506D9D14863' then '7 NO CONTABLE'
                WHEN LOC_LocalidadId = '57E1E6E2-D0C3-45DC-B02E-0BCA47CBFB44' and (ART_ATP_TipoId <> '0D0B9D8A-C779-4D11-8CF6-C1DA16E4334C') THEN '1 MAT PRIMA' --57E1E6E2-D0C3-45DC-B02E-0BCA47CBFB44	VL1	MATERIAS PRIMAS VALLARTA                 
                WHEN LOC_LocalidadId = 'D29779CE-8A17-4447-BBC4-A714083F7B96' and (ART_ATP_TipoId <> '0D0B9D8A-C779-4D11-8CF6-C1DA16E4334C') THEN '1 MAT PRIMA' --L215_LOCAL2 - 2 LOCALIDAD DOS 17/05/2023
                WHEN LOC_LocalidadId = 'DC2B1090-B380-415F-9E05-5204F380401A' THEN '2 WIP MATERIAL' --DC2B1090-B380-415F-9E05-5204F380401A	VL2	WIP VALLARTA
                WHEN LOC_LocalidadId = '14A62BC7-3180-416A-8A63-67A2F2EAB6FC' and (ART_ATP_TipoId = '0D0B9D8A-C779-4D11-8CF6-C1DA16E4334C') THEN '3 PROD. TERM.' --14A62BC7-3180-416A-8A63-67A2F2EAB6FC	VL3	PRODUCTO TERMINADO VALLARTA
                else '9 NO DEFINIDO' end AS CLAVE
                , LOC_Nombre
                , ART_CodigoArticulo
				, ACAT_Nombre
                " . $nom_art . "
                , CMUM_Nombre 
                , SUM(CANTIDAD) AS CANTIDAD
                , SUM(COSTO_TOTAL) / SUM(CANTIDAD) AS UNITARIO
                , SUM(COSTO_TOTAL) AS COSTO_TOTAL
                FROM (SELECT                    
                UPPER(ALM_CodigoAlmacen) AS ALM_CodigoAlmacen
                , ART_ATP_TipoId 
                , LOC_LocalidadId
                , UPPER(LOC_Nombre) AS LOC_Nombre
                , UPPER(LOT_CodigoLote) AS LOT_CodigoLote 
                , ART_ArticuloId
				, ACAT_Nombre
                , UPPER(ART_CodigoArticulo) AS ART_CodigoArticulo
                , ART_Nombre AS ART_Nombre
                , CASE WHEN CMUM_UnidadMedidaId = '621D5394-CD57-4EBD-8EA9-C81C2124C6DA' THEN 'Kg' ELSE CMUM_Nombre END AS CMUM_Nombre
                , SUM(TRLOT_CantidadTraspaso) AS CANTIDAD
                , CASE WHEN ART_ATP_TipoId = '0D0B9D8A-C779-4D11-8CF6-C1DA16E4334C' THEN ART_Precio ELSE  SUM((ISNULL(LOT_CostoUnitario, 0.0) + ISNULL(LOT_ValorIndirectoMaterial, 0.0)) * TRLOT_CantidadTraspaso) / SUM(TRLOT_CantidadTraspaso) END AS UNITARIO
                , CASE WHEN ART_ATP_TipoId = '0D0B9D8A-C779-4D11-8CF6-C1DA16E4334C' THEN ART_Precio  * SUM(TRLOT_CantidadTraspaso) ELSE  (SUM((ISNULL(LOT_CostoUnitario, 0.0) + ISNULL(LOT_ValorIndirectoMaterial, 0.0)) * TRLOT_CantidadTraspaso) / SUM(TRLOT_CantidadTraspaso))  * SUM(TRLOT_CantidadTraspaso) END AS COSTO_TOTAL            
                FROM TraspasosMovtos
                INNER JOIN TraspasosLotes ON TRAM_TraspasoMovtoId = TRLOT_TRAM_TraspasoMovtoId
                INNER JOIN LotesLocalidades ON TRLOT_LOTL_LoteLocalidadId = LOTL_LoteLocalidadId
                INNER JOIN Localidades ON LOTL_LOC_LocalidadId = LOC_LocalidadId
                INNER JOIN Almacenes ON LOC_ALM_AlmacenId = ALM_AlmacenId
                INNER JOIN Lotes ON LOTL_LOT_LoteId = LOT_LoteId
                INNER JOIN Articulos ON LOT_ART_ArticuloId = ART_ArticuloId
                INNER JOIN ArticulosFamilias ON ART_AFAM_FamiliaId = AFAM_FamiliaId
                LEFT  JOIN ArticulosCategorias ON ART_ACAT_CategoriaId = ACAT_CategoriaId
                LEFT  JOIN ArticulosFactoresConversion ON ART_ArticuloId = AFC_ART_ArticuloId AND AFC_CMUM_UnidadMedidaId = '621D5394-CD57-4EBD-8EA9-C81C2124C6DA'
                INNER JOIN ControlesMaestrosUM ON ART_CMUM_UMInventarioId = CMUM_UnidadMedidaId        
                WHERE TRLOT_FechaTraspaso <= '" . Request::input('fcorte') . ' 23:59:59' . "'
                AND (LOC_LocalidadId IN (" . $localidadesInput . ")) 
                AND (ALM_AlmacenId IN (" . $almacenesInput . "))           
                GROUP BY 
                ALM_CodigoAlmacen
                , ART_ATP_TipoId
                , LOC_LocalidadId
                , LOC_Nombre
                , ART_ATP_TipoId
                , LOT_CodigoLote 
                , ART_Precio
                , ART_ArticuloId
				, ACAT_Nombre
                , ART_CodigoArticulo
                , ART_Nombre
                , CMUM_Nombre
                , CMUM_UnidadMedidaId
                HAVING SUM(TRLOT_CantidadTraspaso) > 0
                ) AS TEMP
                GROUP BY 
                LOC_LocalidadId
                , ART_ATP_TipoId
                , ART_CodigoArticulo
				, ACAT_Nombre
                , ART_Nombre               
                , CMUM_Nombre
                , LOC_Nombre
                ORDER BY 
                ACAT_Nombre, CLAVE, LOC_Nombre, ART_Nombre";  
               // dd($sqlstr);                  
            $data = DB::select($sqlstr);
        }else if($tipo == '0' || $tipo == '3'){//RESUMEN                
            $sqlstr = "SELECT          
                    SUM (Case 
                    When (LOC_LocalidadId = '49D778C5-BF1C-4683-A9B5-46DB602862C8' and (ART_ATP_TipoId <> '0D0B9D8A-C779-4D11-8CF6-C1DA16E4334C')) OR
                    (LOC_LocalidadId = '6F3F5BE4-285E-4DF7-B189-A62F0A74AA1B' and (ART_ATP_TipoId <> '0D0B9D8A-C779-4D11-8CF6-C1DA16E4334C')) OR
                    (LOC_LocalidadId = '61AF170D-A584-4AC9-B1AA-5540DB65E6B0' and (ART_ATP_TipoId <> '0D0B9D8A-C779-4D11-8CF6-C1DA16E4334C')) OR
                    (LOC_LocalidadId = '1FB5AA3F-45E3-4511-B4AE-27941334CDCC' and (ART_ATP_TipoId <> '0D0B9D8A-C779-4D11-8CF6-C1DA16E4334C')) OR
                    (LOC_LocalidadId = 'D29779CE-8A17-4447-BBC4-A714083F7B96' and (ART_ATP_TipoId <> '0D0B9D8A-C779-4D11-8CF6-C1DA16E4334C')) OR --L215_LOCAL2 - 2 LOCALIDAD DOS 17/05/2023
                    (LOC_LocalidadId = '57E1E6E2-D0C3-45DC-B02E-0BCA47CBFB44' and (ART_ATP_TipoId <> '0D0B9D8A-C779-4D11-8CF6-C1DA16E4334C')) --57E1E6E2-D0C3-45DC-B02E-0BCA47CBFB44	VL1	MATERIAS PRIMAS VALLARTA
                    then COSTO_TOTAL ELSE 0 END ) AS MP
                    ,SUM (Case 
                    When (LOC_LocalidadId = '581650A9-63D6-43C0-815D-30922AD402D9')  OR
                    (LOC_LocalidadId = '0D6A6312-1B21-4D49-9A3A-632B89ACBA2D') OR
                    (LOC_LocalidadId = 'E6FD8AA4-62FA-4B67-BCCE-D549C9E3BABF')  OR --WIP
                    (LOC_LocalidadId = 'F4B69178-D90C-450C-BA3A-7AEAEC308180')  OR --WIP
                    (LOC_LocalidadId = '8DCEC3E4-B9C1-4014-9643-5B777473576C') OR 
                    (LOC_LocalidadId = 'DC2B1090-B380-415F-9E05-5204F380401A') --DC2B1090-B380-415F-9E05-5204F380401A	VL2	WIP VALLARTA                
                    then COSTO_TOTAL ELSE 0 END ) AS WIP
                    ,SUM (Case 
                    When (LOC_LocalidadId = '49D778C5-BF1C-4683-A9B5-46DB602862C8' and (ART_ATP_TipoId = '0D0B9D8A-C779-4D11-8CF6-C1DA16E4334C')) OR
                    (LOC_LocalidadId = '6F3F5BE4-285E-4DF7-B189-A62F0A74AA1B' and (ART_ATP_TipoId = '0D0B9D8A-C779-4D11-8CF6-C1DA16E4334C')) OR
                    (LOC_LocalidadId = '61AF170D-A584-4AC9-B1AA-5540DB65E6B0' and (ART_ATP_TipoId = '0D0B9D8A-C779-4D11-8CF6-C1DA16E4334C')) OR
                    (LOC_LocalidadId = '1FB5AA3F-45E3-4511-B4AE-27941334CDCC' and (ART_ATP_TipoId = '0D0B9D8A-C779-4D11-8CF6-C1DA16E4334C')) OR
                    (LOC_LocalidadId = '62EAAF01-1020-4C75-9503-D58B07FFC6EF' and (ART_ATP_TipoId = '0D0B9D8A-C779-4D11-8CF6-C1DA16E4334C')) OR
                    (LOC_LocalidadId = '14A62BC7-3180-416A-8A63-67A2F2EAB6FC' and (ART_ATP_TipoId = '0D0B9D8A-C779-4D11-8CF6-C1DA16E4334C')) --14A62BC7-3180-416A-8A63-67A2F2EAB6FC	VL3	PRODUCTO TERMINADO VALLARTA
                    then COSTO_TOTAL ELSE 0 END ) AS PT
                    --,SUM (Case 
                    --When (LOC_LocalidadId = 'E6FD8AA4-62FA-4B67-BCCE-D549C9E3BABF')  OR
                    --(LOC_LocalidadId = 'F4B69178-D90C-450C-BA3A-7AEAEC308180') then COSTO_TOTAL ELSE 0 END ) AS SUBIR
                    ,SUM (Case 
                    When (LOC_LocalidadId = '0547A9FC-4919-459E-920B-15A9A09882AD')  OR
                    (LOC_LocalidadId = '24F8921F-F6BA-47AC-8E93-C035D44F5E99')  OR
                    (LOC_LocalidadId = '10E76110-2A50-48E2-A212-3747C9AAEA4F')  OR
                    (LOC_LocalidadId = 'F493D6B8-0372-428D-A742-0506D9D14863') OR 
                    (LOC_LocalidadId = '34EDC394-529F-4EAE-9761-E12C4D838EDE') then COSTO_TOTAL ELSE 0 END ) AS NOCONTABLE
                , LOC_Nombre 
                , LOC_LocalidadId 
                FROM (SELECT            
                UPPER(ALM_CodigoAlmacen) AS ALM_CodigoAlmacen
                , ART_ATP_TipoId 
                , LOC_LocalidadId
                , UPPER(LOC_Nombre) AS LOC_Nombre
                , UPPER(LOT_CodigoLote) AS LOT_CodigoLote 
                , ART_ArticuloId
                , UPPER(ART_CodigoArticulo) AS ART_CodigoArticulo
                , ART_Nombre AS ART_Nombre
                , CASE WHEN CMUM_UnidadMedidaId = '621D5394-CD57-4EBD-8EA9-C81C2124C6DA' THEN 'Kg' ELSE CMUM_Nombre END AS CMUM_Nombre
                , SUM(TRLOT_CantidadTraspaso) AS CANTIDAD
                , CASE WHEN ART_ATP_TipoId = '0D0B9D8A-C779-4D11-8CF6-C1DA16E4334C' THEN ART_Precio ELSE  SUM((ISNULL(LOT_CostoUnitario, 0.0) + ISNULL(LOT_ValorIndirectoMaterial, 0.0)) * TRLOT_CantidadTraspaso) / SUM(TRLOT_CantidadTraspaso) END AS UNITARIO
                , CASE WHEN ART_ATP_TipoId = '0D0B9D8A-C779-4D11-8CF6-C1DA16E4334C' THEN ART_Precio  * SUM(TRLOT_CantidadTraspaso) ELSE  (SUM((ISNULL(LOT_CostoUnitario, 0.0) + ISNULL(LOT_ValorIndirectoMaterial, 0.0)) * TRLOT_CantidadTraspaso) / SUM(TRLOT_CantidadTraspaso))  * SUM(TRLOT_CantidadTraspaso) END AS COSTO_TOTAL            
                FROM TraspasosMovtos
                INNER JOIN TraspasosLotes ON TRAM_TraspasoMovtoId = TRLOT_TRAM_TraspasoMovtoId
                INNER JOIN LotesLocalidades ON TRLOT_LOTL_LoteLocalidadId = LOTL_LoteLocalidadId
                INNER JOIN Localidades ON LOTL_LOC_LocalidadId = LOC_LocalidadId
                INNER JOIN Almacenes ON LOC_ALM_AlmacenId = ALM_AlmacenId
                INNER JOIN Lotes ON LOTL_LOT_LoteId = LOT_LoteId
                INNER JOIN Articulos ON LOT_ART_ArticuloId = ART_ArticuloId
                INNER JOIN ArticulosFamilias ON ART_AFAM_FamiliaId = AFAM_FamiliaId
                LEFT  JOIN ArticulosCategorias ON ART_ACAT_CategoriaId = ACAT_CategoriaId
                LEFT  JOIN ArticulosFactoresConversion ON ART_ArticuloId = AFC_ART_ArticuloId AND AFC_CMUM_UnidadMedidaId = '621D5394-CD57-4EBD-8EA9-C81C2124C6DA'
                INNER JOIN ControlesMaestrosUM ON ART_CMUM_UMInventarioId = CMUM_UnidadMedidaId
               WHERE TRLOT_FechaTraspaso <= '" . Request::input('fcorte') . ' 23:59:59' . "'
                AND (LOC_LocalidadId IN (" . $localidadesInput . ")) 
                AND (ALM_AlmacenId IN (" . $almacenesInput . "))                               
                GROUP BY 
                ALM_CodigoAlmacen
                , ART_ATP_TipoId 
                , LOC_LocalidadId
                , LOC_Nombre
                , ART_ATP_TipoId
                , LOT_CodigoLote 
                , ART_Precio
                , ART_ArticuloId
                , ART_CodigoArticulo
                , ART_Nombre
                , CMUM_Nombre
                , CMUM_UnidadMedidaId
                HAVING SUM(TRLOT_CantidadTraspaso) > 0
                ) AS TEMP
                GROUP BY 
                LOC_LocalidadId
                , LOC_Nombre
                ORDER BY 
                LOC_Nombre";
              
            $data = DB::select($sqlstr);  

        }
            switch (Request::input('type')) {
                case 'excel':
                    self::xls($data, $tipo, $fcorte, $fdesde);
                    break;
                case 'pdf':
                   // $fdesde = AppHelper::instance()->getHumanDate_createFromFormat($fdesde);
                    $fcorte = AppHelper::instance()->getHumanDate_createFromFormat($fcorte);                   

                    if ($tipo == '1') {//Detalle    
                        // $almacenes = array_pluck($data, 'ACAT_Nombre');
                        // $datas = [];
                        // foreach ($almacenes as $key => $value) {
                        //     $mialmacen = array_where($data, function ($key, $val) use ($value) {
                        //         return $val->ACAT_Nombre = $value;
                        //     });
                        //     $datas[] = ($mialmacen);
                        // }
                        //dd(collect($datas[0]), $data);
                        $pdf = PDF::loadView('Inventario.Reportes.R014DetalleAlmacenesPDF', compact('data', 'tipo', 'fdesde', 'fcorte'));
                    }else if($tipo == '0'){//RESUMEN        
                        $pdf = PDF::loadView('Inventario.Reportes.R014ResumenPDF', compact('data', 'tipo', 'fdesde', 'fcorte'));                    
                    }
                    $pdf->setOptions(['isPhpEnabled' => true, 'isRemoteEnabled' => true]);  
                    return $pdf->stream('014 Inv Contable' . ' - ' . date("d/m/Y") . '.Pdf', array("Attachment" => false));
                    break;
                case 'up':
                self::up($data, $periodo, $ejercicio);
                break;
            }
    }
    public function combobox(){
        $almacenes_ajax = Request::input('almacenes');
        //dd($almacenes_ajax);
        if (strlen($almacenes_ajax) > 3 || $almacenes_ajax != '') {
            $almacenes='';
            $localidades = DB::select("SELECT LOC_LocalidadId as llave, LOC_CodigoLocalidad + ' - ' + LOC_Nombre as valor FROM Localidades        
                where LOC_Eliminado = 0 
                AND LOC_ALM_AlmacenId IN ('".$almacenes_ajax."')
                ORDER BY LOC_Nombre        
            ");
        } else {
            $almacenes = DB::select("SELECT ALM_AlmacenId as llave,  ALM_CodigoAlmacen + ' - ' + ALM_Nombre as valor FROM Almacenes 
                INNER JOIN Localidades ON LOC_ALM_AlmacenId = ALM_AlmacenId
                where ALM_Eliminado = 0 AND LOC_Eliminado = 0 
                GROUP BY ALM_AlmacenId, ALM_CodigoAlmacen, ALM_Nombre
                ORDER BY ALM_Nombre");
            
            $localidades = DB::select("SELECT LOC_LocalidadId as llave, LOC_CodigoLocalidad + ' - ' + LOC_Nombre as valor FROM Localidades        
                where LOC_Eliminado = 0  ORDER BY LOC_Nombre");
            
        }      
                  
        return compact('almacenes','localidades');
    }
}
