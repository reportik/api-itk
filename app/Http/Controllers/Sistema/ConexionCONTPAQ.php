<?php namespace App\Http\Controllers\Sistema;
/**
 */

class ConexionCONTPAQ {

    private static $usuario = "sa";
    private static $contrasenia ='';
    private static $puerto = "1433";
    private static $servidor = '';
    private static $base = '';
    private static $characterSet = "UTF-8";
    private static $dateAsStrings = true;
    private $conexion = null;
    
    public function __construct()
    {
        self::$contrasenia = env("CONT_DB_PASSWORD");
        self::$servidor = env("CONT_DB_HOST");
        self::$base = env("CONT_DB_DATABASE");
    }
    public static function getConexion(){
        $connectionInfo = array( "Database"=>self::$base, "UID"=>self::$usuario, "PWD"=>self::$contrasenia,
            "CharacterSet"=>self::$characterSet, "ReturnDatesAsStrings"=>self::$dateAsStrings, "MultipleActiveResultSets"=>'0');
        $conn = sqlsrv_connect( self::$servidor, $connectionInfo);
        if( $conn === false ) {
            if( ($errors = sqlsrv_errors() ) != null) {
                foreach( $errors as $error ) {
                    throw new \Exception($error[ 'message'], $error[ 'code']);
                }
            }
        }
        return $conn;
    }

    public static function _cierraConexion($conexion) {
        sqlsrv_close($conexion);
    }
    public function cierraConexion(){
        if(!sqlsrv_commit($this->conexion)){
            sqlsrv_close($this->conexion);
            $this->conexion = null;
        }
    }
    public function getArrayAsociativo($consulta)
    {
        try {
            /*ini_set('memory_limit', '-1');
            set_time_limit ( 0 ) ;*/

            $this->conexion = self::getConexion();
            $stmt = sqlsrv_query($this->conexion, $consulta);

            if ($stmt === false) {
                if (($errors = sqlsrv_errors()) != null) {
                    foreach ($errors as $error) {
                        throw new \Exception($error['message'], $error['code']);
                    }
                }
            }

            $rows = array();
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $rows[] = $row;
            }

            sqlsrv_free_stmt($stmt);
            self::cierraConexion();

            return $rows;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getArrayNumerico($consulta)
    {
        try {
            /*ini_set('memory_limit', '-1');
            set_time_limit ( 0 ) ;*/

            $this->conexion = self::getConexion();
            $stmt = sqlsrv_query($this->conexion, $consulta);

            if ($stmt === false) {
                if (($errors = sqlsrv_errors()) != null) {
                    foreach ($errors as $error) {
                        throw new \Exception($error['message'], $error['code']);
                    }
                }
            }

            $rows = array();
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_NUMERIC)) {
                $rows[] = $row;
            }

            sqlsrv_free_stmt($stmt);
            self::cierraConexion();

            return $rows;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getJson($consulta)
    {
        try {
            /*ini_set('memory_limit', '-1');
            set_time_limit ( 0 ) ;*/

            $this->conexion = self::getConexion();
            $stmt = sqlsrv_query($this->conexion, $consulta);

            if ($stmt === false) {
                if (($errors = sqlsrv_errors()) != null) {
                    foreach ($errors as $error) {
                        throw new \Exception($error['message'], $error['code']);
                    }
                }
            }

            $rows = array();
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_NUMERIC)) {
                $rows[] = $row;
            }

            sqlsrv_free_stmt($stmt);
            self::cierraConexion();

            return json_encode($rows);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getDataTable($consulta)
    {
        try {
            /*ini_set('memory_limit', '-1');
            set_time_limit ( 0 ) ;*/

            $this->conexion = self::getConexion();
            $stmt = sqlsrv_query($this->conexion, $consulta);

            if ($stmt === false) {
                if (($errors = sqlsrv_errors()) != null) {
                    foreach ($errors as $error) {
                        throw new \Exception($error['message'], $error['code']);
                    }
                }
            }

            $datos = array();
            $datos['data'] = array();
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $datos['data'][] = (object) $row;
            }

            sqlsrv_free_stmt($stmt);
            self::cierraConexion();

            $datos['options'] = array();

            return json_encode($datos);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getEjecutaConsulta($consulta)
    {
        try {
            /*ini_set('memory_limit', '-1');
            set_time_limit ( 0 ) ;*/

            $this->conexion = self::getConexion();
            $stmt = sqlsrv_query($this->conexion, $consulta);

            if ($stmt === false) {
                if (($errors = sqlsrv_errors()) != null) {
                    foreach ($errors as $error) {
                        throw new \Exception($error['message'], $error['code']);
                    }
                }
            }

            $rows = array();
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $rows[] = (object) $row;
            }

            sqlsrv_free_stmt($stmt);
            self::cierraConexion();

            return $rows;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
