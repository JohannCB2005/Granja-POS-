<?php
class Dependencia {
    public $id_dependencia;
    public $nombre;
    public $estado;

    public function __construct($id_dependencia = null, $nombre = '', $estado = 1) {
        $this->id_dependencia = $id_dependencia;
        $this->nombre = $nombre;
        $this->estado = $estado;
    }
}
?>
