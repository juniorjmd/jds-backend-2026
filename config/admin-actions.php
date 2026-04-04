<?php

/**
 * Mapea acciones legacy del módulo Admin
 *
 * Estructura: 'hash_accion' => [ControllerClass::class, 'methodName']
 * El Router usa este mapeo para resolver acciones sin /api en la URL
 */

use App\Modules\Admin\AdminController;

return [
    'OBTENER_USUARIOS' => [AdminController::class, 'getUsers'],
    'CREAR_USUARIO' => [AdminController::class, 'createUser'],
    'ACTUALIZAR_USUARIO' => [AdminController::class, 'updateUser'],
    'OBTENER_MENUS' => [AdminController::class, 'getMenus'],
];