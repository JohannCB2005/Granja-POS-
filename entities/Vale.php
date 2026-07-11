<?php
class Vale {
    public $id_vale;
    public $codigo;
    public $tipo_vale;
    public $id_trabajador;
    public $monto;
    public $id_insumo_especie;
    public $cantidad_especie;
    public $fecha_emision;
    public $fecha_vencimiento;
    public $estado;
    public $id_usuario_emisor;
    public $campana;

    public function __construct($id_vale=null, $codigo='', $tipo_vale=1, $id_trabajador=null, $monto=0, $id_insumo_especie=null, $cantidad_especie=null, $fecha_emision='', $fecha_vencimiento='', $estado=1, $id_usuario_emisor=null, $campana='') {
        $this->id_vale = $id_vale;
        $this->codigo = $codigo;
        $this->tipo_vale = $tipo_vale;
        $this->id_trabajador = $id_trabajador;
        $this->monto = $monto;
        $this->id_insumo_especie = $id_insumo_especie;
        $this->cantidad_especie = $cantidad_especie;
        $this->fecha_emision = $fecha_emision;
        $this->fecha_vencimiento = $fecha_vencimiento;
        $this->estado = $estado;
        $this->id_usuario_emisor = $id_usuario_emisor;
        $this->campana = $campana;
    }
}
?>
