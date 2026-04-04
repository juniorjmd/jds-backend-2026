# JDS Backend Migration Guide

## Objetivo
Migrar gradualmente el backend legacy `bk.services` a un nuevo backend modular en PHP sin romper el frontend actual.

## Estado actual del proyecto nuevo
El backend nuevo ya tiene:

- composer con autoload PSR-4: `App\\ => app/`
- `public/index.php`
- `Request`
- `Response`
- `Router`
- `.env` + `Connection`
- `BaseRepository`
- `QueryBuilder`
- soporte base para expresiones/subqueries
- módulo `Auth` parcialmente migrado:
  - `login` funcionando
  - `validatekey` funcionando

## Arquitectura obligatoria
Todo módulo nuevo debe seguir esta estructura:

- `Controller`
- `Service`
- `Repository`
- `Queries/` para consultas complejas

Flujo obligatorio:

HTTP
-> Router
-> Controller
-> Service
-> Repository
-> Database

## Reglas obligatorias
1. No escribir SQL en controllers.
2. Repositories solo acceden a base de datos.
3. Services contienen lógica de negocio.
4. Queries complejas deben vivir en `Queries/`.
5. No recrear el monstruo legacy `DATABASE_GENERIC_CONTRUCT_*` en un solo archivo.
6. `QueryBuilder` debe mantenerse pequeño; piezas complejas van en clases auxiliares.
7. Identificadores SQL deben escaparse con backticks.
8. Mantener compatibilidad con el backend legacy mientras dure la migración.
9. No romper `login` ni `validatekey`, que ya funcionan.
10. No exponer DSN, credenciales ni debug en respuestas HTTP.

## Backend legacy a migrar
El backend anterior vive en `services/view/action/` y tiene estos módulos:

- administrator
- csv_manager
- datosiniciales
- documentos
- inventario
- login
- personas
- up_csv
- up_csv_answ
- vehiculos
- ventas

## Mapa de migración
Orden recomendado:

1. Auth
2. DatosIniciales
3. Inventario
4. Ventas
5. Documentos
6. Admin
7. Personas
8. Vehiculos
9. CsvManager

## Auth legacy ya identificado
Acciones relevantes del backend viejo:

- `ef2e1d89937fba9f888516293ab1e19e7ed789a5` -> login
- `16770d92a6a82ee846f7ff23b4c8ad05b69fba03` -> validatekey
- `16770d92a6a82ee8464f678f5f223b4c8ad05b69fba03` -> me
- `RESETEAR_USUARIO_PASS`
- `HIJODELAGRANCHINGADA` -> setpassword

## Login real ya migrado
El login legacy hace esto:

1. recibe usuario y password
2. aplica `sha1(password)`
3. genera `llave = sha1(usuario + fecha)`
4. ejecuta `CALL sp_login(:_usuario, :_pass, :_llave)`
5. interpreta `_result`
6. en éxito llama `sp_actualizar_cuotas_vencidas()`
7. consulta permisos desde `vw_perfil_recurso`

Eso ya está migrado y funcionando en el módulo `Auth`.

## ValidateKey real ya migrado
La validación de llave usa `vw_session` y ya funciona.

## Lo que falta inmediatamente
Terminar el módulo `Auth` con:

- `me`
- `resetpassword`
- `setpassword`
- `AuthContext`

## Convenciones de endpoints nuevos
Endpoints nuevos:

- `/api/auth`
- `/api/auth/login`
- `/api/auth/validatekey`
- `/api/auth/me`

Reglas de URL:

- prefijo obligatorio `/api`
- máximo `/api/modulo/metodo`
- params van en query o JSON body, nunca en path params

## Qué hacer al migrar
1. leer la lógica del módulo legacy
2. identificar tablas, vistas, procedures
3. mover SQL a Repository o Query Object
4. mantener respuesta consistente con `Response::ok()` / `Response::fail()`
5. evitar duplicación

## Definition of done
Una migración de módulo se considera terminada solo si:

- compila
- respeta la arquitectura
- endpoint responde correctamente
- no rompe login existente
- no deja debug activo
- deja código legible y modular