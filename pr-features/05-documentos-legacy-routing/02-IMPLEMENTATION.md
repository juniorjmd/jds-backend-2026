# Feature-05: Documentos Module Legacy Routing - IMPLEMENTATION

## 🏗️ Arquitectura Implementada

### Estructura de Archivos
```
app/Modules/Documentos/
├── DocumentosController.php      # Controlador de acciones legacy documentales
├── Services/
│   └── DocumentosService.php     # Lógica de negocio del módulo Documentos
└── Repositories/                 # Carpeta reservada para futura integración con BD

config/
└── documentos-actions.php        # Mapeo de acciones legacy del módulo Documentos
```

### Componentes Principales

#### DocumentosController
- **Constructor**: Recibe Request y DocumentosService
- **Métodos**:
  - `listDocuments()`
  - `uploadDocument()`
  - `downloadDocument()`
  - `deleteDocument()`
- **Respuestas**: Usa `Response::ok()` y `Response::fail()` para consistencia

#### DocumentosService
- **Constructor**: Recibe Request y AuthContext
- **Autenticación**: Usa `AuthContext::resolve()` para validar al usuario
- **Validaciones**: Verifica acceso al documento
- **Lógica**: Implementa operaciones simuladas con TODO de BD

#### Configuración
- `documentos-actions.php`: Mapea acciones legacy a `[DocumentosController::class, 'method']`
- `Routes.php`: Carga automáticamente `documentos-actions.php`
- `Router.php`: Añade fábrica `createDocumentosController()`

## 🔄 Flujo de Ejecución

### Caso: Listar documentos
1. Frontend envía `POST /` con `{"action":"LISTAR_DOCUMENTOS","_usuario_id":123}`
2. Router detecta acción legacy y busca en `documentos-actions.php`
3. Router instancia `DocumentosController`
4. `DocumentosController::listDocuments()` ejecuta lógica
5. `DocumentosService` valida autenticación y acceso
6. Responde JSON compatible con frontend legacy

## 🔧 Cambios Técnicos

### Router.php
- Añade import de `DocumentosController` y `DocumentosService`
- Añade `DOCUMENTOSController::class` en `instantiateAndCall()`
- Añade `createDocumentosController()`

### Routes.php
- Añade carga de `config/documentos-actions.php`
- Usa `require_once` para cumplir preferencia de carga segura

### Configuración Legacy
- `config/documentos-actions.php` mapea las 4 acciones

## 🧪 Tests Añadidos
- `DocumentosParametersTest`: valida parámetros legacy
- `DocumentosServiceTest`: prueba lógica de negocio y validaciones

## 🚀 Compatibilidad
- Mantiene el mismo formato legacy de parámetros y respuestas
- Añade compatibilidad para el módulo Documentos sin romper el backend moderno
