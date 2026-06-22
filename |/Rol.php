<?php
class Rol {
    protected $id_rol;
    protected $nombre;

    public function __construct($nombre) {
        $this->nombre = $nombre;
    }

    public function __get($propiedad) { 
        if (property_exists($this, $propiedad)) return $this->$propiedad; return null; 
    }
    
    public function __set($propiedad, $valor) { 
        if (property_exists($this, $propiedad)) $this->$propiedad = $valor; 
    }
}
?>