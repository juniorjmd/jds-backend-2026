<?php
declare(strict_types=1);

namespace App\Core\Routing;

use App\Core\Http\Request;
use App\Core\Http\Response;

final class Router
{
    public function __construct(
        private array $actionMap
    ) {}

    public function dispatch(Request $request): mixed
    {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if (!$path) {
            Response::fail('INVALID_ROUTE', 'Ruta inválida', 404);
        }

        /*
         * |--------------------------------------------------------------------------
         * | Remover base path del proyecto
         * |--------------------------------------------------------------------------
         */

        $path = strtolower($path);

        $segments = explode('/', trim($path, '/'));
        
        $apiIndex = array_search('api', $segments);

        // Si no hay /api, ir directo a Legacy Action Router
        if ($apiIndex === false) {
            return $this->dispatchLegacyAction($request);
        }

        $segments = array_slice($segments, $apiIndex);

        if (count($segments) > 3) {
            Response::fail(
                'INVALID_ROUTE',
                'Demasiados segmentos en la ruta',
                400
            );
        }

        $module = $segments[1] ?? null;
        $method = $segments[2] ?? null;

        /*
        |--------------------------------------------------------------------------
        | Endpoint Router
        |--------------------------------------------------------------------------
        */

        if ($module && $method) {

            $controllerClass = "App\\Modules\\" . ucfirst($module) . "\\" . ucfirst($module) . "Controller";

            if (!class_exists($controllerClass)) {
                Response::fail(
                    'MODULE_NOT_FOUND',
                    "Módulo {$module} no existe",
                    404
                );
            }

            $controller = new $controllerClass();

            if (!method_exists($controller, $method)) {
                Response::fail(
                    'METHOD_NOT_FOUND',
                    "Método {$method} no existe",
                    404
                );
            }

            return $controller->$method($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Module root (ej: /api/health)
        |--------------------------------------------------------------------------
        */

        if ($module && !$method) {

            $controllerClass = "App\\Modules\\" . ucfirst($module) . "\\" . ucfirst($module) . "Controller";

            if (class_exists($controllerClass)) {

                $controller = new $controllerClass();

                if (method_exists($controller, 'index')) {
                    return $controller->index($request);
                }
            }
        }

        // Si llega aquí, ir a legacy action router
        return $this->dispatchLegacyAction($request);
    }

    private function dispatchLegacyAction(Request $request): mixed
    {
        /*
        |--------------------------------------------------------------------------
        | Legacy Action Router
        |--------------------------------------------------------------------------
        */

        $action = $request->action();

        if (!$action) {
            Response::fail(
                'MISSING_ACTION',
                'No se envió action',
                400
            );
        }

        $handler = $this->actionMap[$action] ?? null;

        if (!$handler) {
            Response::fail(
                'ACTION_NOT_FOUND',
                "Acción no registrada: {$action}",
                404
            );
        }

        return $handler($request);
    }
}