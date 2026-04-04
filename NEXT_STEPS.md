# Siguientes Pasos

## Estado actual

- Branch activo: `feature/documentos-legacy-routing`
- Ultimo commit subido: `734aef6` (`align personas and ventas legacy responses`)
- Suite actual: `19/19` pasando con `php tests/run-tests.php`
- No subir: `.env`

## Nota de revalidacion

La revision transversal de cobertura legacy mostro que no basta con que un modulo exista o tenga algunos endpoints migrados.

Conclusiones:

- `personas` y `vehiculos` se ven completos por las acciones visibles en sus entrypoints legacy
- `auth` y `admin` ya quedaron cerrados contra la cobertura legacy visible revisada
- `carwash`, `ventas`, `datosiniciales`, `inventario` y `documentos` siguen parciales
- por tanto no se debe asumir que un modulo "ya esta alineado" solo porque tenga consumers frontend adaptados

## Criterio actualizado de migracion

Antes de seguir sumando modulos nuevos, mantener estas reglas:

- completar primero los modulos actuales hasta poder reemplazar el legacy de forma directa
- dejar de trabajar por slices funcionales del frontend y pasar a cobertura completa por modulo
- validar entradas y respuestas de todo endpoint legacy ya migrado
- mantener respuestas estandar en el backend nuevo
- adaptar el frontend para leer el contrato estandar del backend nuevo
- migrar todos los endpoints legacy del modulo aunque hoy no los consuma el frontend
- documentar por endpoint si esta:
  - usado por el frontend actual
  - migrado pero pendiente de validar contrato
  - migrado y validado
  - presente solo en legacy
  - sin evidencia de uso actual
- priorizar primero compatibilidad de contrato legacy y despues limpieza/eliminacion

Documento de apoyo:

- `LEGACY_ENDPOINT_AUDIT.md`
- `FRONTEND_ALIGNMENT_PLAN.md`

## Pendiente prioritario

### 1. Cerrar modulos actuales para reemplazo directo

Antes de abrir mas cobertura en modulos nuevos, cerrar los modulos ya existentes en backend nuevo:

- `auth`
- `carwash`
- `personas`
- `ventas`
- `datosiniciales`
- `vehiculos`
- `inventario`
- `documentos`

Objetivo por modulo:

- que todas las acciones legacy del modulo existan en backend nuevo
- que acepten los mismos parametros legacy
- que devuelvan respuestas estandar del backend nuevo
- que tengan tests de parametros y tests de servicio
- que tengan identificado el ajuste requerido en frontend
- que queden listos para reemplazar el modulo legacy sin arrastrar malas practicas del contrato anterior

Validar por accion:

- parametros legacy aceptados por `Request`
- existencia real contra el entrypoint legacy del modulo
- nombre y forma de campos de respuesta estandar
- diferencias contra legacy real aunque el front no las use hoy
- tests de parametros y tests de servicio por modulo
- archivos frontend que consumen la accion
- cambio necesario en frontend para leer respuesta estandar
- PR backend y PR frontend asociados al modulo

### 2. Documentos

Es el modulo con mayor brecha y el principal faltante para reemplazo directo.

Acciones del frontend que hoy faltan o estan incompletas en backend:

- `GET_DOCUMENTOS_USUARIO_ACTUAL`
- `GET_DOCUMENTOS_USUARIO_ACTUAL_CAJA_ACTIVA`
- `CREAR_DOCUMENTO_POR_USUARIO`
- `CREAR_DOCUMENTO_COMPRA_POR_USUARIO`
- `CREAR_DOCUMENTO_GASTO_POR_USUARIO`
- `CERRAR_DOCUMENTO_FACTURA`
- `CERRAR_DOCUMENTO_REMISION`
- `CAMBIAR_DOCUMENTO_ACTIVO_POR_USUARIO`
- `CAMBIAR_DOCUMENTO_COMPRA_ACTIVO_POR_USUARIO`
- `CAMBIAR_DOCUMENTO_POR_CAJA`
- `CAMBIAR_DOCUMENTO_A_ENVIO`
- `CANCELAR_DOCUMENTO_POR_USUARIO`
- `CREAR_DOCUMENTO_COTIZACION_POR_USUARIO`
- `ASIGNAR_ABONO_DOCUMENTOS_CREDITO_POR_PAGAR`
- `GENERAR_DOCUMENTOS_DEVOLUCION`
- `GENERAR_DOCUMENTOS_NOTA_DEBITO`

Contrato de transicion a revisar:

- hoy el frontend espera respuestas legacy planas
- el backend nuevo debe converger a respuestas estandar
- el frontend debe adaptarse modulo por modulo para leer ese contrato estandar

Siguiente entrega recomendada:

- crear `pr-features/13-documentos-complete-legacy-coverage/`
- completar primero el mapa legacy real del modulo
- implementar todas las acciones legacy visibles del modulo
- registrar en la auditoria el estado de cada endpoint del modulo
- documentar archivos frontend afectados y PR del frontend
- agregar/ajustar tests unitarios

### 3. Cobertura total legacy

Despues de `documentos`, completar los modulos actuales que ya existen en backend nuevo hasta dejarlos cerrados por cobertura total.

Focos visibles en esta revision:

- `carwash` ya tiene contrato estandar alineado con frontend pero sigue con logica simulada
- `ventas` sigue parcial frente al legacy visible en `ventas/index.php`
- `datosiniciales` sigue parcial frente al legacy visible en `datosiniciales/index.php`
- `inventario` sigue parcial y ademas con contrato backend aun no estandarizado
- `documentos` todavia no tiene mapa legacy real en `config/documentos-actions.php`
- hay acciones genericas de base de datos y acciones Odoo/reportes sin mapear en backend nuevo
- hay que separar claramente endpoints usados por el front de endpoints heredados sin consumo actual confirmado
- algunos modulos legacy todavia no tienen reflejo directo por nombre en el backend nuevo y habra que decidir su destino despues de cerrar los modulos actuales

## Secuencia recomendada para la siguiente pasada

1. Revalidar cobertura legacy real de cada modulo ya revisado
2. Completar faltantes de `datosiniciales`
3. Completar faltantes de `inventario`
4. Estandarizar respuestas backend del modulo que se cierre
5. Adaptar frontend del modulo cerrado
6. Despues entrar a `documentos`
7. Solo despues de cerrar modulos actuales, revisar acciones genericas y modulos legacy sin reflejo directo
8. Ejecutar:
   - `php tests/Unit/DocumentosServiceTest.php`
   - `php tests/Unit/DocumentosParametersTest.php`
   - `php tests/run-tests.php`

## Nota importante

Seguir manteniendo estas reglas:

- no quitar endpoints legacy por ahora
- backend nuevo con respuesta estandar, no replica permanente del payload legacy
- cada endpoint migrado debe quedar con validacion de entradas y respuesta estandar definida
- cada modulo actual debe quedar completo antes de abrir otra migracion grande
- cada cambio importante debe quedar en su carpeta `pr-features/XX-*`
- cada entrega por modulo debe quedar con tests y listo para PR
- cada modulo revisado debe dejar documentado su cambio correspondiente en frontend
- backend y frontend deben salir en PRs separados
- toda exclusion o falta de uso debe quedar documentada, no asumida
