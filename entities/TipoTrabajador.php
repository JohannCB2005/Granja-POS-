<?php
class TipoTrabajador {
    public $id_tipo;
    public $nombre;
    public $estado;

    public function __construct($id_tipo = null, $nombre = '', $estado = 1) {
        $this->id_tipo = $id_tipo;
        $this->nombre = $nombre;
        $this->estado = $estado;
    }
}
?>
