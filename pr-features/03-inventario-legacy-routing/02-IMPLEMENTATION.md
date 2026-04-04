# Feature-03: Inventario Module Legacy Routing - IMPLEMENTATION

## 🏗️ Cambios Implementados

### 1. config/inventario-actions.php
```php
use App\Modules\Inventario\InventarioController;

return [
    'STOCK_MOVE' => [InventarioController::class, 'recordStockMove'],
    'STOCK_MOVE_DEVOLUCION' => [InventarioController::class, 'recordStockMoveDevolución'],
    'BORRAR_DATOS_INGRESO_AUX_INVENTARIO' => [InventarioController::class, 'cancelPrechart'],
    'INGRESO_DATOS_DATOS_AUX_INVENTARIO' => [InventarioController::class, 'savePrechart'],
];
```

### 2. app/Bootstrap/Routes.php
Actualizar carga de config files:
```php
$inventarioActions = (static function() {
    return require __DIR__ . '/../../config/inventario-actions.php';
})();
```

Agregar al array_merge:
```php
is_array($inventarioActions) ? $inventarioActions : []
```

### 3. app/Modules/Inventario/InventarioController.php
Crear controlador con 4 métodos HTTP handlers.

### 4. app/Modules/Inventario/Services/InventarioService.php
Crear service con lógica de negocio para cada acción.

## 🎯 Implementación Paso a Paso

La implementación sigue exactamente el patrón de Carwash:

1. Mapear acciones en config/inventario-actions.php
2. Cargar en Routes::map()
3. Crear InventarioController con handlers
4. Crear InventarioService con lógica
5. Tests unitarios (5 parameters + 5 service = 10 tests)

## 🔄 Flujo de Request

```
Frontend → POST / {action: 'STOCK_MOVE', ...}
         ↓
Router::dispatch() → Detecta NO /api
                   ↓
Router::dispatchLegacyAction()
                   ↓
Routes::map() → Busca 'STOCK_MOVE'
                ↓
InventarioController::recordStockMove()
                ↓
InventarioService::recordStockMove()
                ↓
Response JSON al frontend
```

## 📝 Métodos del Controller

1. **recordStockMove()** - Registra movimiento de stock
   - Parámetros: id_documento, id_producto, cantidad, tipo_movimiento
   - Retorna: Nuevo estado del stock

2. **recordStockMoveDevolución()** - Registra devolución
   - Parámetros: id_documento, id_producto, cantidad
   - Retorna: Stock después de devolución

3. **cancelPrechart()** - Cancela pre-carga
   - Parámetros: id_ingreso
   - Retorna: { success: true }

4. **savePrechart()** - Guarda pre-carga
   - Parámetros: listado (JSON array), id_ingreso
   - Retorna: Pre-carga guardada

## 📋 Archivos Creados

```
config/inventario-actions.php                          (nuevo)
app/Modules/Inventario/InventarioController.php        (nuevo)
app/Modules/Inventario/Services/InventarioService.php  (nuevo)
tests/Unit/InventarioParametersTest.php                (nuevo)
tests/Unit/InventarioServiceTest.php                   (nuevo)
```

## 📊 Archivos Modificados

```
app/Bootstrap/Routes.php                               (actualizado)
```

## ✅ Validación

Todos los tests pasan en el nivel unitario. Próximas fases:
- Integración (cuando BD disponible)
- HTTP manual (cuando BD disponible)
- Frontend real (cuando todo esté listo)
