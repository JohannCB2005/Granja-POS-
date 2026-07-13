<?php
/**
 * Entidad Insumo
 * Representa un artículo del catálogo de inventario.
 * 
 * Regla de negocio:
 *   - contenido_estandar = NULL  → Peso variable al momento de la venta (Ej: pavos vivos).
 *                                   El precio se calcula sobre el peso real de balanza.
 *   - contenido_estandar = N     → Cada pieza tiene un peso/volumen fijo (Ej: saco de 50 Kg).
 *                                   El precio se calcula sobre las piezas vendidas.
 */
class Insumo {
    public $id_insumo;
    public $id_categoria;
    public $id_unidad;
    public $nombre;
    public $precio_unitario;
    public $costo_produccion;    // Costo base por lote para cálculo de rentabilidad
    public $stock_piezas;        // Cantidad de piezas/unidades físicas en almacén
    public $contenido_estandar;  // Kg/L por pieza. NULL = peso variable (aves)
    public $estado;
    public $imagen;

    public function __construct(
        $id_categoria = null,
        $id_unidad = null,
        $nombre = '',
        $precio_unitario = 0.0,
        $costo_produccion = 0.0,
        $stock_piezas = 0.0,
        $contenido_estandar = null,
        $id_insumo = null,
        $imagen = null
    ) {
        $this->id_insumo          = $id_insumo;
        $this->id_categoria       = $id_categoria;
        $this->id_unidad          = $id_unidad;
        $this->nombre             = $nombre;
        $this->precio_unitario    = $precio_unitario;
        $this->costo_produccion   = $costo_produccion;
        $this->stock_piezas       = $stock_piezas;
        $this->contenido_estandar = $contenido_estandar;
        $this->estado             = 1;
        $this->imagen             = $imagen;
    }
}
?>
