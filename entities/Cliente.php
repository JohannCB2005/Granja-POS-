<?php
require_once __DIR__ . '/Persona.php';

class Cliente extends Persona {
    public $id_cliente;
    public $tipo_cliente;

    public function __construct(
        $id_persona = null,
        $tipo_documento = 1,
        $numero_documento = '',
        $nombres_razon_social = '',
        $apellidos = null,
        $direccion = null,
        $telefono = null,
        $estado = 1,
        $id_cliente = null,
        $tipo_cliente = 1
    ) {
        parent::__construct(
            $id_persona,
            $tipo_documento,
            $numero_documento,
            $nombres_razon_social,
            $apellidos,
            $direccion,
            $telefono,
            $estado
        );
        $this->id_cliente = $id_cliente;
        $this->tipo_cliente = $tipo_cliente;
    }
}
?>
