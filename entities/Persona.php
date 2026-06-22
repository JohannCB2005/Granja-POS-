<?php
abstract class Persona {
    protected $id_persona;
    protected $tipo_documento;
    protected $numero_documento;
    protected $nombres_razon_social;
    protected $apellidos;
    protected $direccion;
    protected $telefono;
    protected $estado;

    public function __construct($tipo_documento, $numero_documento, $nombres_razon_social, 
                                $apellidos, $direccion, $telefono) {
        $this->tipo_documento = $tipo_documento;
        $this->numero_documento = $numero_documento;
        $this->nombres_razon_social = $nombres_razon_social;
        $this->apellidos = $apellidos;
        $this->direccion = $direccion;
        $this->telefono = $telefono;
        
        $this->estado = 1; 
        $this->id_persona = null; 
    }

    public function __get($propiedad) {
        if (property_exists($this, $propiedad)) return $this->$propiedad;
        return null;
    }

    public function __set($propiedad, $valor) {
        if (property_exists($this, $propiedad)) $this->$propiedad = $valor;
    }

}
?>