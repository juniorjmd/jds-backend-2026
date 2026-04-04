# Feature-03: Inventario Module Legacy Routing - ACCEPTANCE CRITERIA

## ✅ CAT-01: Mapeo de 4 Acciones Legacy

- [ ] Archivo `config/inventario-actions.php` existe
- [ ] Contiene 4 acciones correctamente mapeadas
- [ ] Archivo retorna array PHP

**TEST**: `tests/Unit/InventarioParametersTest.php::TEST 1`

## ✅ CAT-02: Routes Carga Acciones

- [ ] `Routes::map()` carga `config/inventario-actions.php`
- [ ] Las 4 acciones están en el mapa final

**TEST**: `tests/Unit/InventarioParametersTest.php::TEST 2`

## ✅ CAT-03: InventarioController Existe

- [ ] 4 métodos públicos creados
- [ ] `recordStockMove()`, `recordStockMoveDevolución()`, `cancelPrechart()`, `savePrechart()`

**TEST**: `tests/Unit/InventarioParametersTest.php::TEST 3-4`

## ✅ CAT-04: InventarioService Existe

- [ ] Misma estructura del flujo

**TEST**: `tests/Unit/InventarioServiceTest.php::TEST 1`

## ✅ CAT-05: Parámetros Legacy Aceptados

- [ ] Todos los parámetros legacy se extraen correctamente

**TEST**: `tests/Unit/InventarioParametersTest.php::TEST 5`

## ✅ CAT-06: 10/10 Tests Unitarios Pasando

- [ ] 5 tests de parámetros ✓
- [ ] 5 tests de service ✓

**COMMAND**:
```bash
php tests/Unit/InventarioParametersTest.php && php tests/Unit/InventarioServiceTest.php
# Esperado: PASSED 10/10
```

## 📋 Checklist de Implementación

**Código**:
- [ ] config/inventario-actions.php creado
- [ ] app/Modules/Inventario/InventarioController.php creado  
- [ ] app/Modules/Inventario/Services/InventarioService.php creado
- [ ] Routes.php actualizado

**Tests**:
- [ ] tests/Unit/InventarioParametersTest.php creado y pasando
- [ ] tests/Unit/InventarioServiceTest.php creado y pasando

**Documentación**:
- [ ] 01-SPECS.md completo
- [ ] 02-IMPLEMENTATION.md completo
- [ ] 03-ACCEPTANCE_CRITERIA.md (este archivo) completo

**Git**:
- [ ] Código commiteado con mensaje descriptivo

