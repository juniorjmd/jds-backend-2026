<?php
declare(strict_types=1);

namespace App\Core\Routing;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Modules\Auth\AuthContext;
use App\Modules\Documentos\DocumentosController;
use App\Modules\Documentos\Services\DocumentosService;

final class Router
{
    public function __construct(
        private array $actionMap
    ) {}

    public function dispatch(Request $request): mixed
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);

        if (!$path) {
            Response::fail('INVALID_ROUTE', 'Ruta inválida', 404);
        }

        $path = strtolower($path);
        $segments = explode('/', trim($path, '/'));
        $apiIndex = array_search('api', $segments, true);

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

        if ($module && $method) {
            $controllerClass = "App\\Modules\\" . ucfirst($module) . "\\" . ucfirst($module) . "Controller";

            if (!class_exists($controllerClass)) {
                Response::fail(
                    'MODULE_NOT_FOUND',
                    "Módulo {$module} no existe",
                    404
                );
            }

            $controller = $this->createController($controllerClass, $request);

            if (!method_exists($controller, $method)) {
                Response::fail(
                    'METHOD_NOT_FOUND',
                    "Método {$method} no existe",
                    404
                );
            }

            return $controller->$method($request);
        }

        if ($module && !$method) {
            $controllerClass = "App\\Modules\\" . ucfirst($module) . "\\" . ucfirst($module) . "Controller";

            if (class_exists($controllerClass)) {
                $controller = $this->createController($controllerClass, $request);

                if (method_exists($controller, 'index')) {
                    return $controller->index($request);
                }
            }
        }

        return $this->dispatchLegacyAction($request);
    }

    private function dispatchLegacyAction(Request $request): mixed
    {
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

        if (is_array($handler)) {
            return $this->instantiateAndCall($handler, $request);
        }

        return $handler($request);
    }

    private function instantiateAndCall(array $handler, Request $request): mixed
    {
        [$class, $method] = $handler;

        $instance = match ($class) {
            DocumentosController::class => $this->createDocumentosController($request),
            default => throw new \Exception("No factory para {$class}"),
        };

        return $instance->$method();
    }

    private function createController(string $controllerClass, Request $request): object
    {
        return match ($controllerClass) {
            DocumentosController::class => $this->createDocumentosController($request),
            default => new $controllerClass(),
        };
    }

    private function createDocumentosController(Request $request): DocumentosController
    {
        $authContext = new AuthContext();
        $service = new DocumentosService($request, $authContext);

        return new DocumentosController($request, $service);
    }
}
