<?php
class DetalleVenta {
    public $id_detalle;
    public $id_insumo;
    public $cantidad;
    public $precio_venta;
    public $subtotal;

    public function __construct($id_detalle = null, $id_insumo = null, $cantidad = 0, $precio_venta = 0.0, $subtotal = 0.0) {
        $this->id_detalle  = $id_detalle;
        $this->id_insumo   = $id_insumo;
        $this->cantidad    = $cantidad;
        $this->precio_venta= $precio_venta;
        $this->subtotal    = $subtotal;
    }
}
?>
