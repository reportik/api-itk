<?php namespace App\Http\Controllers\Sistema;
/**
 * Created by PhpStorm.
 * User: Juan
 * Date: 27/08/2016
 * Time: 12:24 AM
 */


class Conexion {
    
    private static $usuario = "sa";
    private static $contrasenia = "Itkc4224aQA";
    private static $puerto = "1433";
    private static $servidor = "itekniaapp.serveftp.com,1430";
    private static $base = "ItekniaDB";
    private static $characterSet = "UTF-8";
    private static $dateAsStrings = true;

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

    public static function cierraConexion($conexion) {
        sqlsrv_close($conexion);
    }

}
