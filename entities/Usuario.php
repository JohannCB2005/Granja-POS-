<?php
class Usuario {
    public $id_usuario;
    public $id_persona;
    public $id_rol;
    public $username;
    public $password;

    public function __construct($id_persona = null, $id_rol = null, $username = '', $password = '', $id_usuario = null) {
        $this->id_usuario = $id_usuario;
        $this->id_persona = $id_persona;
        $this->id_rol     = $id_rol;
        $this->username   = $username;
        $this->password   = $password;
    }
}
?>
