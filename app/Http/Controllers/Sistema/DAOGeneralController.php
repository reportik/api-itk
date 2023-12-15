<?php namespace App\Http\Controllers\Sistema;
use App\Http\Controllers\Controller;

/**
 * Created by PhpStorm.
 * User: Juan
 * Date: 27/08/2016
 * Time: 12:16 AM
 * Version: 1.00.00
 */

class DAOGeneralController extends Controller {

    private $conexion = null;

    public function getArrayAsociativo($consulta) {
        try{
            /*ini_set('memory_limit', '-1');
            set_time_limit ( 0 ) ;*/

            $this->conexion = self::getConexion();
            $stmt = sqlsrv_query($this->conexion, $consulta);

            if( $stmt === false ) {
                if( ($errors = sqlsrv_errors() ) != null) {
                    foreach( $errors as $error ) {
                        throw new \Exception($error[ 'message'], $error[ 'code']);
                    }
                }
            }

            $rows = array();
            while( $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC) ) {
                $rows[] = $row;
            }

            sqlsrv_free_stmt($stmt);
            self::cierraConexion();

            return $rows;
        }catch (\Exception $e){
            throw $e;
        }
    }

    public function getArrayNumerico($consulta) {
        try{
            /*ini_set('memory_limit', '-1');
            set_time_limit ( 0 ) ;*/

            $this->conexion = self::getConexion();
            $stmt = sqlsrv_query($this->conexion, $consulta);

            if( $stmt === false ) {
                if( ($errors = sqlsrv_errors() ) != null) {
                    foreach( $errors as $error ) {
                        throw new \Exception($error[ 'message'], $error[ 'code']);
                    }
                }
            }

            $rows = array();
            while( $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_NUMERIC) ) {
                $rows[] = $row;
            }

            sqlsrv_free_stmt($stmt);
            self::cierraConexion();

            return $rows;
        }catch (\Exception $e){
            throw $e;
        }
    }

    public function getJson($consulta){
        try{
            /*ini_set('memory_limit', '-1');
            set_time_limit ( 0 ) ;*/

            $this->conexion = self::getConexion();
            $stmt = sqlsrv_query($this->conexion, $consulta);

            if( $stmt === false ) {
                if( ($errors = sqlsrv_errors() ) != null) {
                    foreach( $errors as $error ) {
                        throw new \Exception($error[ 'message'], $error[ 'code']);
                    }
                }
            }

            $rows = array();
            while( $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_NUMERIC) ) {
                $rows[] = $row;
            }

            sqlsrv_free_stmt($stmt);
            self::cierraConexion();

            return json_encode($rows);
        }catch (\Exception $e){
            throw $e;
        }
    }

    public function getDataTable($consulta){
        try{
            /*ini_set('memory_limit', '-1');
            set_time_limit ( 0 ) ;*/

            $this->conexion = self::getConexion();
            $stmt = sqlsrv_query($this->conexion, $consulta);

            if( $stmt === false ) {
                if( ($errors = sqlsrv_errors() ) != null) {
                    foreach( $errors as $error ) {
                        throw new \Exception($error[ 'message'], $error[ 'code']);
                    }
                }
            }

            $datos = array();
            $datos['data'] = array();
            while( $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC) ) {
                $datos['data'][] = (Object) $row;
            }

            sqlsrv_free_stmt($stmt);
            self::cierraConexion();

            $datos['options'] = array();

            return json_encode($datos);
        }catch (\Exception $e){
            throw $e;
        }
    }

    public function getEjecutaConsulta($consulta) {
        try{
            /*ini_set('memory_limit', '-1');
            set_time_limit ( 0 ) ;*/

            $rows = \DB::select($consulta);

            return $rows;
        }catch (\Exception $e){
            throw $e;
        }
    }

    public function getList($consulta, $id, $value) {
        try{
            /*ini_set('memory_limit', '-1');
            set_time_limit ( 0 ) ;*/

            $this->conexion = self::getConexion();
            $stmt = sqlsrv_query($this->conexion, $consulta);

            if( $stmt === false ) {
                if( ($errors = sqlsrv_errors() ) != null) {
                    foreach( $errors as $error ) {
                        throw new \Exception($error[ 'message'], $error[ 'code']);
                    }
                }
            }

            $rows = array();
            while( $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC) ) {
                $rows[$row[$id]] = $row[$value];
            }

            sqlsrv_free_stmt($stmt);
            self::cierraConexion();

            return $rows;
        }catch (\Exception $e){
            throw $e;
        }
    }

    public function nuevoId()
    {
        try{
            $resultSet = \DB::select("SELECT NEWID() AS ID");

            return $resultSet[0]->ID;
        }catch (\Exception $e){
            throw $e;
        }
    }

    public function uuid() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
    
          // 32 bits for "time_low"
          mt_rand(0, 0xffff), mt_rand(0, 0xffff),
    
          // 16 bits for "time_mid"
          mt_rand(0, 0xffff),
    
          // 16 bits for "time_hi_and_version",
          // four most significant bits holds version number 4
          mt_rand(0, 0x0fff) | 0x4000,
    
          // 16 bits, 8 bits for "clk_seq_hi_res",
          // 8 bits for "clk_seq_low",
          // two most significant bits holds zero and one for variant DCE1.1
          mt_rand(0, 0x3fff) | 0x8000,
    
          // 48 bits for "node"
          mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
      }

    public function getFechaHoraServidor()
    {
        try{
            $resultSet = \DB::select("SELECT GETDATE() AS FECHA");

            return $resultSet[0]->FECHA;
        }catch (\Exception $e){
            throw $e;
        }
    }

    public function getHoraServidor()
    {
        try{
            /*ini_set('memory_limit', '-1');
            set_time_limit ( 0 );*/

            //date_default_timezone_set('America/Mexico_City');
            $hoy = date("H:i:s");
            return $hoy;
        }catch (\Exception $e){
            throw $e;
        }
    }

    public function getConexion()
    {
        if($this->conexion == null){
            $this->conexion = Conexion::getConexion();
        }
        return $this->conexion;
    }

    public function cierraConexion(){
        if(!sqlsrv_commit($this->conexion)){
            sqlsrv_close($this->conexion);
            $this->conexion = null;
        }
    }

    public function beginTransaction(){
        $this->conexion = null;
        $this->conexion = self::getConexion();
    }

    public function commitTransaction(){
        sqlsrv_commit( $this->conexion );
    }

    public function rollbackTransaction(){
        sqlsrv_rollback( $this->conexion );
    }

    public function ejecutaConsulta($consulta) {
        try{
            /*ini_set('memory_limit', '-1');
            set_time_limit ( 0 ) ;*/

            $this->conexion = self::getConexion();
            $stmt = sqlsrv_query($this->conexion, $consulta);

            if( $stmt === false ) {
                if( ($errors = sqlsrv_errors() ) != null) {
                    foreach( $errors as $error ) {
                        throw new \Exception($error[ 'message'], $error[ 'code']);
                    }
                }
            }

            $rows = array();

            do {
                while( $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC) ) {
                    $rows[] = (Object) $row;
                }
            } while (sqlsrv_next_result($stmt));

            sqlsrv_free_stmt($stmt);
            self::cierraConexion();

            return $rows;
        }catch (\Exception $e){
            throw $e;
        }
    }

    public function getFechaHoraServidorANSI() {
        //date_default_timezone_set('America/Mexico_City');
        $hoy = date("Ymd H:i:s");
        return $hoy;
    }

    public function idEnUso($id) {
        try {
            $consulta = "
                    SET NOCOUNT ON;
            
                    DECLARE 
                          @I INT
                        , @FILA_I INT = 1
                        , @NOMBRE_TABLA NVARCHAR(100)
                        , @NOMBRE_COLUMNA NVARCHAR(100)
                        , @ELIMINADO NVARCHAR(50)
                        , @ACTIVO NVARCHAR(50)
                        , @CONSULTA NVARCHAR(MAX)
                        , @CRITERIO NVARCHAR(MAX)
                        , @EN_USO BIT = 0 
                        , @ROWCOUNT INT
            
                    DECLARE @TABLAS TABLE (
                          NOMBRE_TABLA NVARCHAR(100)
                        , FILA INT
                    )
            
                    INSERT @TABLAS
            
                    SELECT TABLE_NAME, ROW_NUMBER() OVER(ORDER BY TABLE_NAME)
                    FROM INFORMATION_SCHEMA.TABLES 
            
                    SET @I = @@ROWCOUNT
            
                    WHILE @FILA_I <= @I
                    BEGIN
                        SET @CRITERIO = ''
                        SET @NOMBRE_TABLA = ''
                        SET @NOMBRE_COLUMNA = ''
                        SET @ACTIVO = ''
                        SET @ELIMINADO = ''
            
                        SELECT @NOMBRE_TABLA = NOMBRE_TABLA
                        FROM @TABLAS
                        WHERE FILA = @FILA_I
            
                        SELECT @NOMBRE_COLUMNA = COLUMN_NAME
                        FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_NAME = @NOMBRE_TABLA AND ORDINAL_POSITION = 1 AND DATA_TYPE = 'uniqueidentifier'
            
                        IF @NOMBRE_COLUMNA IS NOT NULL AND @NOMBRE_COLUMNA <> ''
                        BEGIN
                    
                                SELECT @ACTIVO = COLUMN_NAME
                                FROM INFORMATION_SCHEMA.COLUMNS
                                WHERE TABLE_NAME = @NOMBRE_TABLA AND (COLUMN_NAME LIKE '%Activo%' OR COLUMN_NAME LIKE '%Activa%')
            
                                SELECT @ELIMINADO = COLUMN_NAME
                                FROM INFORMATION_SCHEMA.COLUMNS
                                WHERE TABLE_NAME = @NOMBRE_TABLA AND (COLUMN_NAME LIKE '%Eliminado%' OR COLUMN_NAME LIKE '%Eliminada%' OR COLUMN_NAME LIKE '%Borrado%' OR COLUMN_NAME LIKE '%Borrada%')
            
                                IF @ACTIVO IS NOT NULL AND @ACTIVO <> ''
                                BEGIN
                                        SET @CRITERIO = ' AND ' + @ACTIVO + ' = 1 '
                                END
                
                                IF @ELIMINADO IS NOT NULL AND @ELIMINADO <> ''
                                BEGIN
                                        SET @CRITERIO += ' AND ' + @ELIMINADO + ' = 0 '
                                END
            
                                SET @CONSULTA = 'SELECT ' + @NOMBRE_COLUMNA + ' FROM ' + @NOMBRE_TABLA + ' WHERE ' + @NOMBRE_COLUMNA + ' = ''' + '$id' + ''' ' + @CRITERIO
            
                                EXEC (@CONSULTA)
            
                                IF @@ROWCOUNT > 0
                                BEGIN
                                    SET @EN_USO = 1 
                                    BREAK
                                END
            
                        END
            
                        SET @FILA_I += 1;
                    END
            
                    SELECT @EN_USO
            ";

            $resultSet = $this->getEjecutaConsulta($consulta);

            dd($resultSet);
        } catch (\Exception $e) {
            throw $e;
        }
    }

}