<?php
class Venta {
    public $id_venta;
    public $id_usuario;
    public $id_cliente;
    public $id_trabajador;
    public $fecha;
    public $tipo_comprobante;
    public $total;
    public $metodo_pago;
    public $id_vale;
    public $pago_efectivo;
    public $pago_vale;
    public $estado;

    public function __construct($id_venta=null, $id_usuario=null, $id_cliente=null, $id_trabajador=null, $fecha='', $tipo_comprobante=1, $total=0, $metodo_pago=1, $id_vale=null, $pago_efectivo=0, $pago_vale=0, $estado=1) {
        $this->id_venta = $id_venta;
        $this->id_usuario = $id_usuario;
        $this->id_cliente = $id_cliente;
        $this->id_trabajador = $id_trabajador;
        $this->fecha = $fecha;
        $this->tipo_comprobante = $tipo_comprobante;
        $this->total = $total;
        $this->metodo_pago = $metodo_pago;
        $this->id_vale = $id_vale;
        $this->pago_efectivo = $pago_efectivo;
        $this->pago_vale = $pago_vale;
        $this->estado = $estado;
    }
}
?>
