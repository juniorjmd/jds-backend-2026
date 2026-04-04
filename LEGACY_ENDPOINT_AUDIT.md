# Auditoria Inicial De Endpoints Legacy

## Objetivo

Tener una matriz simple para decidir el orden de migracion sin perder cobertura del backend legacy.

Regla vigente:

- completar los modulos actuales para reemplazo directo del legacy
- migrar todos los endpoints legacy
- validar entradas y respuestas de lo ya migrado
- mantener contrato de respuesta estandar en backend nuevo
- adaptar frontend para consumir ese contrato estandar
- documentar uso real del frontend y falta de uso confirmado

## Hallazgos de esta revision

### Router nuevo

- `app/Bootstrap/Routes.php` ya mezcla mapas por modulo
- el router si puede despachar acciones legacy fuera de `/api`
- el bloqueo principal no es el router sino la cobertura de acciones por modulo

### Frontend actual

El frontend sigue declarando y usando multiples acciones legacy en:

- `src/app/models/app.db.actions.ts`

Tambien consume respuestas con acoplamiento fuerte al contrato legacy. Patrones visibles:

- `value.error === 'ok'`
- `value.numdata`
- `value.data`
- `e.error.error`

Eso incluye acciones de:

- auth
- documentos
- ventas
- inventario
- admin
- personas
- datos iniciales
- integraciones Odoo
- acciones genericas de base de datos

### Backend nuevo: estado visible por modulo

#### Carwash

- estado: migrado parcial y ahora alineado con contrato estandar
- acciones visibles:
  - `ABRIR_CAJA_ACTIVA`
  - `CERRAR_CAJA_ACTIVA`
  - `CERRAR_CAJA_PARCIAL`
  - `OBTENER_RESUMEN_CAJA`
- pendiente:
  - reemplazar payload simulado por logica real contra legacy/BD
  - confirmar ownership final de acciones que legacy tambien expone desde `ventas`
- archivos frontend detectados:
  - `src/app/services/Cajas.services.ts`
  - `src/app/modules/pos/pages/abrir-caja/abrir-caja.component.ts`
  - `src/app/modules/pos/pages/cerrar-caja/cerrar-caja.component.ts`
  - `src/app/modules/pos/modals/definir-base-caja/definir-base-caja.component.ts`
- nota detallada:
  - `pr-features/02-carwash-legacy-routing/04-BACK-FRONT-ALIGNMENT.md`

#### Auth

- estado: migrado y mapeado en `config/actions.php`
- pendiente:
  - validar completamente entradas y respuestas
  - definir contrato estandar final del modulo
  - adaptar frontend de login a ese contrato
- archivos frontend detectados:
  - `src/app/services/login.services.ts`
  - `src/app/modules/login/pages/login/login.component.ts`
  - `src/app/modules/login/pages/forgotPassWord/forgotPassWord.component.ts`
  - `src/app/components/home/home.component.ts`
  - `src/app/components/mi-usuario/mi-usuario.component.ts`
- nota detallada:
  - `pr-features/01-auth-legacy-routing/04-BACK-FRONT-ALIGNMENT.md`

#### Personas

- estado: con acciones legacy registradas
- acciones visibles: `BUSCAR_ODOO_TITULO_PERSONA`, `GET_MAESTROS_CLIENTES`
- pendiente: validacion de contrato legacy

#### Ventas

- estado: con acciones legacy registradas
- acciones visibles:
  - `ASIGNAR_PAGOS_DOCUMENTOS_COMPRA_CREDITO`
  - `ASIGNAR_PAGOS_DOCUMENTOS_COMPRA_CREDITO_EDICION`
  - `ASIGNAR_PAGOS_DOCUMENTOS_CREDITO`
  - `ASIGNAR_ABONO_DOCUMENTOS_CREDITO`
- pendiente:
  - validar respuestas legacy
  - revisar cobertura frente a acciones adicionales del legacy

#### DatosIniciales

- estado: con accion legacy registrada
- accion visible: `GET_SUCURSAL_PRINCIPAL_DATA`
- pendiente: validacion de contrato legacy

#### Vehiculos

