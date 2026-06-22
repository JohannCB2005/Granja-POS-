<?php
class Cliente {
    public $id_cliente;
    public $id_persona;
    public $tipo_cliente;

    public function __construct($id_persona = null, $tipo_cliente = 1, $id_cliente = null) {
        $this->id_cliente   = $id_cliente;
        $this->id_persona   = $id_persona;
        $this->tipo_cliente = $tipo_cliente;
    }
}
?>
