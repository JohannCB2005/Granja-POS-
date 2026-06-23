<?php
require_once __DIR__ . '/Persona.php';

class Cliente extends Persona {
    public $id_cliente;
    public $tipo_cliente;

    public function __construct(
        $tipo_documento = 1,
        $numero_documento = '',
        $nombres_razon_social = '',
        $apellidos = '',
        $direccion = '',
        $telefono = '',
        $tipo_cliente = 1,
        $id_cliente = null,
        $id_persona = null
    ) {
        parent::__construct(
            $tipo_documento,
            $numero_documento,
            $nombres_razon_social,
            $apellidos,
            $direccion,
            $telefono,
            $id_persona
        );
        $this->tipo_cliente = $tipo_cliente;
        $this->id_cliente = $id_cliente;
    }
}
?>
