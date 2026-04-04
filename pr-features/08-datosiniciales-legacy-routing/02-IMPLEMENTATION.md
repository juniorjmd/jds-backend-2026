# Feature-08: DatosIniciales Module Legacy Routing - IMPLEMENTATION

## Arquitectura Implementada

### Estructura de Archivos
```
app/Modules/DatosIniciales/
├── DatosInicialesController.php
├── Repositories/
│   └── DatosInicialesRepository.php
└── Services/
    └── DatosInicialesService.php

config/
└── datosiniciales-actions.php
```

## Cambios Técnicos
- `Routes.php`: carga `config/datosiniciales-actions.php`
- `Router.php`: añade factory `createDatosInicialesController()`
- `DatosInicialesRepository`: consulta real sobre `vw_sucursales`
- `DatosInicialesController`: devuelve payload legacy directo para no romper el frontend que espera un arreglo simple

## Alcance Migrado
- `GET_SUCURSAL_PRINCIPAL_DATA`

## No Migrado en Este Módulo
- `52444d9072f7ec12a26cb2879ebb4ab0bf5aa553`
- `52444d9072f7ec12aJEE8FFJJKVNASDHQWFLKA`
- `23929870008e23007350be74a708ab3a806dce13`
- `8e9ae038c37d3b59fc1eed456c77aefb5eadffea`
- `99c505a66a9d8a984059baf1b99bb9e6456ae4bb`

Motivo:
No se encontró consumo directo de estas acciones en `jds-carwash-front/src`, así que se excluyen para evitar migrar código muerto o de uso no confirmado.
