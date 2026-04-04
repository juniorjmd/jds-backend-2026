# Feature-04: Admin Module Legacy Routing - SPECS

## 📋 Objetivo
Implementar compatibilidad backward-compatible para acciones legacy del módulo Admin, permitiendo que el frontend actual continúe funcionando mientras se mantiene la gestión de usuarios, menús y permisos.

## 🎯 Requisitos Funcionales

### RF-01: Mapeo de Acciones Legacy
El sistema debe mapear 4 acciones legacy del módulo Admin a sus handlers:

| Acción | Hash/ID | Método Esperado | Descripción |
|--------|---------|-----------------|-------------|
| Get Users | `OBTENER_USUARIOS` | `AdminController::getUsers()` | Obtiene lista de usuarios del sistema |
| Create User | `CREAR_USUARIO` | `AdminController::createUser()` | Crea un nuevo usuario en el sistema |
| Update User | `ACTUALIZAR_USUARIO` | `AdminController::updateUser()` | Actualiza información de usuario |
| Get Menus | `OBTENER_MENUS` | `AdminController::getMenus()` | Obtiene estructura de menús del sistema |

### RF-02: Parámetros Legacy
El módulo Admin debe aceptar parámetros siguiendo el mismo patrón de Auth:

**Get Users**
```json
{
  "action": "OBTENER_USUARIOS",
  "_estado": "A"
}
```

**Create User**
```json
{
  "action": "CREAR_USUARIO",
  "_login": "newuser",
  "_nombre1": "Juan",
  "_apellido1": "Pérez",
  "_mail": "juan@example.com",
  "_id_perfil": 1
}
```

**Update User**
```json
{
  "action": "ACTUALIZAR_USUARIO",
  "_id": 123,
  "_estado": "A",
  "_id_perfil": 2
}
```

**Get Menus**
```json
{
  "action": "OBTENER_MENUS",
  "_id_perfil": 1
}
```

## 🔒 Requisitos de Seguridad
- Todas las acciones requieren usuario autenticado con permisos administrativos
- Validación de permisos por perfil antes de ejecutar operaciones
- Auditoría de cambios en usuarios y permisos

## 📊 Requisitos No Funcionales
- Respuestas en formato JSON compatible con frontend legacy
- Manejo de errores consistente
- Performance aceptable para operaciones administrativas