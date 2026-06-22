<?php
class DetalleVenta {
    protected $id_detalle;
    protected $id_venta;
    protected $id_insumo;
    protected $cantidad;
    protected $precio_venta;
    protected $subtotal;

    public function __construct($id_venta, $id_insumo, $cantidad, $precio_venta, $subtotal) {
        $this->id_venta = $id_venta;
        $this->id_insumo = $id_insumo;
        $this->cantidad = $cantidad;
        $this->precio_venta = $precio_venta;
        $this->subtotal = $subtotal;
        
    }

    public function __get($propiedad) { 
        if (property_exists($this, $propiedad)) return $this->$propiedad; return null; 
    }
    
    public function __set($propiedad, $valor) { 
        if (property_exists($this, $propiedad)) $this->$propiedad = $valor; 
    }
}
?>