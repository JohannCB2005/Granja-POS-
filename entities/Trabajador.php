<?php
require_once __DIR__ . '/Persona.php';

class Trabajador extends Persona {
    public $id_trabajador;
    public $id_tipo_trabajador;
    public $id_dependencia;
    public $codigo_planilla;

    public function __construct(
        $id_persona = null,
        $tipo_documento = 1,
        $numero_documento = '',
        $nombres_razon_social = '',
        $apellidos = null,
        $direccion = null,
        $telefono = null,
        $estado = 1,
        $id_trabajador = null,
        $id_tipo_trabajador = null,
        $id_dependencia = null,
        $codigo_planilla = null
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
        $this->id_trabajador = $id_trabajador;
        $this->id_tipo_trabajador = $id_tipo_trabajador;
        $this->id_dependencia = $id_dependencia;
        $this->codigo_planilla = $codigo_planilla;
    }
}
?>
