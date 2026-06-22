<?php
require_once 'Persona.php';

class Cliente extends Persona {
    protected $id_cliente;
    protected $tipo_cliente;

    public function __construct($tipo_documento, $numero_documento, $nombres_razon_social, 
                                $apellidos, $direccion, $telefono, $tipo_cliente) {
        
        parent::__construct($tipo_documento, $numero_documento, $nombres_razon_social, 
                            $apellidos, $direccion, $telefono);

        $this->tipo_cliente = $tipo_cliente;
        
        $this->id_cliente = null;
    }
}
?>