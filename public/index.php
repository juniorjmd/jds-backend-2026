<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Routing\Router;
use App\Bootstrap\Routes; 



try {
    $dotenv = Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();
    $request = Request::fromGlobals();

    // CORS + preflight (mínimo)
    if ($request->method() === 'OPTIONS') {
        Response::noContent();
        return;
    }

    $router = new Router(Routes::map());
    $result = $router->dispatch($request);

    Response::ok($result);

} catch (Throwable $e) {
    // En dev podrías devolver detalle, en prod solo mensaje genérico
   $isDebug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';

    Response::fail(
        'INTERNAL_ERROR',
        'Error interno del servidor',
        500,
        $isDebug ? [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
        ] : null
    );
}