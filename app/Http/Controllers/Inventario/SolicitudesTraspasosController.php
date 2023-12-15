<?php namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Embarques\EmbarquesController;
use App\Http\Controllers\Sistema\AutonumericoController;
use App\Http\Controllers\Sistema\DAOGeneralController;
use App\Http\Controllers\Sistema\DataBaseSession;
use App\Http\Controllers\RecursosHumanos\Departamentos\DepartamentosController;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Mapeos\Controles\ControlesMaestrosMultiples;
use App\Models\Almacenes;
use App\Models\Articulos;
use App\Models\ControlesMaestros;
use App\Models\Inventario\Almacen;
use App\Models\Inventario\Localidad;
use App\Models\Inventario\LocalidadesArticulos;
use App\Models\TraspasosSolicitudes;
use App\Models\TraspasosSolicitudesDetalle;
use Response;
use Illuminate\Support\Facades\Request as NewRequest;

class SolicitudesTraspasosController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    function __construct(){
        $this->dao = new DAOGeneralController();
    }

    public function index()
    {
        /*$encabezados =array(
            'Id',
            'Código',
            'Fecha',
            'Ruta',
            'Origen',
            'Destino',
            'Status'
        );

        $contenidos=array(
            'TRS_TraspasoSolicitudId',
            'TRS_CodigoSolicitud',
            'TRS_FechaSolicitud',
            'RUT_Nombre',
            'Origen',
            'Destino',
            'CMM_Valor'
        );

        $results = \DB::select(\DB::raw(
            "select TRS_TraspasoSolicitudId,TRS_CodigoSolicitud,
                CAST(TRS_FechaSolicitud AS DATE) AS TRS_FechaSolicitud,
                RUT_Nombre, ALM_OR.ALM_Nombre + ' - '+ Origen.LOC_Nombre as Origen,ALM_DES.ALM_Nombre + ' - '+  Destino.LOC_Nombre as Destino, CMM_Valor
                from TraspasosSolicitudes
                inner join Localidades Destino on Destino.LOC_LocalidadId = TRS_LOC_LocalidadDestinoId
                inner join Localidades Origen on Origen.LOC_LocalidadId = TRS_LOC_LocalidadOrigenId
                inner join Almacenes ALM_OR on ALM_OR.ALM_AlmacenId = Origen.LOC_ALM_AlmacenId
                inner join Almacenes ALM_DES on ALM_DES.ALM_AlmacenId = Destino.LOC_ALM_AlmacenId
                inner join ControlesMaestrosMultiples on CMM_ControlId = TRS_CMM_EstatusSolicitudId
                left join TransportesUnidades on TUN_LOC_LocalidadId = TRS_LOC_LocalidadOrigenId AND TUN_Eliminado = 0
                left join Rutas on RUT_TUN_TransporteUnidadId = TUN_TransporteUnidadId
                where TRS_Eliminado = 0
                AND ISNULL(TRS_Eliminado,0) = 0
                AND TRS_FechaSolicitud >= convert(char(6), dateadd(month, -1, getdate()), 112) + '01'
                order by CAST(TRS_FechaSolicitud AS DATE) desc, TRS_CodigoSolicitud DESC"
        ));*/

        $version = $this->dao->nuevoId();

        $almacenes = array(''=>'Selecciona Almacen') + Localidad::select('LOC_LocalidadId','ALM_Nombre')
                ->join('Almacenes', 'ALM_AlmacenId', '=', 'LOC_ALM_AlmacenId')
                //->where('LOC_LocalidadGeneral','=',1)
                ->whereRaw("LOC_Eliminado = 0 AND LOC_General = 1 " .(DataBaseSession::isPermisoCorporativo() ? "" : " AND LOC_LocalidadId = '".DataBaseSession::getLocalidadGeneralId()."' OR LOC_CodigoLocalidad = 'M01'"))
                ->orderby('ALM_Nombre','ASC')
                ->lists('ALM_Nombre','LOC_LocalidadId')->all();

        $almacenesLocalidades = array(''=>'Selecciones Almacen/Localidad') + Localidad::select('LOC_LocalidadId', \DB::raw("ALM_Nombre + (CASE WHEN LOC_LocalidadGeneral = 0 THEN ' - ' + LOC_Nombre ELSE '' END) AS FULL_NAME"))
                ->join('Almacenes','ALM_AlmacenId','=','LOC_ALM_AlmacenId')
                ->where('LOC_LocalidadGeneral','=',0)
                ->whereRaw("ALM_Eliminado = 0".(DataBaseSession::isPermisoCorporativo() ? "" :  " AND ALM_AlmacenId IN (".DataBaseSession::getAlmacenesPorCediId().")"))
                ->lists('FULL_NAME','LOC_LocalidadId')->all();

        //$articulos=array(''=>'Selecciona Artículo') + Articulos::orderby('ART_CodigoArticulo','ASC')->lists('ART_CodigoArticulo','ART_ArticuloId');
        $articulos=array(''=>'Selecciona Articulo') + Articulos::select('ART_ArticuloId',\DB::raw("ART_CodigoArticulo + ' - ' + ART_Nombre AS FULL_NAME"))
                ->whereRaw("ART_Eliminado = 0 AND ART_Activo = 1")
                ->orderBy('ART_Nombre','ASC')
                ->lists('FULL_NAME', 'ART_ArticuloId')->all();

        //date_default_timezone_set('America/Mexico_City');
        $fecha=date('d/m/Y');

        return view('Inventario.SolicitudesTraspasos.create', compact('version','articulos','almacenesLocalidades', 'almacenes', 'fecha'));
    }

    public function buscaSolicitudesTraspasos(){

        $FechaInicio = NewRequest::input('fechaDesde');
        $FechaFinal = NewRequest::input('fechaHasta');

        $consultaLotes = \DB::select(
            \DB::raw(
                "select
                    TRS_TraspasoSolicitudId AS DT_RowId,
                    TRS_CodigoSolicitud,
                    CAST(TRS_FechaSolicitud AS DATE) AS TRS_FechaSolicitud,
                    RUT_Nombre, ALM_OR.ALM_Nombre + ' - '+ Origen.LOC_Nombre as Origen,ALM_DES.ALM_Nombre + ' - '+  Destino.LOC_Nombre as Destino,
                    CMM_Valor
                from TraspasosSolicitudes
                inner join Localidades Destino on Destino.LOC_LocalidadId = TRS_LOC_LocalidadDestinoId
                inner join Localidades Origen on Origen.LOC_LocalidadId = TRS_LOC_LocalidadOrigenId
                inner join Almacenes ALM_OR on ALM_OR.ALM_AlmacenId = Origen.LOC_ALM_AlmacenId
                inner join Almacenes ALM_DES on ALM_DES.ALM_AlmacenId = Destino.LOC_ALM_AlmacenId
                inner join ControlesMaestrosMultiples on CMM_ControlId = TRS_CMM_EstatusSolicitudId
                left join TransportesUnidades on TUN_LOC_LocalidadId = TRS_LOC_LocalidadOrigenId AND TUN_Eliminado = 0
                left join Rutas on RUT_TUN_TransporteUnidadId = TUN_TransporteUnidadId
                where TRS_Eliminado = 0
                AND ISNULL(TRS_Eliminado,0) = 0
                AND CAST(TRS_FechaSolicitud AS DATE) BETWEEN '".$FechaInicio."' AND '".$FechaFinal."'
                ".(DataBaseSession::isPermisoCorporativo() ? "" : " AND (ALM_OR.ALM_AlmacenId IN (".DataBaseSession::getAlmacenesPorCediId().") OR ALM_DES.ALM_AlmacenId IN (".DataBaseSession::getAlmacenesPorCediId().") )")."
                order by
                    CAST(TRS_FechaSolicitud AS DATE) desc,
                    TRS_CodigoSolicitud
                DESC"
            )
        );

        $ajaxData = array();
        $ajaxData['data'] = $consultaLotes;
        $ajaxData['options'] = array();
        return (json_encode($ajaxData));

    }

    public function consultarAutonumerico($idLocalidad){

        $Codigo_Solicitud = '';
        $Autonumerico_dao = new AutonumericoController();
        if($Autonumerico_dao->isAutonumericoActivoPorReferenciaId('CM_INV_SiguienteSolicitudTraspaso', null)){
            $Autonumerico_id = $this->EstablecerAutonumerico(DepartamentosController::getCedisPorLocalidadId($idLocalidad), null);
            $Codigo_Solicitud = $Autonumerico_dao->getSiguienteAutonumericoPorId($Autonumerico_id);
        }

        return $Codigo_Solicitud;

    }

    public function EstablecerAutonumerico($cediId, $EmpleadoId){

        try
        {

            $Autonumerico_Dao = new AutonumericoController();
            $AutonumericoFicha = $Autonumerico_Dao->getAutonumericoN('CM_INV_SiguienteSolicitudTraspaso', $cediId);

            return $AutonumericoFicha->AUT_AutonumericoId;
        }
        catch(Exception $e)
        {



        }

    }

    public function consultarcantidaddecimales(){
        $sub=ControlesMaestros::select('CMA_Valor')
            ->where('CMA_Control','=',\App\Mapeos\Controles\ControlesMaestros::$CMA_INV_DecimalesCantidades)
            ->get();
        return Response::json($sub);
    }

    public function consultararticulo($ART_ArticuloId){
        $result=Articulos::select('ART_ArticuloId','ART_Nombre','ART_CodigoArticulo','CMUM_UnidadMedidaId','CMUM_Nombre')
            ->join('ControlesMaestrosUM','CMUM_UnidadMedidaId','=','ART_CMUM_UMInventarioId')
            ->where('ART_ArticuloId','=',$ART_ArticuloId)
            ->get();
        return Response::json($result);
    }

    public function consultardetallesolicitudtraspaso($id){
        $consultaDetallesSolicitudTraspaso = TraspasosSolicitudesDetalle::select('TRSD_NumeroPartida','ART_CodigoArticulo','ART_Nombre','CMUM_Nombre','TRSD_Cantidad')
            ->join('Articulos','ART_ArticuloId','=','TRSD_ART_ArticuloId')
            ->join('ControlesMaestrosUM','CMUM_UnidadMedidaId','=','TRSD_CMUM_UnidadMedidaId')
            ->where('TRSD_TRS_TraspasoSolicitudId','=',$id)
            ->orderby('TRSD_NumeroPartida','DESC')
            ->get();

        return Response::json($consultaDetallesSolicitudTraspaso);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {

        \DB::beginTransaction();

        try {

            $codigoSolicitud = $this->consultarAutonumerico($_POST['TRS_LOC_LocalidadOrigenId']);

            if($codigoSolicitud != '') {

                $idSolicitud = EmbarquesController::getNuevoId();

                $sub = \DB::table('TraspasosSolicitudes')->insert(
                    array(
                        'TRS_TraspasoSolicitudId' => $idSolicitud,
                        'TRS_CodigoSolicitud' => $codigoSolicitud,
                        'TRS_LOC_LocalidadOrigenId' => $_POST['TRS_LOC_LocalidadOrigenId'],
                        'TRS_LOC_LocalidadDestinoId' => $_POST['TRS_LOC_LocalidadDestinoId'],
                        'TRS_CMM_EstatusSolicitudId' => ControlesMaestrosMultiples::$CMM_EstatusSolicitudTraspaso_Solicitado,
                        'TRS_Comentarios' => $_POST['TRS_Comentarios'],
                        'TRS_FechaSolicitud' => $_POST['TRS_FechaSolicitud'],
                        'TRS_DEP_DeptoId' => DepartamentosController::getCediPorLocalidadId($_POST['TRS_LOC_LocalidadOrigenId'])
                    )
                );

                $UltimoRegistrado = $idSolicitud;//TraspasosSolicitudes::orderby('TRS_FechaUltimaModificacion', 'DESC')->first()->TRS_TraspasoSolicitudId;

                for ($val = 1; $val <= $_POST['CCON_contador']; $val++) {
                    \DB::table('TraspasosSolicitudesDetalle')->insert(
                        array(
                            'TRSD_TRS_TraspasoSolicitudId' => $UltimoRegistrado,
                            'TRSD_ART_ArticuloId' => $_POST['TRSD_ART_ArticuloId' . $val],
                            'TRSD_CMUM_UnidadMedidaId' => $_POST['TRSD_CMUM_UnidadMedidaId' . $val],
                            'TRSD_Cantidad' => floatval($_POST['TRSD_Cantidad' . $val]),
                            'TRSD_NumeroPartida' => floatval($_POST['TRSD_NumeroPartida' . $val])
                        )
                    );
                }
                //return json_encode(array());

                $mensaje = 'La Solicitud de Traspaso se registró con éxito.';

                \DB::commit();
                $codigoSolicitud = preg_replace("/[^0-9,.]/", "", $codigoSolicitud );
                return ['Status' => 'Valido', 'Mensaje' => $mensaje, 'Codigo' => $codigoSolicitud];
            }
            else{
                \DB::rollback();

                return ['Status' => 'Error', 'Mensaje' => 'No se registró la Solicitud de Trapsaso. Ocurrió un error al obtener el Autonúmerico.'];
            }

        }
        catch (\Exception $e) {

            \DB::rollback();

            return ['Status' => 'Error', 'Mensaje' => 'No se registró La Solicitud de Traspaso. Ocurrió un error al realizar el proceso. <br><br><br>Error: <br>'.$e->getMessage()];

        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $resultados=TraspasosSolicitudes::find($id);

        $resultados->TRS_FechaSolicitud = substr($resultados->TRS_FechaSolicitud, 0, 10);

        $almacenes = array(''=>'Selecciona Alamacen') + Localidad::select('LOC_LocalidadId','ALM_Nombre')
                ->join('Almacenes', 'ALM_AlmacenId', '=', 'LOC_ALM_AlmacenId')
                ->whereRaw("LOC_LocalidadId = '".DataBaseSession::getLocalidadGeneralId()."' OR LOC_CodigoLocalidad = 'M01'")
                ->orderby('ALM_Nombre','ASC')
                ->lists('ALM_Nombre','LOC_LocalidadId')->all();

        $almacenesLocalidades = array(''=>'Selecciones Almacen/Localidad') + Localidad::select('LOC_LocalidadId', \DB::raw("ALM_Nombre + (CASE WHEN LOC_LocalidadGeneral = 0 THEN ' - ' + LOC_Nombre ELSE '' END) AS FULL_NAME"))
                ->join('Almacenes','ALM_AlmacenId','=','LOC_ALM_AlmacenId')
                ->where('LOC_LocalidadGeneral','=',0)
                ->whereRaw(" ALM_AlmacenId IN (".DataBaseSession::getAlmacenesPorCediId().")")
                ->lists('FULL_NAME','LOC_LocalidadId')->all();
        //$articulos=array(''=>'Selecciona Código del Artículo') + Articulos::orderby('ART_CodigoArticulo','ASC')->lists('ART_CodigoArticulo','ART_ArticuloId');
        $articulos=array(''=>'Selecciona Articulo') + Articulos::select('ART_ArticuloId',\DB::raw("ART_CodigoArticulo + ' - ' + ART_Nombre AS FULL_NAME"))
                ->whereRaw("ART_Eliminado = 0 AND ART_Activo = 1")
                ->orderBy('ART_Nombre','ASC')
                ->lists('FULL_NAME', 'ART_ArticuloId')->all();

        return view('Inventario.SolicitudesTraspasos.editar', compact('id','resultados', 'almacenesLocalidades', 'articulos', 'almacenes'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

}
