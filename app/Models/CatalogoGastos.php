<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogoGastos extends Model{

    protected $table = 'CatalogoGastos';

    protected $primaryKey = 'CAG_CatalogoGastoId';

    public $timestamps = false;

    public $incrementing = false;

    /**
     * Permite eliminar logicamente un registro
     * a través del identificador del catalogo de gastos.
     *
     * @author Juan Gómez Gálvez
     * @param string identificador del catalogo
     */
    public static function eliminaPorId($catalogoId, $empleadoId) {
        \DB::statement("
        UPDATE CatalogoGastos
        SET CAG_Eliminado = 1, CAG_EMP_ModificadoPorId = '$empleadoId'
        WHERE CAG_CatalogoGastoId = '$catalogoId'");
    }
} 