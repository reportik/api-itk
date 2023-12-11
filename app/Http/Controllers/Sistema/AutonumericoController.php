<?php namespace App\Http\Controllers\Sistema;

use Illuminate\Support\Facades\Session;
use App\Mapeos\Controles\ControlesMaestros;
use App\Models\Autonumericos;
use Carbon\Carbon;

class AutonumericoController {
    public $siguiente;

    public function inserta($autonumerico){
        $autonumerico_tmp = new Autonumericos();
        $autonumerico_tmp->AUT_Nombre = $autonumerico->AUT_Nombre;
        $autonumerico_tmp->AUT_Siguiente = $autonumerico->AUT_Siguiente;
        $autonumerico_tmp->AUT_Activo = $autonumerico->AUT_Activo;
        $autonumerico_tmp->AUT_Prefijo = $autonumerico->AUT_Prefijo;
        $autonumerico_tmp->AUT_Ceros = $autonumerico->AUT_Ceros;
        $autonumerico_tmp->AUT_ReferenciaId = $autonumerico->AUT_ReferenciaId;
        $autonumerico_tmp->AUT_MPC_ModuloId = $autonumerico->AUT_MPC_ModuloId;
        $autonumerico_tmp->AUT_Tabla = $autonumerico->AUT_Tabla;
        $autonumerico_tmp->AUT_Campo = $autonumerico->AUT_Campo;
        $autonumerico_tmp->AUT_SUC_SucursalId = $autonumerico->AUT_SUC_SucursalId;
        $autonumerico_tmp->AUT_FechaUltimaModificacion = AutonumericoController::getFechaServidor();
        $autonumerico_tmp->AUT_EMP_ModificadoPor = $autonumerico->AUT_EMP_ModificadoPor;
        $autonumerico_tmp->save();

        return buscaPorNombre($autonumerico_tmp->AUT_Nombre);
    }

    public function eliminaPorNombre($autonumerico_nombre){
        Autonumericos::where('AUT_Nombre', '=', $autonumerico_nombre)->delete();
    }

    public function actualizaPorId($autonumerico){
        $autonumerico_tmp = Autonumericos::find($autonumerico->AUT_AutonumericoId);
        $autonumerico_tmp->AUT_Siguiente = $autonumerico->AUT_Siguiente;
        $autonumerico_tmp->AUT_FechaUltimaModificacion = AutonumericoController::getFechaServidor();//AutonumericoController::getFechaANSI(Carbon::now('America/Mexico_City')->toDateTimeString());

        $autonumerico_tmp->save();
    }

    public function actualizaPorNombre($autonumerico){
        $autonumerico_tmp = Autonumericos::where('AUT_Nombre', '=',$autonumerico->AUT_Nombre);
        $autonumerico_tmp->AUT_Nombre = $autonumerico->AUT_Nombre;
        $autonumerico_tmp->AUT_Siguiente = $autonumerico->AUT_Siguiente;
        $autonumerico_tmp->AUT_Activo = $autonumerico->AUT_Activo;
        $autonumerico_tmp->AUT_Prefijo = $autonumerico->AUT_Prefijo;
        $autonumerico_tmp->AUT_Ceros = $autonumerico->AUT_Ceros;
        $autonumerico_tmp->AUT_ReferenciaId = $autonumerico->AUT_ReferenciaId;
        $autonumerico_tmp->AUT_MPC_ModuloId = $autonumerico->AUT_MPC_ModuloId;
        $autonumerico_tmp->AUT_Tabla = $autonumerico->AUT_Tabla;
        $autonumerico_tmp->AUT_Campo = $autonumerico->AUT_Campo;
        $autonumerico_tmp->AUT_SUC_SucursalId = $autonumerico->AUT_SUC_SucursalId;
        $autonumerico_tmp->AUT_FechaUltimaModificacion = AutonumericoController::getFechaServidor();//AutonumericoController::getFechaANSI(Carbon::now('America/Mexico_City')->toDateTimeString());
        $autonumerico_tmp->AUT_EMP_ModificadoPor = $autonumerico->AUT_EMP_ModificadoPor;
        $autonumerico_tmp->save();
    }

    public function buscaPorId($autonumerico_id){
        $autonumerico_tmp = Autonumericos::find($autonumerico_id);
        return $autonumerico_tmp;
    }

    public function buscaPorNombre($autonumerico_nombre){
        $autonumerico_tmp = Autonumericos::where('AUT_Nombre', '=',$autonumerico_nombre)->get();
        return $autonumerico_tmp;
    }

