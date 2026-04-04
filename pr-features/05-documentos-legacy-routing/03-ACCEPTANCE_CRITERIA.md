# Feature-05: Documentos Module Legacy Routing - ACCEPTANCE_CRITERIA

## ✅ Criterios Funcionales

### CA-01: Listar Documentos
**Dado** un usuario autenticado
**Cuando** envía `{"action":"LISTAR_DOCUMENTOS","_usuario_id":123}`
**Entonces** debe retornar un array de documentos autorizados para el usuario

#### Criterios
- ✅ Retorna `success: true`
- ✅ Retorna lista de documentos con campos: `documento_id`, `nombre`, `tipo`, `fecha_creacion`
- ✅ Permite filtrar por `tipo` cuando se especifica
- ✅ Retorna error si el usuario no está autenticado

### CA-02: Subir Documento
**Dado** un usuario autenticado
**Cuando** envía datos completos del documento
**Entonces** debe retornar metadata del documento creado

#### Criterios
- ✅ Valida los campos requeridos
- ✅ Retorna `ID` generado y nombre del archivo
- ✅ Soporta carga de contenido Base64
- ✅ Maneja errores de validación

### CA-03: Descargar Documento
**Dado** un usuario autenticado
**Cuando** envía `{"action":"DESCARGAR_DOCUMENTO","_documento_id":456}`
**Entonces** debe retornar metadata del documento y URL de descarga simulada

#### Criterios
- ✅ Verifica acceso del usuario al documento
- ✅ Retorna `success: true` con `download_url`
- ✅ Retorna error si el documento no existe

### CA-04: Eliminar Documento
**Dado** un usuario autenticado
**Cuando** envía `{"action":"BORRAR_DOCUMENTO","_documento_id":456}`
**Entonces** debe marcar el documento como eliminado

#### Criterios
- ✅ Verifica que el documento exista
- ✅ Retorna confirmación exitosa
- ✅ Retorna error si el documento no pertenece al usuario

## ❌ Criterios de No-Regresión

- CA-NR-01: Las acciones legacy existentes en otros módulos deben seguir funcionando
- CA-NR-02: El formato JSON de respuesta debe mantenerse consistente
- CA-NR-03: No debe romper la lectura de `Routes::map()`

## 🧪 Tests
- TA-01: Escenario completo de listar documentos
- TA-02: Escenario de error por documento no encontrado
- TA-03: Escenario de error de autenticación

## 📏 Condición de Completitud
- [ ] Todos los criterios funcionales pasan
- [ ] Tests unitarios pasan
- [ ] Documentación creada
- [ ] PR listo para revisión
