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

#### Cierre compartido en curso: `Admin` + `Carwash` + `Vehiculos` + `Inventario`

- se abrio un frente unico de hardening porque los cuatro modulos seguian dependiendo de acciones raiz legacy
- acciones raiz ya implementadas en backend nuevo:
  - `DATABASE_GENERIC_CONTRUCT_INSERT_SELECT`
  - `INSERT_PERFIL_USUARIO`
  - `mnbvcxzxcxcxasdfewq15616`
  - `qwer12356yhn7ujm8ik`
  - `BUSCAR_STOCK_LOCATION`
- validacion real local ya hecha para acciones no destructivas:
  - `mnbvcxzxcxcxasdfewq15616`
  - `BUSCAR_STOCK_LOCATION`
  - `e06c06e7e4ef58bdb0kieujfñ541b3017fdd35473`
- validacion real local adicional sobre tabla controlada `test_crud_clientes`:
  - `DATABASE_GENERIC_CONTRUCT_INSERT`
  - `DATABASE_GENERIC_CONTRUCT_SELECT`
  - `DATABASE_GENERIC_CONTRUCT_UPDATE`
  - `DATABASE_GENERIC_CONTRUCT_INSERT_SELECT`
  - `DATABASE_GENERIC_CONTRUCT_DELETE`
- validacion manual controlada pendiente por afectar datos compartidos:
  - `INSERT_PERFIL_USUARIO`
  - `qwer12356yhn7ujm8ik`

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
- validacion de cobertura legacy:
  - parcial
  - las 4 acciones de caja visibles estan mapeadas
  - pero en legacy esas acciones tambien viven mezcladas con `ventas`
  - sigue pendiente confirmar ownership final y reemplazo real de procedimientos

#### Auth

- estado: migrado y mapeado en `config/actions.php`
- pendiente:
  - completar cobertura del legacy
  - validar completamente entradas y respuestas
- archivos frontend detectados:
  - `src/app/services/login.services.ts`
  - `src/app/modules/login/pages/login/login.component.ts`
  - `src/app/modules/login/pages/forgotPassWord/forgotPassWord.component.ts`
  - `src/app/components/home/home.component.ts`
  - `src/app/components/mi-usuario/mi-usuario.component.ts`
- nota detallada:
  - `pr-features/01-auth-legacy-routing/04-BACK-FRONT-ALIGNMENT.md`
- validacion de cobertura legacy:
  - completo
  - cubiertos:
    - `ef2e1d89937fba9f888516293ab1e19e7ed789a5`
    - `16770d92a6a82ee846f7ff23b4c8ad05b69fba03`
    - `16770d92a6a82ee8464f678f5f223b4c8ad05b69fba03`
    - `RESETEAR_USUARIO_PASS`
    - `HIJODELAGRANCHINGADA`
    - `c332258e69e38f18450f9a48c65c89d9e436c561` cierre de sesion
  - nota frontend:
    - no se encontro consumo directo actual del logout legacy en el repo frontend durante esta revision

#### Personas

- estado: con acciones legacy registradas
- acciones visibles: `BUSCAR_ODOO_TITULO_PERSONA`, `GET_MAESTROS_CLIENTES`
- validacion de cobertura legacy:
  - completo por acciones visibles del modulo `personas/index.php`
- pendiente:
  - alinear respuesta estandar en backend

#### Ventas

- estado: con acciones legacy registradas
- acciones visibles:
  - `ASIGNAR_PAGOS_DOCUMENTOS_COMPRA_CREDITO`
  - `ASIGNAR_PAGOS_DOCUMENTOS_COMPRA_CREDITO_EDICION`
  - `ASIGNAR_PAGOS_DOCUMENTOS_CREDITO`
  - `ASIGNAR_ABONO_DOCUMENTOS_CREDITO`
- pendiente:
  - alinear respuesta estandar en backend
  - revisar ownership de acciones adicionales legacy que hoy viven en `ventas/index.php`
