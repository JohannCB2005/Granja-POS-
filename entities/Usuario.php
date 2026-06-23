<?php
require_once __DIR__ . '/Persona.php';

class Usuario extends Persona {
    public $id_usuario;
    public $id_rol;
    public $username;
    public $password;

    public function __construct(
        $tipo_documento = 1,
        $numero_documento = '',
        $nombres_razon_social = '',
        $apellidos = '',
        $direccion = '',
        $telefono = '',
        $id_rol = null,
        $username = '',
        $password = '',
        $id_usuario = null,
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
        $this->id_rol = $id_rol;
        $this->username = $username;
        $this->password = $password;
        $this->id_usuario = $id_usuario;
    }
}
?>
