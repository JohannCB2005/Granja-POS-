<?php
class Insumo {
    public $id_insumo;
    public $id_categoria;
    public $id_unidad;
    public $nombre;
    public $precio_unitario;
    public $stock;
    public $estado;

    public function __construct($id_categoria = null, $id_unidad = null, $nombre = '', $precio_unitario = 0.0, $stock = 0.0, $id_insumo = null) {
        $this->id_insumo      = $id_insumo;
        $this->id_categoria   = $id_categoria;
        $this->id_unidad      = $id_unidad;
        $this->nombre         = $nombre;
        $this->precio_unitario= $precio_unitario;
        $this->stock          = $stock;
        $this->estado         = 1;
    }
}
?>
