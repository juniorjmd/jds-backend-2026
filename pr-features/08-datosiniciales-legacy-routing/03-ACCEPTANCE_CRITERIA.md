# Feature-08: DatosIniciales Module Legacy Routing - ACCEPTANCE CRITERIA

## Criterios Funcionales
- `GET_SUCURSAL_PRINCIPAL_DATA` queda registrada en `Routes::map()`.
- `DatosInicialesController` expone `getPrincipalBranchData()`.
- El servicio falla de forma explícita si no existe sucursal principal configurada.

## Criterios de No-Regresión
- No se alteran los módulos ya migrados.
- La respuesta mantiene compatibilidad con el frontend actual que espera un arreglo plano.

## Tests
- `tests/Unit/DatosInicialesParametersTest.php`
- `tests/Unit/DatosInicialesServiceTest.php`

## Condición de Completitud
- La acción usada por el frontend queda migrada.
- Las acciones restantes del módulo quedan documentadas como no migradas.