    public function isAutonumericoActivo($autonumerico_nombre){
        $autonumerico_tmp = AutonumericoController::buscaPorNombre($autonumerico_nombre);
        if( $autonumerico_tmp != null ) {
            return $autonumerico_tmp[0]->AUT_Activo;
        }
        return false;
    }

    public function isAutonumericoActivoPorId($id){
        $autonumerico_tmp = AutonumericoController::buscaPorId($id);
        if( $autonumerico_tmp != null ) {
            return $autonumerico_tmp->AUT_Activo;
        }
        return false;
    }

    public function isAutonumericoActivoPorReferenciaId($autonumerico_nombre, $referenciaId){
        $autonumerico_tmp = AutonumericoController::buscaPorReferenciaId($autonumerico_nombre, $referenciaId);
        if( $autonumerico_tmp != null ) {
            return $autonumerico_tmp[0]->AUT_Activo;
        }
        return false;
    }

    public function getSiguienteAutonumerico($autonumerico_nombre){
        $siguiente_codigo = '';
        $codigo = '';
        $an_dao = new AutonumericoController();
        $autonumerico_tmp = buscaPorNombre($autonumerico_nombre);
        if($autonumerico_tmp != null){
            do {
                $siguiente_codigo = $autonumerico_tmp.getSiguienteCodigo();
                $codigo = \DB::select(\DB::raw("SELECT $autonumerico_tmp->AUT_Campo FROM $autonumerico_tmp->AUT_Tabla WHERE $autonumerico_tmp->AUT_Campo = $siguiente_codigo"));
                $an_dao->actualizaPorId($autonumerico_tmp);
            } while (!empty($codigo));
            return $siguiente_codigo;
        }
        return null;
    }

    public function getSiguienteAutonumericoPorReferenciaId($autonumerico_nombre, $referenciaId){
        $siguiente_codigo = '';
        $codigo = '';
        $an_dao = new AutonumericoController();
        $autonumerico_tmp = buscaPorReferenciaId($autonumerico_nombre, $referenciaId);
        if($autonumerico_tmp != null){
            do {
                $siguiente_codigo = $autonumerico_tmp.getSiguienteCodigo();
                $codigo = \DB::select(\DB::raw("SELECT $autonumerico_tmp->AUT_Campo FROM $autonumerico_tmp->AUT_Tabla WHERE $autonumerico_tmp->AUT_Campo = $siguiente_codigo"));
                $an_dao->actualizaPorId($autonumerico_tmp);
            } while (!empty($codigo));
            return $siguiente_codigo;
        }
        return null;
    }

    public function getAutonumericoN($autonumerico, $referenciaId){
        try{
            $consulta = "
						DECLARE @autonumericoId UNIQUEIDENTIFIER,
								@referenciaId UNIQUEIDENTIFIER,
								@nombre NVARCHAR(50) ";

            if($referenciaId != null){
                $consulta .= " SELECT
							@autonumericoId = AUT_AutonumericoId,
							@nombre = AUT_Nombre,
							@referenciaId = AUT_SUC_SucursalId
						FROM Autonumericos
						WHERE AUT_Activo = 1
							  AND AUT_SUC_SucursalId IN ($referenciaId)
							  AND AUT_Nombre = '$autonumerico'

						IF @autonumericoId IS NULL
							BEGIN
								SELECT
									@autonumericoId = AUT_AutonumericoId,
									@nombre = AUT_Nombre,
									@referenciaId = AUT_SUC_SucursalId
								FROM Autonumericos
								WHERE AUT_Activo = 1
									  AND AUT_SUC_SucursalId IS NULL
									  AND AUT_Nombre = '$autonumerico'
							END ";
            } else {
                $consulta .= "
							SELECT
								@autonumericoId = AUT_AutonumericoId,
								@nombre = AUT_Nombre,
								@referenciaId = AUT_SUC_SucursalId
							FROM Autonumericos
							WHERE AUT_Activo = 1
								  AND AUT_SUC_SucursalId IS NULL
								  AND AUT_Nombre = '$autonumerico' ";
            }

            $consulta .= " SELECT
							@autonumericoId AS AUT_AutonumericoId,
							@nombre AS AUT_Nombre,
							@referenciaId AS AUT_ReferenciaId ";

            $resultSet = \DB::select(\DB::raw($consulta));

            $auto = new Autonumericos();
            $auto->AUT_AutonumericoId = $resultSet[0]->AUT_AutonumericoId;
            $auto->AUT_Nombre = $resultSet[0]->AUT_Nombre;
            $auto->AUT_ReferenciaId = $resultSet[0]->AUT_ReferenciaId;

            return $auto;
        } catch(\Exception $e){
            throw $e;
        }
    }

