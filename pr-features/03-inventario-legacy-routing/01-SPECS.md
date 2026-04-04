# Feature-03: Inventario Module Legacy Routing - SPECS

## 📋 Objetivo
Implementar compatibilidad backward-compatible para acciones legacy del módulo Inventario, permitiendo que el frontend actual continúe funcionando.

## 🎯 Requisitos Funcionales

### RF-01: Mapeo de Acciones Legacy
El sistema debe mapear 4 acciones legacy del módulo Inventario a sus handlers:

| Acción | Hash/ID | Método Esperado | Descripción |
|--------|---------|-----------------|-------------|
| Stock Move | `STOCK_MOVE` | `InventarioController::recordStockMove()` | Registra movimiento de stock (salida/entrada) |
| Stock Move Devolución | `STOCK_MOVE_DEVOLUCION` | `InventarioController::recordStockMoveDevolución()` | Registra devolución/reintegro de stock |
| Cancelar Ingreso | `BORRAR_DATOS_INGRESO_AUX_INVENTARIO` | `InventarioController::cancelPrechart()` | Cancela pre-carga de ingreso |
| Ingreso Pre-carga | `INGRESO_DATOS_DATOS_AUX_INVENTARIO` | `InventarioController::savePrechart()` | Guarda datos de pre-carga de ingreso |

### RF-02: Parámetros Legacy
El módulo Inventario acepta múltiples parámetros según la acción:

**Para STOCK_MOVE:**
```json
{
  "action": "STOCK_MOVE",
  "_usuario": "admin",
  "_password": "admin123",
  "_llaveSession": "token123",
  "id_documento": 12345,
  "id_producto": 567,
  "cantidad": 10,
  "precio": 25000,
  "tipo_movimiento": "salida"
}
```

**Para INGRESO_DATOS_DATOS_AUX_INVENTARIO:**
```json
{
  "action": "INGRESO_DATOS_DATOS_AUX_INVENTARIO",
  "_usuario": "admin",
  "listado": "[{...}]",
  "id_ingreso": 789
}
```

### RF-03: Estructura de Respuesta
Respuestas mantienen compatibilidad con formato legacy:

**Éxito (STOCK_MOVE):**
```json
{
  "success": true,
  "message": "Stock registrado correctamente",
  "data": {
    "stock_move_id": 1,
    "producto_id": 567,
    "cantidad_anterior": 50,
    "cantidad_nueva": 40,
    "tipo_movimiento": "salida"
  }
}
```

## ✅ Criterios de Aceptación

1. **Mapeo**: Los 4 hashes se resuelven correctamente a handlers
2. **Parámetros**: Ambos estilos (legacy y moderno) funcionan
3. **Unitarios**: 10/10 tests pasando (5 mapping + 5 service)
4. **Compatibilidad**: Sin cambios requeridos en frontend

## 📊 Impacto

- Archivos nuevos: 4
- Archivos modificados: 1 (Routes.php)
- BD: Sin cambios
- Frontend: SIN CAMBIOS

## 🎓 Referencias

- [Auth Legacy Routing](../01-auth-legacy-routing/02-IMPLEMENTATION.md)
- [Carwash Legacy Routing](../02-carwash-legacy-routing/02-IMPLEMENTATION.md)
