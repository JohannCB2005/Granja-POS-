<?php
class Persona {
    public $id_persona;
    public $tipo_documento;
    public $numero_documento;
    public $nombres_razon_social;
    public $apellidos;
    public $direccion;
    public $telefono;
    public $estado;

    public function __construct(
        $tipo_documento = 1,
        $numero_documento = '',
        $nombres_razon_social = '',
        $apellidos = '',
        $direccion = '',
        $telefono = '',
        $id_persona = null
    ) {
        $this->id_persona           = $id_persona;
        $this->tipo_documento       = $tipo_documento;
        $this->numero_documento     = $numero_documento;
        $this->nombres_razon_social = $nombres_razon_social;
        $this->apellidos            = $apellidos;
        $this->direccion            = $direccion;
        $this->telefono             = $telefono;
        $this->estado               = 1;
    }
}
?>
