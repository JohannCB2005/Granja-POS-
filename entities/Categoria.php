<?php
class Categoria {
    public $id_categoria;
    public $nombre;
    public $descripcion;
    public $estado;

    public function __construct($nombre = '', $descripcion = '', $id_categoria = null) {
        $this->id_categoria = $id_categoria;
        $this->nombre       = $nombre;
        $this->descripcion  = $descripcion;
        $this->estado       = 1;
    }
}
?>