    public function getSerieN($autonumerico, $referenciaId){
        try{
            $consulta = " DECLARE @serie NVARCHAR(4) ";

            if($referenciaId != null){
                $consulta .= "
							SELECT
								@serie = AUT_Prefijo
							FROM Autonumericos
							WHERE AUT_Activo = 1
								  AND AUT_SUC_SucursalId = '$referenciaId'
								  AND AUT_Nombre = '$autonumerico'

							IF @serie IS NULL
								BEGIN
									SELECT
										@serie = AUT_Prefijo
									FROM Autonumericos
									WHERE AUT_Activo = 1
										  AND AUT_SUC_SucursalId IS NULL
										  AND AUT_Nombre = '$autonumerico'
								END ";
            } else {
                $consulta .= "
								SELECT
									@serie = AUT_Prefijo
								FROM Autonumericos
								WHERE AUT_Activo = 1
									  AND AUT_SUC_SucursalId IS NULL
									  AND AUT_Nombre = '$autonumerico' ";
            }


            $consulta .= " SELECT @serie AS SERIE ";

            $resultSet = \DB::select(\DB::raw($consulta));

            return $resultSet[0]->SERIE;
        } catch(\Exception $e){
            throw $e;
        }
    }

    public function getAutonumerico($clienteId, $autonumerico_nombre, $empleadoId, $rutaId = null)
    {
        $query = "DECLARE @AutonumericoId UNIQUEIDENTIFIER, @Nombre NVARCHAR(50), @SucursalId UNIQUEIDENTIFIER,
                          @referenciaId UNIQUEIDENTIFIER, @departamentoId UNIQUEIDENTIFIER ";
        if ($empleadoId != null) {
            if($rutaId == null) {
                $query .= " SET @referenciaId = NULL";
            } else {
                $query .= " SET @referenciaId = '$rutaId'";
            }

            $query .= " IF @referenciaId IS NULL
				BEGIN
						SET @referenciaId = (SELECT RUT_RutaId
														FROM Rutas
														WHERE RUT_EMP_VendedorId = '".$empleadoId."'
														AND RUT_Activo = 1 AND RUT_Eliminado = 0 AND RUT_ActivoFolioFactura = 1)

								   SELECT @AutonumericoId = AUT_AutonumericoId, @Nombre = AUT_Nombre, @SucursalId = AUT_SUC_SucursalId
								   FROM Autonumericos
								   WHERE AUT_Nombre = '".$autonumerico_nombre."' AND AUT_SUC_SucursalId = @referenciaId AND AUT_Activo = 1
				END

					   IF @AutonumericoId IS NULL
					   	BEGIN
						   SET @referenciaId = ( SELECT DISTINCT EC_ReferenciaId
					                             FROM dbo.getPadresEstructuraComercialPorReferencia ( @referenciaId )
												 WHERE TipoComponente = 'CEDIS')

						   SELECT @AutonumericoId = AUT_AutonumericoId, @Nombre = AUT_Nombre, @SucursalId = AUT_SUC_SucursalId
						   FROM Autonumericos
						   WHERE AUT_Nombre = '".$autonumerico_nombre."' AND AUT_SUC_SucursalId = @referenciaId AND AUT_Activo = 1
						END ";
        } else {
            $query .= "SET @AutonumericoId = NULL";
        }

        if ($clienteId != null) {
            $query .= " IF @AutonumericoId IS NULL
                            BEGIN";

            if($rutaId == null) {
                $query .= " SET @referenciaId = NULL";
            } else {
                $query .= " SET @referenciaId = '$rutaId'";
            }

            $query .= " IF @referenciaId IS NULL
									BEGIN
											SET @referenciaId = (SELECT dbo.getRutaActualPorClienteId('$clienteId'))
									END

                                SELECT @AutonumericoId = AUT_AutonumericoId, @Nombre = AUT_Nombre, @SucursalId = AUT_SUC_SucursalId
                                FROM Autonumericos
                                WHERE AUT_Nombre = '".$autonumerico_nombre."' AND AUT_SUC_SucursalId = @referenciaId AND AUT_Activo = 1

								IF @AutonumericoId IS NULL
									BEGIN
										SET @referenciaId = ( SELECT DISTINCT EC_ReferenciaId
																FROM dbo.getPadresEstructuraComercialPorReferencia ( @referenciaId )
																WHERE TipoComponente = 'CEDIS')

										SELECT @AutonumericoId = AUT_AutonumericoId, @Nombre = AUT_Nombre, @SucursalId = AUT_SUC_SucursalId
										FROM Autonumericos
										WHERE AUT_Nombre = '".$autonumerico_nombre."' AND AUT_SUC_SucursalId = @referenciaId AND AUT_Activo = 1
									END
                            END";
        }
        $query .= "
                    IF @AutonumericoId IS NULL
                     BEGIN
                         SELECT @AutonumericoId = AUT_AutonumericoId, @Nombre = AUT_Nombre, @SucursalId = AUT_SUC_SucursalId
                         FROM Autonumericos
                         WHERE AUT_Nombre = '".$autonumerico_nombre."' AND AUT_Activo = 1 AND AUT_SUC_SucursalId IS NULL
                    END

                    SELECT @AutonumericoId AUT_AutonumericoId, @Nombre AS AUT_Nombre, @SucursalId AS AUT_SUC_SucursalId";

        $consulta =
            \DB::select(\DB::raw($query));

        $autonumerico = new Autonumericos();
        $autonumerico->AUT_AutonumericoId = $consulta[0]->AUT_AutonumericoId;
        $autonumerico->AUT_Nombre = $consulta[0]->AUT_Nombre;
        $autonumerico->AUT_ReferenciaId = $consulta[0]->AUT_SUC_SucursalId;

        return $autonumerico;
    }

