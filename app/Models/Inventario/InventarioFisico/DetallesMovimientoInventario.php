<?php namespace App\Models\Inventario\InventarioFisico;

//use Illuminate\Database\Eloquent\Model;
//use App\Models\Lotes;

class DetallesMovimientoInventario {

    /** Id del almacen */
    private $idAlmacen;
    /** Objeto modelo de tipo Localidad */
    private $localidad;
    /** Objeto modelo de tipo Lote */
    private  $lote;
    /** Cantidad que se mueve en el inventario */
    private $cantidadTransferir;

    function _construct(){}

    function DetallesMovimientoInventario($idAlmacen,$localidad,$lote,$cantidadTransferir){
        $this->idAlmacen=$idAlmacen;
        $this->lote=$lote;
        $this->localidad=$localidad;
        $this->cantidadTransferir=$cantidadTransferir;
    }

    /**
     * Conocer la cantidad que se mueve en el inventario
     * @return cantidad
     */
    public function getCantidadTransferir() {
        return $this->cantidadTransferir;
    }

    /**
     * Asignar la cantidad que se mueve en el inventario
     * @param cantidadTransferir
     */
    public function  setCantidadTransferir($cantidadTransferir) {
        $this->cantidadTransferir = $cantidadTransferir;
    }

    /**
     * Conocer el id del almacen que se afectara en el inventario
     * @return Id del almacen
     */
    public function getIdAlmacen() {
        return $this->idAlmacen;
    }

    /**
     * Asignar el id del almacen que se afectara en el inventario
     * @param idAlmacen - Id del almacen
     */
    public function setIdAlmacen($idAlmacen) {
        $this->idAlmacen = $idAlmacen;
    }

    /**
     * Conocer el objeto de modelo de tipo Localidad que se afectara en el inventario
     * @return Objeto de tipo Localidad
     */
    public function getLocalidad() {
        return $this->localidad;
    }

    public function setLocalidad($localidad) {
        $this->localidad = $localidad;
    }

    public function getLote() {
        return $this->lote;
    }

    public function setLote($lote) {
        $this->lote = $lote;
    }
}
