<?php
require_once 'Persona.php';

class Usuario extends Persona {
    protected $id_usuario;
    protected $id_rol;
    protected $username;
    protected $password;

    public function __construct($tipo_documento, $numero_documento, $nombres_razon_social, 
                                $apellidos, $direccion, $telefono, $id_rol, $username, $password) {
        
        parent::__construct($tipo_documento, $numero_documento, $nombres_razon_social, 
                            $apellidos, $direccion, $telefono);

        // Llenamos los datos específicos del hijo
        $this->id_rol = $id_rol;
        $this->username = $username;
        $this->password = $password;
        
        $this->id_usuario = null;
    }
}
?>