    public function getSerie($clienteId, $empleadoId, $rutaId = null){
        $query = "DECLARE @serie NVARCHAR(10),
		                  @referenciaId UNIQUEIDENTIFIER ";
        if ($empleadoId != null) {
            if($rutaId == null) {
                $query .= " SET @referenciaId = NULL";
            } else {
                $query .= " SET @referenciaId = '$rutaId'";
            }

            $query .= " IF @referenciaId IS NULL
				BEGIN
						SET @referenciaId = (SELECT RUT_RutaId
                                            FROM Rutas
                                            WHERE RUT_EMP_VendedorId = '".$empleadoId."'
                       						AND RUT_Activo = 1 AND RUT_Eliminado = 0 AND RUT_ActivoFolioFactura = 1)
				END

					   SELECT @serie = RUT_PrefijoFolioFactura
					   FROM Rutas
					   WHERE RUT_RutaId = @referenciaId AND RUT_Activo = 1 AND RUT_Eliminado = 0 AND RUT_ActivoFolioFactura = 1

					   IF @serie IS NULL
					   	BEGIN
						   SET @referenciaId = ( SELECT DISTINCT EC_ReferenciaId
					                             FROM dbo.getPadresEstructuraComercialPorReferencia ( @referenciaId )
												 WHERE TipoComponente = 'CEDIS')

					       SELECT @serie = DEPD_AcronimoFactura
					       FROM Departamentos
					       INNER JOIN DepartamentosDatos ON DEP_DeptoId = DEPD_DEP_DeptoId
					       WHERE DEP_Eliminado = 0 AND DEP_Activo = 1 AND DEP_DeptoId = @referenciaId
						END ";
        } else {
            $query .= "SET @serie = NULL";
        }

        if ($clienteId != null) {
            $query .= " IF @serie IS NULL
                            BEGIN";

            if($rutaId == null) {
                $query .= " SET @referenciaId = NULL";
            } else {
                $query .= " SET @referenciaId = '$rutaId'";
            }

            $query .= " IF @referenciaId IS NULL
									BEGIN
											SET @referenciaId = (SELECT dbo.getRutaActualPorClienteId('$clienteId'))
									END

								SELECT @serie = RUT_PrefijoFolioFactura
								FROM Rutas
								WHERE RUT_RutaId = @referenciaId AND RUT_Activo = 1 AND RUT_Eliminado = 0 AND RUT_ActivoFolioFactura = 1

								IF @serie IS NULL
									BEGIN
										SET @referenciaId = ( SELECT DISTINCT EC_ReferenciaId
																FROM dbo.getPadresEstructuraComercialPorReferencia ( @referenciaId )
																WHERE TipoComponente = 'CEDIS')

										SELECT @serie = DEPD_AcronimoFactura
										FROM Departamentos
										INNER JOIN DepartamentosDatos ON DEP_DeptoId = DEPD_DEP_DeptoId
										WHERE DEP_Eliminado = 0 AND DEP_Activo = 1 AND DEP_DeptoId = @referenciaId
									END
                            END";
        }
        $query .= "
                    IF @serie IS NULL
                     BEGIN
						SELECT @serie = CONVERT(NVARCHAR(10), CMA_Valor) FROM ControlesMaestros WHERE  CMA_Control  = 'CMA_CCNF_CFDISerie'
                     END

                    SELECT @serie AS SERIE ";

        $consulta =
            \DB::select(\DB::raw($query));

        return $consulta[0]->SERIE;
    }

