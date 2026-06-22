<?php
class Categoria {
    protected $id_categoria;
    protected $nombre;
    protected $descripcion;
    protected $estado;

    public function __construct($nombre, $descripcion) {
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        
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