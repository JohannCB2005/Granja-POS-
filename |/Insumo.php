<?php
class Insumo {
    protected $id_insumo;
    protected $id_categoria;
    protected $id_unidad;
    protected $nombre;
    protected $precio_unitario;
    protected $stock;
    protected $estado;

    public function __construct($id_categoria, $id_unidad, $nombre, $precio_unitario, $stock = 0) {
        $this->id_categoria = $id_categoria;
        $this->id_unidad = $id_unidad;
        $this->nombre = $nombre;
        $this->precio_unitario = $precio_unitario;
        $this->stock = $stock;
        $this->estado = 1;
    }

    public function __get($propiedad) { 
        if (property_exists($this, $propiedad)) return $this->$propiedad; return null; 
    }

    public function __set($propiedad, $valor) { 
        if (property_exists($this, $propiedad)) $this->$propiedad = $valor; 
    }
}
?>