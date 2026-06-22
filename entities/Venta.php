<?php
class Venta {
    public $id_venta;
    public $id_usuario;
    public $id_cliente;
    public $tipo_comprobante;
    public $total;
    public $estado;
    public $detalles = [];

    public function __construct($id_usuario = null, $id_cliente = null, $tipo_comprobante = 1, $total = 0.0, $id_venta = null) {
        $this->id_venta         = $id_venta;
        $this->id_usuario       = $id_usuario;
        $this->id_cliente       = $id_cliente;
        $this->tipo_comprobante = $tipo_comprobante;
        $this->total            = $total;
        $this->estado           = 1;
        $this->detalles         = [];
    }

    public function agregarDetalle($detalle) {
        $this->detalles[] = $detalle;
    }
}
?>
