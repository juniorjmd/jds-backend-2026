# Feature-04: Admin Module Legacy Routing - ACCEPTANCE_CRITERIA

## ✅ Criterios Funcionales

### CA-01: Get Users
**Dado** un usuario autenticado con permisos administrativos
**Cuando** envía `{"action": "OBTENER_USUARIOS", "_estado": "A"}`
**Entonces** debe retornar lista de usuarios activos en formato JSON

**Criterios de aceptación:**
- ✅ Retorna array de usuarios con campos: ID, Login, Nombre, Estado
- ✅ Filtra por estado cuando se proporciona `_estado`
- ✅ Retorna error si usuario no tiene permisos
- ✅ Formato JSON compatible con frontend legacy

### CA-02: Create User
**Dado** un usuario administrador autenticado
**Cuando** envía datos completos de nuevo usuario
**Entonces** debe crear usuario y retornar confirmación

**Criterios de aceptación:**
- ✅ Valida campos requeridos: _login, _nombre1, _apellido1, _mail
- ✅ Genera ID único para nuevo usuario
- ✅ Asigna perfil por defecto si no se especifica
- ✅ Retorna datos del usuario creado
- ✅ Maneja errores de validación (email duplicado, login existente)

### CA-03: Update User
**Dado** un usuario administrador autenticado
**Cuando** envía ID de usuario y campos a actualizar
**Entonces** debe modificar usuario y retornar confirmación

**Criterios de aceptación:**
- ✅ Valida que usuario existe
- ✅ Actualiza solo campos proporcionados
- ✅ Mantiene integridad referencial (perfiles existentes)
- ✅ Retorna usuario actualizado
- ✅ Previene actualización de usuarios críticos (admin master)

### CA-04: Get Menus
**Dado** un usuario autenticado
**Cuando** solicita menús disponibles para su perfil
**Entonces** debe retornar estructura jerárquica de menús

**Criterios de aceptación:**
- ✅ Retorna menús ordenados por jerarquía (PadreId)
- ✅ Filtra menús según permisos del perfil
- ✅ Incluye campos: idmenus, Descripcion, Icono, Url, Orden
- ✅ Estructura anidada para submenús

## ❌ Criterios de No-Regresión

### CA-NR-01: Autenticación
**Dado** cualquier acción del módulo Admin
**Cuando** se ejecuta sin usuario autenticado
**Entonces** debe retornar error de autenticación

### CA-NR-02: Permisos
**Dado** un usuario sin permisos administrativos
**Cuando** intenta ejecutar acción administrativa
**Entonces** debe retornar error de permisos insuficientes

### CA-NR-03: Formato de Respuesta
**Dado** cualquier respuesta exitosa
**Cuando** se examina el formato JSON
**Entonces** debe mantener compatibilidad con frontend legacy

## ⚡ Criterios de Performance

### CA-PERF-01: Tiempo de Respuesta
**Dado** una consulta de usuarios típica (100 usuarios)
**Cuando** se mide el tiempo de respuesta
**Entonces** debe ser inferior a 500ms

### CA-PERF-02: Memoria
**Dado** operaciones de listado
**Cuando** se ejecutan múltiples consultas concurrentes
**Entonces** el uso de memoria debe ser eficiente (< 50MB por proceso)

## 🔒 Criterios de Seguridad

### CA-SEC-01: Validación de Entrada
**Dado** parámetros de entrada potencialmente maliciosos
**Cuando** se procesan
**Entonces** deben ser sanitizados y validados

### CA-SEC-02: Control de Acceso
**Dado** un usuario con perfil limitado
**Cuando** intenta acceder a datos de otros usuarios
**Entonces** debe ser bloqueado

## 🧪 Tests de Aceptación

### TA-01: Escenario Completo Get Users
```bash
# Setup: Usuario admin autenticado
curl -X POST / \
  -H "Content-Type: application/json" \
  -d '{"action": "OBTENER_USUARIOS", "_estado": "A"}'

# Expected: 200 OK con array de usuarios
{
  "success": true,
  "data": [
    {
      "ID": 1,
      "Login": "admin",
      "Nombre": "Administrador",
      "estado": "A"
    }
  ]
}
```

### TA-02: Escenario Error de Permisos
```bash
# Setup: Usuario sin permisos admin
curl -X POST / \
  -H "Content-Type: application/json" \
  -d '{"action": "OBTENER_USUARIOS"}'

# Expected: Error de permisos
{
  "success": false,
  "error": "Permisos insuficientes"
}
```

## 📏 Condición de Completitud

**✅ LISTO PARA PRODUCCIÓN** cuando:
- [ ] Todos los criterios funcionales pasan
- [ ] Todos los criterios de no-regresión pasan
- [ ] Tests unitarios cubren > 80% del código
- [ ] Tests de integración pasan
- [ ] Documentación actualizada
- [ ] Code review aprobado
- [ ] Despliegue en staging exitoso