<?php namespace App\Http\Controllers\Sistema;
use Illuminate\Support\Facades\Config;
//use Illuminate\Support\Facades\Cookie;
//use Illuminate\Support\Facades\Session;
use App\Mapeos\Controles\ControlesMaestrosMultiples;
use App\Models\Almacenes;
use App\Models\EmpleadosAlmacenes;
use App\Models\Usuarios;
//use App\Models\Departamentos;
use Illuminate\Support\Facades\Request;

/**
 * Created by PhpStorm.
 * User: Victor
 * Date: 15/11/2016
 * Time: 12:24 AM
 */

const COL_BASE_DATOS = 0;
const COL_USUARIO_ID = 1;
const COL_EMPLEADO_ID = 2;
const COL_EMPLEADO_NOMBRE = 3;
const COL_AUTENTICADO = 4;
const COL_CEDI_ID = 5;
const COL_CORPORATIVO = 6;



class DataBaseSession {

//    public static $NAME_DB = '';
//    public static $USER_ID = '';
//    public static $EMPLEADO_ID = '';
//    public static $NOMBRE_EMPLEADO = '';
//
//    public function __construct()
//    {
//        $valorCookie = $_COOKIE['muliix_session'];
//
//        $arrayDatos = explode("|", $valorCookie);
//
//        $DB = explode(":", $arrayDatos[0])[1];
//        $usuarioId = explode(":", $arrayDatos[1])[1];
//        $empleadoId = explode(":", $arrayDatos[2])[1];
//        $empleadoNombre = explode(":", $arrayDatos[3])[1];
//
//
//        DataBaseSession::setNAMEDB($DB);
//        DataBaseSession::setUSERID($usuarioId);
//        DataBaseSession::setEMPLEADOID($empleadoId);
//        DataBaseSession::setNOMBREEMPLEADO($empleadoNombre);
//
//    }


    /**
     * @return string
     */
    public static function getEmpleadoId()
    {
        $userdata = auth()->user();
        return $userdata['USU_EMP_EmpleadoId'];

        if(isset($_COOKIE['muliix_session'])) {
            $valorCookie = $_COOKIE['muliix_session'];

            $arrayDatos = explode("|", $valorCookie);

            $empleadoId = explode(":", $arrayDatos[COL_EMPLEADO_ID])[1];

            return $empleadoId;
        }
        else{
            return null;
        }
    }

    /**
     * @return string
     */
    public static function getNombreDB()
    {
        if(isset($_COOKIE['muliix_session'])) {
            $valorCookie = $_COOKIE['muliix_session'];

            $arrayDatos = explode("|", $valorCookie);

            $DB = explode(":", $arrayDatos[COL_BASE_DATOS])[1];

            return $DB;
        }
        else{
            return '';
        }
    }

    /**
     * @return string
     */
    public static function getNombreEmpleado()
    {
        if(isset($_COOKIE['muliix_session'])) {
            $valorCookie = $_COOKIE['muliix_session'];

            $arrayDatos = explode("|", $valorCookie);

            $nombreEmpleado = explode(":", $arrayDatos[COL_EMPLEADO_NOMBRE])[1];

            return $nombreEmpleado;
        }
        else{
            return null;
        }
    }

    /**
     * @return string
     */
    public static function getUsuarioId()
    {

        if(isset($_COOKIE['muliix_session'])) {
            $valorCookie = $_COOKIE['muliix_session'];

            $arrayDatos = explode("|", $valorCookie);

            $usuarioId = explode(":", $arrayDatos[COL_USUARIO_ID])[1];

            return $usuarioId;
        }
        else{
            return null;
        }
    }

    public static function isAunteticado()
    {

        if(isset($_COOKIE['muliix_session'])) {
            $valorCookie = $_COOKIE['muliix_session'];

            $arrayDatos = explode("|", $valorCookie);

            $usuarioId = explode(":", $arrayDatos[COL_AUTENTICADO])[1];

            return $usuarioId == 'true' ? true : false;
        }
        else{
            return false;
        }
    }

