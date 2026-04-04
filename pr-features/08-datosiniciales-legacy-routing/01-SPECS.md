# Feature-08: DatosIniciales Module Legacy Routing - SPECS

## Objetivo
Migrar solo la acción legacy de `datosiniciales` que el frontend actual usa de forma directa.

## Requisitos Funcionales

### RF-01: Mapeo de acción usada por el frontend
El sistema debe mapear:

| Acción | Método Esperado | Uso detectado en frontend |
|--------|-----------------|---------------------------|
| `GET_SUCURSAL_PRINCIPAL_DATA` | `DatosInicialesController::getPrincipalBranchData()` | `DatosInicialesService.getDatosIniSucursal()` |

### RF-02: Compatibilidad de payload
- La respuesta debe conservar formato legacy compatible con el frontend actual.
- La acción no exige autenticación, igual que el endpoint legacy original.

### RF-03: Alcance explícito
- No se migran en esta feature las acciones hash restantes del módulo `datosiniciales`.
- Cada acción no migrada debe quedar documentada como fuera de alcance por no evidenciar uso real en el frontend fuente.
