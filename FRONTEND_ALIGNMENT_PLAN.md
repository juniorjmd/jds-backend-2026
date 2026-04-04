# Plan De Alineacion Frontend

## Objetivo

Mantener respuestas estandar en el backend nuevo y adaptar el frontend para consumirlas modulo por modulo.

No se debe perpetuar el contrato legacy como formato final de salida.

## Regla de trabajo

Por cada modulo revisado en backend:

1. revisar acciones legacy del modulo
2. validar entradas legacy aceptadas por backend
3. definir o confirmar respuesta estandar del backend nuevo
4. localizar en frontend los servicios, componentes o guards que consumen ese modulo
5. documentar archivos frontend afectados
6. hacer PR backend
7. hacer PR frontend

## Repositorios

### Backend

- repo local: `jds-backend-app-2026`

### Frontend

- repo objetivo indicado por producto: `https://github.com/juniorjmd/jds-frontend-2026.git`
- observacion actual: el repo local `jds-carwash-front` tiene remoto `origin` apuntando a `https://github.com/juniorjmd/jds_carwash.git`
- accion pendiente: confirmar o actualizar remoto antes de enviar cambios del frontend

## Consumo actual del frontend

Patrones visibles hoy en el frontend:

- validaciones como `value.error === 'ok'`
- uso de `value.data`
- uso de `value.numdata`
- manejo de error como `e.error.error`

Eso implica que cada migracion de modulo debe incluir:

- adaptacion del parser de respuesta
- ajuste de manejo de errores
- validacion de estructuras anidadas

## Formato de documentacion por modulo

Cada modulo debe dejar una nota con:

- acciones backend revisadas
- archivos backend modificados
- archivos frontend a modificar
- cambio esperado en lectura de respuesta
- riesgo de compatibilidad
- PR backend
- PR frontend

## Primer modulo a revisar con esta metodologia

- `auth`

Archivos frontend inicialmente detectados:

- `src/app/services/login.services.ts`
- `src/app/models/app.db.actions.ts`

Se deben descubrir mas consumidores reales durante la revision del modulo.
