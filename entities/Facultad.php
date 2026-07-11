<?php
class Facultad {
    public $id_facultad;
    public $nombre;
    public $estado;

    public function __construct($id_facultad = null, $nombre = '', $estado = 1) {
        $this->id_facultad = $id_facultad;
        $this->nombre = $nombre;
        $this->estado = $estado;
    }
}
?>