- validacion de cobertura legacy:
  - parcial
  - faltantes visibles en `ventas/index.php`:
    - `ASIGNAR_PAGOS_DOCUMENTOS`
    - `insertar_producto_venta`
    - `BUSCAR_PRODUCTO_ODOO`
    - `INSERT_PERFIL_USUARIO`
    - `mnbvcxzxcxcxasdfewq15616`
    - `qwer12356yhn7ujm8ik`
    - `52444d9072f7ec12a26cb2879ebb4ab0bf5aa553`
    - `52444d9072f7ec12aJEE8FFJJKVNASDHQWFLKA`
    - `e06c06e7e4ef58bdb0kieujfñ541b3017fdd35473`
  - nota:
    - algunas acciones de caja e inventario tambien aparecen dentro de `ventas/index.php`, por eso no basta revisar solo la carpeta del modulo por nombre

#### DatosIniciales

- estado: migrado y alineado para consumo actual del front
- acciones visibles:
  - `GET_SUCURSAL_PRINCIPAL_DATA`
  - `52444d9072f7ec12a26cb2879ebb4ab0bf5aa553`
  - `52444d9072f7ec12aJEE8FFJJKVNASDHQWFLKA`
  - `23929870008e23007350be74a708ab3a806dce13`
  - `8e9ae038c37d3b59fc1eed456c77aefb5eadffea`
  - `99c505a66a9d8a984059baf1b99bb9e6456ae4bb`
- archivos frontend detectados:
  - `src/app/services/DatosIniciales.services.ts`
  - `src/app/modules/login/pages/login/login.component.ts`
  - `src/app/modules/login/pages/forgotPassWord/forgotPassWord.component.ts`
- nota detallada:
  - `pr-features/08-datosiniciales-legacy-routing/04-BACK-FRONT-ALIGNMENT.md`
- validacion de cobertura legacy:
  - completo por acciones visibles del modulo `datosiniciales/index.php`
- pendiente:
  - confirmar destino funcional final de las acciones legacy sin uso actual confirmado en frontend

#### Vehiculos

- estado: con accion legacy registrada
- accion visible: `CREAR_DOCUMENTO_POR_SERVICIO_VEHICULO`
- validacion de cobertura legacy:
  - completo por acciones visibles del modulo `vehiculos/index.php`
- pendiente:
  - alinear respuesta estandar en backend

#### Inventario

- estado: migrado y alineado con frontend
- cobertura visible:
  - movimientos
  - traslados
  - precargue
  - catalogos
  - crear/actualizar producto
  - busquedas de producto
- archivos frontend detectados:
  - `src/app/services/producto.service.ts`
  - `src/app/modules/admin/pages/productos/productos.component.ts`
  - `src/app/modules/pos/pages/ventas/ventas.component.ts`
  - `src/app/modules/pos/modals/ingresar-producto-venta/ingresar-producto-venta.component.ts`
  - `src/app/modules/pos/modals/ModalUpdateProductoVenta/ModalUpdateProductoVenta.component.ts`
  - `src/app/modules/compras/pages/crear/crearCompra.component.ts`
  - `src/app/modules/compras/pages/editar/editarCompra.component.ts`
  - `src/app/modules/admin/modals/modalUpdateProducto/modalUpdateProducto.component.ts`
- nota detallada:
  - `pr-features/03-inventario-legacy-routing/04-BACK-FRONT-ALIGNMENT.md`
- validacion de cobertura legacy:
  - completo
  - cubiertos:
    - `STOCK_MOVE`
    - `STOCK_MOVE_DEVOLUCION`
    - `TRASLADO_ENTRE_BODEGAS`
    - `GET_CATEGORIAS`
    - `INGRESO_DATOS_DATOS_AUX_INVENTARIO`
    - `GET_BODEGAS`
    - `BORRAR_DATOS_INGRESO_AUX_INVENTARIO`
    - `BUSCAR_TODOS_LOS_PRODUCTOS`
    - `BUSCAR_TODOS_LOS_PRODUCTOS_OLD`
    - `BUSCAR_TODOS_LOS_PRODUCTOS_POR_CATEGORIA`
    - `BUSCAR_TODOS_LOS_PRODUCTOS_POR_MARCA`
    - `BUSCAR_TODOS_LOS_PRODUCTOS_POR_NOMBRE`
    - `BUSCAR_PRODUCTO`
    - `BUSCAR_EXISTENCIA_PRODUCTO`
    - `BUSCAR_PRODUCTO_COD_BARRAS`
    - `INSERTAR_NUEVO_PRODUCTO`
    - `ACTULIZAR_PRODUCTO`
    - `SET_ACTIVIDAD_DESCUENTO`
    - `devolver_producto_venta`

