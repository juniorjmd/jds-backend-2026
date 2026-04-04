<?php

/**
 * Mapea acciones legacy del módulo Inventario
 * 
 * Estructura: 'hash_accion' => [ControllerClass::class, 'methodName']
 * El Router usa este mapeo para resolver acciones sin /api en la URL
 */

use App\Modules\Inventario\InventarioController;

return [
    'STOCK_MOVE' => [InventarioController::class, 'recordStockMove'],
    'STOCK_MOVE_DEVOLUCION' => [InventarioController::class, 'recordStockMoveDevolución'],
    'BORRAR_DATOS_INGRESO_AUX_INVENTARIO' => [InventarioController::class, 'cancelPrechart'],
    'INGRESO_DATOS_DATOS_AUX_INVENTARIO' => [InventarioController::class, 'savePrechart'],
];