    public function buscaPorReferenciaId($autonumerico_nombre, $referenciaId){
        $referenciaId = ($referenciaId == null ? " IS NULL " : " = '" . $referenciaId . "'");
        $autonumerico_tmp = \DB::select(\DB::raw("SELECT *
									              FROM Autonumericos
									              WHERE AUT_Nombre = '$autonumerico_nombre' AND AUT_ReferenciaId $referenciaId"));
        return $autonumerico_tmp;
    }

    public function getSiguienteAutonumericoPorId($id){
        $siguiente_codigo = '';
        $codigo = '';
        $an_dao = new AutonumericoController();
        $autonumerico_tmp = AutonumericoController::buscaPorId($id);
        if($autonumerico_tmp != null){
            do {
                $siguiente_codigo = AutonumericoController::getSiguienteCodigo($autonumerico_tmp);
                $codigo = \DB::select(\DB::raw("SELECT $autonumerico_tmp->AUT_Campo FROM $autonumerico_tmp->AUT_Tabla WHERE $autonumerico_tmp->AUT_Campo = '".$siguiente_codigo."'"));
                $autonumerico_tmp->AUT_Siguiente = $this->siguiente;
                $an_dao->actualizaPorId($autonumerico_tmp);
            } while (!empty($codigo));
            return $siguiente_codigo;
        }
        return null;
    }

    public function getSiguienteCodigo($autonumerico){
        $codigo = AutonumericoController::getSiguienteCodigo_(true, $autonumerico);
        return $codigo;
    }

    public function getSiguienteCodigo_($incrementa, $autonumerico){
        $codigo = (($autonumerico->AUT_Prefijo == null) ? "" : $autonumerico->AUT_Prefijo) . str_pad($autonumerico->AUT_Siguiente, $autonumerico->AUT_Ceros, '0', STR_PAD_LEFT);
        if($incrementa){
            $this->siguiente = $autonumerico->AUT_Siguiente + 1;
        }
        return $codigo;
    }

    public function getSiguienteCodigosinIncrementa_($autonumerico){
        $codigo = (($autonumerico->AUT_Prefijo == null) ? "" : $autonumerico->AUT_Prefijo) . str_pad($autonumerico->AUT_Siguiente, $autonumerico->AUT_Ceros, '0',STR_PAD_LEFT);
        return $codigo;
    }

    public function getSiguienteAutonumericoporIdsinIncrementa($id){
        $siguiente_codigo = '';
        $codigo = '';
        $an_dao = new AutonumericoController();
        $autonumerico_tmp = AutonumericoController::buscaPorId($id);
        if($autonumerico_tmp != null){
            do {
                $siguiente_codigo = AutonumericoController::getSiguienteCodigosinIncrementa_($autonumerico_tmp);
                $codigo = \DB::select(\DB::raw("SELECT $autonumerico_tmp->AUT_Campo FROM $autonumerico_tmp->AUT_Tabla WHERE $autonumerico_tmp->AUT_Campo = '".$siguiente_codigo."'"));
                $autonumerico_tmp->AUT_Siguiente = $this->siguiente;
            } while (!empty($codigo));
            return $siguiente_codigo;
        }
        return null;
    }

    /**
     * Método que convierte la fecha de formato dd/mm/yyyy a formato ansi yyyymmdd
     * @param fecha - La fecha en formato dd/mm/yyyy
     * @return - La fecha en formato ansi yyyymmdd
     */
    public function getFechaANSI($fecha) { //  dd/mm/yyy
        $elem = explode("-", $fecha);//17/03/2009
        return $elem[0].$elem[1].$elem[2]; //20090317
    }

    public  function getFechaServidor() {
        $hora = \DB::select(\DB::raw(" SELECT CONVERT(VARCHAR(8), GETDATE(), 112) + ' ' + CONVERT(VARCHAR(8), CAST(GETDATE() AS TIME)) AS FECHA "));
        return $hora[0]->FECHA;
    }
}