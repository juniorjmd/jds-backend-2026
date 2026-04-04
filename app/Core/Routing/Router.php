<?php
declare(strict_types=1);

namespace App\Core\Routing;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Modules\Admin\AdminController;
use App\Modules\Admin\Services\AdminService;
use App\Modules\Auth\AuthContext;
use App\Modules\Carwash\CarwashController;
use App\Modules\Carwash\Services\CarwashService;
use App\Modules\Documentos\DocumentosController;
use App\Modules\Documentos\Services\DocumentosService;
use App\Modules\DatosIniciales\DatosInicialesController;
use App\Modules\DatosIniciales\Services\DatosInicialesService;
use App\Modules\Inventario\InventarioController;
use App\Modules\Inventario\Services\InventarioService;
use App\Modules\Personas\PersonasController;
use App\Modules\Personas\Services\PersonasService;
use App\Modules\Ventas\VentasController;
use App\Modules\Ventas\Services\VentasService;
use App\Modules\Vehiculos\VehiculosController;
use App\Modules\Vehiculos\Services\VehiculosService;

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

        /*
        |--------------------------------------------------------------------------
        | Endpoint Router
        |--------------------------------------------------------------------------
        */

        if ($module && $method) {

            $controllerClass = $this->resolveControllerClass($module);

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

        /*
        |--------------------------------------------------------------------------
        | Module root (ej: /api/health)
        |--------------------------------------------------------------------------
        */

        if ($module && !$method) {

            $controllerClass = $this->resolveControllerClass($module);

            if (class_exists($controllerClass)) {

                $controller = $this->createController($controllerClass, $request);

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
            AdminController::class => $this->createAdminController($request),
            CarwashController::class => $this->createCarwashController($request),
            InventarioController::class => $this->createInventarioController($request),
            DocumentosController::class => $this->createDocumentosController($request),
            VentasController::class => $this->createVentasController($request),
            PersonasController::class => $this->createPersonasController($request),
            DatosInicialesController::class => $this->createDatosInicialesController($request),
            VehiculosController::class => $this->createVehiculosController($request),
            default => throw new \Exception("No factory for {$class}"),
        };

        return $instance->$method();
    }

    private function createAdminController(Request $request): AdminController
    {
        $authContext = new AuthContext();
        $service = new AdminService($request, $authContext);
        return new AdminController($request, $service);
    }

    private function createCarwashController(Request $request): CarwashController
    {
        $authContext = new AuthContext();
        $service = new CarwashService($request, $authContext);
        return new CarwashController($request, $service);
    }

    private function createInventarioController(Request $request): InventarioController
    {
        $authContext = new AuthContext();
        $service = new InventarioService($request, $authContext);
        return new InventarioController($request, $service);
    }

    private function createController(string $controllerClass, Request $request): object
    {
        return match ($controllerClass) {
            AdminController::class => $this->createAdminController($request),
            CarwashController::class => $this->createCarwashController($request),
            InventarioController::class => $this->createInventarioController($request),
            DocumentosController::class => $this->createDocumentosController($request),
            VentasController::class => $this->createVentasController($request),
            PersonasController::class => $this->createPersonasController($request),
            DatosInicialesController::class => $this->createDatosInicialesController($request),
            VehiculosController::class => $this->createVehiculosController($request),
            default => new $controllerClass(),
        };
    }

    private function createDocumentosController(Request $request): DocumentosController
    {
        $authContext = new AuthContext();
        $service = new DocumentosService($request, $authContext);

        return new DocumentosController($request, $service);
    }

    private function createVentasController(Request $request): VentasController
    {
        $authContext = new AuthContext();
        $service = new VentasService($request, $authContext);

        return new VentasController($request, $service);
    }

    private function createPersonasController(Request $request): PersonasController
    {
        $authContext = new AuthContext();
        $service = new PersonasService($request, $authContext);

        return new PersonasController($request, $service);
    }

    private function createDatosInicialesController(Request $request): DatosInicialesController
    {
        $service = new DatosInicialesService();

        return new DatosInicialesController($request, $service);
    }

    private function createVehiculosController(Request $request): VehiculosController
    {
        $authContext = new AuthContext();
        $service = new VehiculosService($request, $authContext);

        return new VehiculosController($request, $service);
    }

    private function resolveControllerClass(string $module): string
    {
        return match (strtolower($module)) {
            'admin' => AdminController::class,
            'carwash' => CarwashController::class,
            'inventario' => InventarioController::class,
            'documentos' => DocumentosController::class,
            'ventas' => VentasController::class,
            'personas' => PersonasController::class,
            'datosiniciales', 'datos-iniciales' => DatosInicialesController::class,
            'vehiculos' => VehiculosController::class,
            default => "App\\Modules\\" . ucfirst($module) . "\\" . ucfirst($module) . "Controller",
        };
    }
}