- estado: con accion legacy registrada
- accion visible: `CREAR_DOCUMENTO_POR_SERVICIO_VEHICULO`
- pendiente: validacion de contrato legacy

#### Inventario

- estado: con varias acciones legacy registradas
- cobertura visible:
  - movimientos
  - precargue
  - descuento
  - crear/actualizar producto
  - busquedas de producto
- pendiente:
  - validar contrato legacy
  - revisar acciones legacy no mapeadas del modulo como categorias, bodegas, marcas y otros filtros

#### Admin

- estado: cobertura parcial
- acciones visibles:
  - `OBTENER_USUARIOS`
  - `CREAR_USUARIO`
  - `ACTUALIZAR_USUARIO`
  - `OBTENER_MENUS`
- pendiente:
  - comparar contra acciones legacy reales del modulo
  - validar entradas y respuestas

#### Documentos

- estado: principal brecha actual
- observacion clave: `config/documentos-actions.php` todavia expone acciones estilo API moderna y no el mapa legacy principal del modulo
- acciones legacy visibles y relevantes:
  - `GET_DOCUMENTOS_USUARIO_ACTUAL`
  - `GET_DOCUMENTOS_USUARIO_ACTUAL_CAJA_ACTIVA`
  - `CREAR_DOCUMENTO_POR_USUARIO`
  - `CREAR_DOCUMENTO_COMPRA_POR_USUARIO`
  - `CREAR_DOCUMENTO_GASTO_POR_USUARIO`
  - `CERRAR_DOCUMENTO_FACTURA`
  - `CERRAR_DOCUMENTO_REMISION`
  - `CAMBIAR_DOCUMENTO_A_ENVIO`
  - `CANCELAR_DOCUMENTO_POR_USUARIO`
  - `CREAR_DOCUMENTO_COTIZACION_POR_USUARIO`
  - `CAMBIAR_DOCUMENTO_ACTIVO_POR_USUARIO`
  - `CAMBIAR_DOCUMENTO_COMPRA_ACTIVO_POR_USUARIO`
  - `CAMBIAR_DOCUMENTO_POR_CAJA`
  - `ASIGNAR_ABONO_DOCUMENTOS_CREDITO`
  - `ASIGNAR_ABONO_DOCUMENTOS_CREDITO_POR_PAGAR`
  - `GENERAR_DOCUMENTOS_DEVOLUCION`
  - `GENERAR_DOCUMENTOS_NOTA_DEBITO`
- prioridad: alta

## Clasificacion operativa sugerida

Para cada endpoint legacy registrar uno de estos estados:

- `usado-frontend`
- `migrado-pendiente-validacion`
- `migrado-validado`
- `solo-legacy-sin-uso-confirmado`
- `pendiente-migracion`

Y por cada modulo registrar tambien:

- archivos frontend afectados
- adaptacion requerida para leer respuesta estandar
- PR backend asociado
- PR frontend asociado

## Orden de cierre recomendado

Primero cerrar los modulos ya existentes en backend nuevo:

1. `auth`
2. `carwash`
3. `personas`
4. `ventas`
5. `datosiniciales`
6. `vehiculos`
7. `inventario`
8. `admin`
9. `documentos`

Despues de eso evaluar:

- acciones genericas de base de datos
- acciones Odoo e integraciones
- modulos legacy sin reflejo directo por nombre como `brand`, `csv_manager`, `download`, `images`, `up_csv`, `up_csv_answ`

## Requisito operativo frontend

- los cambios frontend deben prepararse contra el repo `https://github.com/juniorjmd/jds-frontend-2026.git`
- validado en esta maquina: `jds-carwash-front` ya tiene `origin` apuntando a `https://github.com/juniorjmd/jds-frontend-2026.git`

## Siguiente corte recomendado

1. Validar contrato de los modulos ya migrados.
2. Registrar sus consumidores frontend.
3. Migrar `documentos` en modo legacy real y con cobertura completa del modulo.
4. Adaptar frontend de `documentos` a la respuesta estandar.
5. Completar cobertura restante por `admin`.
6. Repetir el mismo esquema por los demas modulos actuales hasta cierre completo.
7. Mantener actualizado este archivo por accion o por modulo en cada PR.
