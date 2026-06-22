<?php
class Rol {
    public $id_rol;
    public $nombre;

    public function __construct($nombre = '', $id_rol = null) {
        $this->id_rol  = $id_rol;
        $this->nombre  = $nombre;
    }
}
?>
