<?php
class Caja {
    public $id_caja;
    public $id_usuario;
    public $monto_apertura;
    public $fecha_apertura;
    public $monto_cierre;
    public $fecha_cierre;
    public $total_ventas;
    public $num_ventas;
    public $diferencia;
    public $observaciones;
    public $estado;

    public function __construct($id_usuario = null, $monto_apertura = 0.00) {
        $this->id_usuario      = $id_usuario;
        $this->monto_apertura  = $monto_apertura;
        $this->estado          = 1;
    }
}
?>
