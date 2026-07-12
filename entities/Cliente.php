<?php
require_once __DIR__ . '/Persona.php';

class Cliente extends Persona {
    public $id_cliente;
    public $tipo_cliente;
    public $id_tipo_trabajador;
    public $id_facultad;
    public $codigo_planilla;

    public function __construct(
        $tipo_documento = 1,
        $numero_documento = '',
        $nombres_razon_social = '',
        $apellidos = null,
        $direccion = null,
        $telefono = null,
        $tipo_cliente = 1,
        $id_tipo_trabajador = null,
        $id_facultad = null,
        $codigo_planilla = null,
        $id_cliente = null
    ) {
        parent::__construct(
            $tipo_documento,
            $numero_documento,
            $nombres_razon_social,
            $apellidos,
            $direccion,
            $telefono
        );
        $this->tipo_cliente = $tipo_cliente;
        $this->id_tipo_trabajador = $id_tipo_trabajador;
        $this->id_facultad = $id_facultad;
        $this->codigo_planilla = $codigo_planilla;
        $this->id_cliente = $id_cliente;
    }
}
?>
