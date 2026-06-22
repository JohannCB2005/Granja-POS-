<?php
class Insumo {
    protected $id_insumo;
    protected $id_categoria;
    protected $nombre;
    protected $precio_unitario;
    protected $stock;
    protected $estado;

    public function __construct($id_categoria, $nombre, $precio_unitario) {
        $this->id_categoria = $id_categoria;
        $this->nombre = $nombre;
        $this->precio_unitario = $precio_unitario;
        
        $this->stock = 0;
        $this->estado = 1;
    }

    public function __get($propiedad) { 
        if (property_exists($this, $propiedad)) return $this->$propiedad; return null; 
    }

    public function __set($propiedad, $valor) { 
        if (property_exists($this, $propiedad)) $this->$propiedad = $valor; 
    }
}
?>S