#### Admin

- estado: migrado y alineado con frontend
- acciones visibles:
  - `GET_ALL_RECURSOS`
  - `SET_PERFIL_RECURSO`
  - `GET_ALL_RECURSOS_BY_PERFIL`
  - `CREAR_USUARIO`
  - `CREAR_OPERACION_MANUAL`
  - `CREAR_OPERACIONES_PREESTABLECIDAS`
  - `EJECUTAR_OPERACIONES_PREESTABLECIDAS`
- archivos frontend detectados:
  - `src/app/services/usuario.services.ts`
  - `src/app/services/cntContables.service.ts`
  - `src/app/modules/admin/modules/permisos/pages/perfil/perfil.component.ts`
  - `src/app/modules/shared/components/menu-item-li-check/menu-item-li-check.component.ts`
  - `src/app/modules/admin/modules/permisos/pages/usuario/nuevo/usuario-nuevo.component.ts`
  - `src/app/modules/admin/modules/cuentas-contables/pages/operaciones/pages/crtOperaciones.component.ts`
  - `src/app/modules/admin/modules/traslados-cnt/modals/*`
- nota detallada:
  - `pr-features/04-admin-legacy-routing/04-BACK-FRONT-ALIGNMENT.md`
- validacion de cobertura legacy:
  - completo para las acciones visibles en `administrator/index.php`
  - nota:
    - `OBTENER_USUARIOS`, `ACTUALIZAR_USUARIO` y `OBTENER_MENUS` siguen existiendo como compatibilidad adicional del backend nuevo, pero no forman parte del set principal del legacy revisado

#### Documentos

- estado: parcialmente cerrado en backend nuevo, ya con acciones legacy minimas del flujo actual
- observacion clave:
  - ya no es un stub puro
  - quedaron implementadas y validadas con datos reales las acciones que hoy bloqueaban `ventas` y `compras`
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
- validacion de cobertura legacy:
  - parcial
  - ya cubiertos y probados localmente:
    - `GET_DOCUMENTOS_USUARIO_ACTUAL`
    - `GET_DOCUMENTOS_USUARIO_ACTUAL_CAJA_ACTIVA`
    - `CREAR_DOCUMENTO_POR_USUARIO`
    - `CREAR_DOCUMENTO_COMPRA_POR_USUARIO`
    - `CAMBIAR_DOCUMENTO_ACTIVO_POR_USUARIO`
    - `CAMBIAR_DOCUMENTO_COMPRA_ACTIVO_POR_USUARIO`
  - validacion real hecha:
    - login real contra `http://localhost/jds_back_2026/api/login/`
    - `GET_DOCUMENTOS_USUARIO_ACTUAL_CAJA_ACTIVA` responde `ok: true`
    - `CREAR_DOCUMENTO_POR_USUARIO` responde `ok: true`
    - `CAMBIAR_DOCUMENTO_ACTIVO_POR_USUARIO` responde `ok: true`
  - pendiente para darlo por cerrado:
    - `CREAR_DOCUMENTO_GASTO_POR_USUARIO`
    - `CERRAR_DOCUMENTO_FACTURA`
    - `CERRAR_DOCUMENTO_REMISION`
    - `CAMBIAR_DOCUMENTO_A_ENVIO`
    - `CANCELAR_DOCUMENTO_POR_USUARIO`
    - `CREAR_DOCUMENTO_COTIZACION_POR_USUARIO`
    - `CAMBIAR_DOCUMENTO_POR_CAJA`
    - `ASIGNAR_ABONO_DOCUMENTOS_CREDITO`
    - `ASIGNAR_ABONO_DOCUMENTOS_CREDITO_POR_PAGAR`
    - `GENERAR_DOCUMENTOS_DEVOLUCION`
    - `GENERAR_DOCUMENTOS_NOTA_DEBITO`

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

1. Revalidar cobertura legacy real de todos los modulos ya revisados.
2. Corregir el estado de cada modulo a `completo` o `parcial`.
3. Completar primero faltantes de `datosiniciales` e `inventario`.
4. Despues entrar a `documentos` con mapa legacy real completo.
5. Adaptar frontend solo despues de cerrar el contrato backend del modulo correspondiente.
6. Mantener actualizado este archivo por accion o por modulo en cada PR.