    public static function isPermisoCorporativo()
    {

        if(isset($_COOKIE['muliix_session'])) {
            $valorCookie = $_COOKIE['muliix_session'];

            $arrayDatos = explode("|", $valorCookie);

            $corporativo = explode(":", $arrayDatos[COL_CORPORATIVO])[1];

            return $corporativo == 1 ? true : false;
        }
        else{
            return false;
        }
    }

    public static function getCediId()
    {

        if(isset($_COOKIE['muliix_session'])) {
            $valorCookie = $_COOKIE['muliix_session'];

            $arrayDatos = explode("|", $valorCookie);

            $idCedi = explode(":", $arrayDatos[COL_CEDI_ID])[1];

            $nombreCedi = \DB::table('Departamentos')
                ->where('DEP_DeptoId', '=', $idCedi)
                ->where('DEP_Eliminado', '=', 0)
                ->get()[0]->DEP_Nombre;

            if(strpos($nombreCedi, 'Matriz') !== false){

                $cedisMatriz = \DB::select(\DB::raw("
                    select * from Departamentos
                    where DEP_Nombre like '%Matriz%'
                    and DEP_CMM_TipoDeptoId = '5845CCF9-23B9-41C7-B49F-A8495A7C4D08'
                    and DEP_Eliminado = 0"));

                $idsCedisMatriz = '';

                for($x=0;$x<count($cedisMatriz);$x++){
                    $idsCedisMatriz .= $idsCedisMatriz == '' ? "'".$cedisMatriz[$x]->DEP_DeptoId."'" : ",'".$cedisMatriz[$x]->DEP_DeptoId."'";
                }

                return $idsCedisMatriz;
            }


            return "'$idCedi'";
            //return $corporativo == 1 ? true : false;
        }
        else{
            return null;
        }
    }

    public static function getLocalidadGeneralId()
    {

        $idCedi = DataBaseSession::getCediId();

        if(is_null($idCedi)){
            return null;
        }
        else{

            $idLocalidadGeneral = \DB::select(\DB::raw("
                    select LOC_LocalidadId,LOC_Nombre
                    from Almacenes
                    inner join AlmacenesSucursales on ALMS_ALM_AlmacenId = ALM_AlmacenId
                    inner join Localidades on LOC_ALM_AlmacenId = ALMS_ALM_AlmacenId
                    where
                    ALMS_DEP_DeptoId in ($idCedi)
                    and LOC_General = 1
                    and LOC_Eliminado = 0
                    and ALM_Eliminado = 0
                    and LOC_LocalidadGeneral = 0
                    group by LOC_LocalidadId, LOC_Nombre"))[0]->LOC_LocalidadId;

            return $idLocalidadGeneral;

        }

    }

    public static function getAlmacenes(){

        $idEmpleado = DataBaseSession::getEmpleadoId();

        if(is_null($idEmpleado)){
            return null;
        }
        else {
            $almacenes = EmpleadosAlmacenes::select('EAL_ALM_AlmacenId')->where('EAL_EMP_EmpleadoId', '=', $idEmpleado)
                ->get();

            $almacenesArray = array();

            for($x=0; $x<count($almacenes); $x++){

                $almacenesArray = $almacenesArray + [$almacenes[$x]->EAL_ALM_AlmacenId];

            }

            return $almacenesArray;

        }

    }

    public static function getAlmacenesNombres(){

        $idEmpleado = DataBaseSession::getEmpleadoId();

        if(is_null($idEmpleado)){
            return null;
        }
        else {
            $almacenes = EmpleadosAlmacenes::select('ALM_CodigoAlmacen', 'ALM_Nombre')
                ->join('Almacenes', 'ALM_AlmacenId', '=','EAL_ALM_AlmacenId')
                ->where('EAL_EMP_EmpleadoId', '=', $idEmpleado)
                ->get();

            $almacenesNombres = "";

            for($x=0; $x<count($almacenes); $x++){

                $almacenesNombres = $almacenesNombres . ($almacenesNombres == "" ? ("(".$almacenes[$x]->ALM_CodigoAlmacen.") ". $almacenes[$x]->ALM_Nombre)
                        : "(".$almacenes[$x]->ALM_CodigoAlmacen.") ". $almacenes[$x]->ALM_Nombre);

            }
            return $almacenesNombres;
        }

    }

    public static function getAlmacenesId(){

        $idEmpleado = DataBaseSession::getEmpleadoId();

        if(is_null($idEmpleado)){
            return null;
        }
        else {
            $almacenes = EmpleadosAlmacenes::select('ALM_AlmacenId')
                ->join('Almacenes', 'ALM_AlmacenId', '=','EAL_ALM_AlmacenId')
                ->where('EAL_EMP_EmpleadoId', '=', $idEmpleado)
                ->get();

            $almacenesId = "";

            for($x=0; $x<count($almacenes); $x++){

                $almacenesId = $almacenesId . ($almacenesId == "" ? "'".$almacenes[$x]->ALM_AlmacenId."'"
                        : ", '".$almacenes[$x]->ALM_AlmacenId."'");

            }
            return $almacenesId;
        }

    }

    public static function getAlmacenesProductoEnProcesoPorEmpleadoLogueado(){

        $idEmpleado = DataBaseSession::getEmpleadoId();

        if(is_null($idEmpleado)){
            return null;
        }
        else {

            if(DataBaseSession::isPermisoCorporativo()){

                $almacenes = Almacenes::select('ALM_Nombre')
                    ->where('ALM_CMM_TipoAlmacenId', '=', "7AAE2221-3420-4B17-B8AC-EE53D8339D64")
                    ->where('ALM_Eliminado', '=', 0)
                    ->orderBy('ALM_Nombre')
                    ->get();

            }
            else {

                $almacenes = EmpleadosAlmacenes::select('ALM_Nombre')
                    ->join('Almacenes', 'ALM_AlmacenId', '=', 'EAL_ALM_AlmacenId')
                    ->where('EAL_EMP_EmpleadoId', '=', $idEmpleado)
                    ->where('ALM_CMM_TipoAlmacenId', '=', "7AAE2221-3420-4B17-B8AC-EE53D8339D64")
                    ->where('ALM_Eliminado', '=', 0)
                    ->orderBy('ALM_Nombre')
                    ->get();
            }

            $almacenesNombre = '';

            for($x=0; $x<count($almacenes); $x++){

                $almacenesNombre = $almacenesNombre . ($almacenesNombre == "" ? "" : ", "). $almacenes[$x]->ALM_Nombre;

            }

            return $almacenesNombre;

        }

    }

    public static function getIdsAlmacenesProductoEnProcesoPorEmpleadoLogueado(){

        $idEmpleado = DataBaseSession::getEmpleadoId();

        if(is_null($idEmpleado)){
            return null;
        }
        else {

            if(DataBaseSession::isPermisoCorporativo()){

                $almacenes = Almacenes::select('ALM_AlmacenId')
                    ->where('ALM_CMM_TipoAlmacenId', '=', "7AAE2221-3420-4B17-B8AC-EE53D8339D64")
                    ->where('ALM_Eliminado', '=', 0)
                    ->orderBy('ALM_Nombre')
                    ->get();

            }
            else {

                $almacenes = EmpleadosAlmacenes::select('ALM_AlmacenId')
                    ->join('Almacenes', 'ALM_AlmacenId', '=', 'EAL_ALM_AlmacenId')
                    ->where('EAL_EMP_EmpleadoId', '=', $idEmpleado)
                    ->where('ALM_CMM_TipoAlmacenId', '=', "7AAE2221-3420-4B17-B8AC-EE53D8339D64")
                    ->where('ALM_Eliminado', '=', 0)
                    ->orderBy('ALM_Nombre')
                    ->get();
            }

            $almacenesIds = '';

            for($x=0; $x<count($almacenes); $x++){

                $almacenesIds = $almacenesIds . ($almacenesIds == "" ? "" : ", "). "'". $almacenes[$x]->ALM_AlmacenId."'";

            }

            return $almacenesIds;

        }

    }

    public static function getArrayIdsAlmacenesProductoEnProcesoPorEmpleadoLogueado(){

        $idEmpleado = DataBaseSession::getEmpleadoId();

        if(is_null($idEmpleado)){
            return null;
        }
        else {

            if(DataBaseSession::isPermisoCorporativo()){

                $almacenes = Almacenes::select('ALM_AlmacenId')
                    ->where('ALM_CMM_TipoAlmacenId', '=', "7AAE2221-3420-4B17-B8AC-EE53D8339D64")
                    ->where('ALM_Eliminado', '=', 0)
                    ->orderBy('ALM_Nombre')
                    ->get();

            }
            else {

                $almacenes = EmpleadosAlmacenes::select('ALM_AlmacenId')
                    ->join('Almacenes', 'ALM_AlmacenId', '=', 'EAL_ALM_AlmacenId')
                    ->where('EAL_EMP_EmpleadoId', '=', $idEmpleado)
                    ->where('ALM_CMM_TipoAlmacenId', '=', "7AAE2221-3420-4B17-B8AC-EE53D8339D64")
                    ->where('ALM_Eliminado', '=', 0)
                    ->orderBy('ALM_Nombre')
                    ->get();
            }


            $arrayAlmacenes = array();

            for($x=0; $x<count($almacenes); $x++){

                $arrayAlmacenes += ["'". $almacenes[$x]->ALM_AlmacenId."'"];

            }

            return $arrayAlmacenes;

        }

    }

    public static function getAlmacenesPorCediId()
    {

        if(isset($_COOKIE['muliix_session'])) {
            $valorCookie = $_COOKIE['muliix_session'];

            $arrayDatos = explode("|", $valorCookie);

            $idCedi = explode(":", $arrayDatos[COL_CEDI_ID])[1];

            $nombreCedi = \DB::table('Departamentos')
                ->where('DEP_DeptoId', '=', $idCedi)
                ->where('DEP_Eliminado', '=', 0)
                ->get()[0]->DEP_Nombre;

            $idsCedisMatriz = '';

            if(strpos($nombreCedi, 'Matriz') !== false){

                $cedisMatriz = \DB::select(\DB::raw("
                    select * from Departamentos
                    where DEP_Nombre like '%Matriz%'
                    and DEP_CMM_TipoDeptoId = '5845CCF9-23B9-41C7-B49F-A8495A7C4D08'
                    and DEP_Eliminado = 0"));

                for($x=0;$x<count($cedisMatriz);$x++){
                    $idsCedisMatriz .= $idsCedisMatriz == '' ? "'".$cedisMatriz[$x]->DEP_DeptoId."'" : ",'".$cedisMatriz[$x]->DEP_DeptoId."'";
                }

            }

            $almacenesCedi = \DB::select(\DB::raw("
                    select ALM_AlmacenId from Almacenes
                    inner join AlmacenesSucursales on ALMS_ALM_AlmacenId = ALM_AlmacenId
                    where ALM_Eliminado = 0
                    and ALMS_DEP_DeptoId IN (".($idsCedisMatriz == '' ? "'$idCedi'" : $idsCedisMatriz).")
                    GROUP BY ALM_AlmacenId"));

            $idAlmacenes = '';

            for($x=0;$x<count($almacenesCedi);$x++){
                $idAlmacenes .= $idAlmacenes == '' ? "'".$almacenesCedi[$x]->ALM_AlmacenId."'" : ",'".$almacenesCedi[$x]->ALM_AlmacenId."'";
            }


            return $idAlmacenes;
            //return $corporativo == 1 ? true : false;
        }
        else{
            return null;
        }
    }


    public static function home(){


        $usuarioId = DataBaseSession::getUsuarioId();

        $results = \DB::table('Usuarios')
            ->join('Empleados', 'EMP_EmpleadoId', '=', 'USU_EMP_EmpleadoId')
            ->leftJoin('Puestos', 'PUE_PuestoId', '=', 'EMP_PUE_PuestoId')
            ->leftJoin('Departamentos', 'DEP_DeptoId', '=', 'EMP_DEP_SucursalId')
            ->where('USU_UsuarioId', '=', $usuarioId)
            ->get();

        $isEmpaque = $results[0]->PUE_CMM_TabuladoresPuestosId == ControlesMaestrosMultiples::$ID_SUPERVISOR_EMPAQUE_TABULADOR_PUESTOS ? true : false;

        $arbolitoMenu = \DB::select(\DB::raw("
                    select * from MenuPrincipalConfiguracion INNER JOIN (select PER_PermisoId, PER_TipoPermiso, PERD_MPC_NodoId
                    from Permisos inner join PermisosDetalle on PER_PermisoId = PERD_PER_PermisoId
                    inner join Usuarios on USU_PER_PermisoId=PER_PermisoId WHERE USU_UsuarioId ='" . $usuarioId . "')
                    AS aaa ON MPC_NodoId = PERD_MPC_NodoId WHERE MPC_Movil = 0 AND MPC_Activo = 1 order by MPC_Nivel, MPC_Orden "));

        $sidebarPadre = array();
        $sidebarHijo = array();
        $sidebarNieto = array();
        $sidebarUltimo = array();
        $contadorPadre = 0;
        $contadorHijo = 0;
        $contadorNieto = 0;
        $contadorUltimo = 0;
        for ($i = 0; $i < count($arbolitoMenu); $i++) {
            //dd($arbolitoMenu[$i]);
            if ($arbolitoMenu[$i]->MPC_Nivel == "0") {
                $sidebarPadre[$contadorPadre] = $arbolitoMenu[$i];
                $contadorPadre = $contadorPadre + 1;
            }
            if ($arbolitoMenu[$i]->MPC_Nivel == "1") {
                $sidebarHijo[$contadorHijo] = $arbolitoMenu[$i];
                $contadorHijo = $contadorHijo + 1;
            }
            if ($arbolitoMenu[$i]->MPC_Nivel == "2") {
                $sidebarNieto[$contadorNieto] = $arbolitoMenu[$i];
                $contadorNieto = $contadorNieto + 1;

            } elseif ($arbolitoMenu[$i]->MPC_Nivel == "3") {
                $sidebarUltimo[$contadorUltimo] = $arbolitoMenu[$i];
                $contadorUltimo = $contadorUltimo + 1;
            }
        }

        $BD = DataBaseSession::getNombreDB();
        $nombreEmpleado = DataBaseSession::getNombreEmpleado();
        $cedi = $results[0]->DEP_Nombre .($results[0]->EMP_Corporativo != null && $results[0]->EMP_Corporativo == 1 ? " (Permiso Corporativo)" : "");

        return view('plantilla.masterAjax',
            compact('sidebarPadre', 'sidebarHijo', 'sidebarNieto',
                'sidebarUltimo', 'isEmpaque', 'BD', 'nombreEmpleado', 'cedi'));

    }


    public static function getUsuarioPassword(){

        $usuarioId = DataBaseSession::getUsuarioId();

        Config::set('database.connections.sqlsrv.database',  Request::input('DATA_BASE'));

        $results = Usuarios::find($usuarioId);

        $usuario = $results->USU_Nombre;
        $password = $results->USU_Contrasenia;

        return ['usuario' => $usuario, 'password' => $password];

    }

    public static function getLocalidadGeneralPorCediId($cediId){

        $idLocalidadGeneral = \DB::select(\DB::raw("
                    select LOC_LocalidadId,LOC_Nombre
                    from Almacenes
                    inner join AlmacenesSucursales on ALMS_ALM_AlmacenId = ALM_AlmacenId
                    inner join Localidades on LOC_ALM_AlmacenId = ALMS_ALM_AlmacenId
                    where
                    ALMS_DEP_DeptoId in ($cediId)
                    and LOC_General = 1
                    and LOC_Eliminado = 0
                    and ALM_Eliminado = 0
                    and LOC_LocalidadGeneral = 0
                    group by LOC_LocalidadId, LOC_Nombre"))[0]->LOC_LocalidadId;

        return $idLocalidadGeneral;

    }

    public static function getCediAsignadoId()
    {

        if(isset($_COOKIE['muliix_session'])) {
            $valorCookie = $_COOKIE['muliix_session'];

            $arrayDatos = explode("|", $valorCookie);

            $cediId = explode(":", $arrayDatos[COL_CEDI_ID])[1];

            return $cediId;
        }
        else{
            return null;
        }
    }


}