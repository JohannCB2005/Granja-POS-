<?php
class Venta {
    protected $id_venta;
    protected $id_usuario;
    protected $id_cliente;
    protected $tipo_comprobante;
    protected $fecha;
    protected $total;
    protected $estado;
    
    // Lista de objetos DetalleVenta
    protected $detalles; 

    public function __construct($id_usuario, $id_cliente, $tipo_comprobante, $total) {
        $this->id_usuario = $id_usuario;
        $this->id_cliente = $id_cliente;
        $this->tipo_comprobante = $tipo_comprobante;
        $this->total = $total;
        
        $this->fecha = null; // MySQL le asignará el CURRENT_TIMESTAMP
        $this->estado = 1;
        $this->detalles = []; // Nace como un array vacío
    }

    public function __get($propiedad) { 
        if (property_exists($this, $propiedad)) return $this->$propiedad; return null; 
    }
    public function __set($propiedad, $valor) { 
        if (property_exists($this, $propiedad)) $this->$propiedad = $valor; 
    }

    public function agregarDetalle(DetalleVenta $detalle) {
        $this->detalles[] = $detalle;
    }
}
?>