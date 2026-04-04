<?php
declare(strict_types=1);

namespace App\Bootstrap;

use App\Core\Http\Request;

final class Routes
{
<<<<<<< HEAD
=======

>>>>>>> origin/main
    private static ?array $map = null;

    /** @return array<string, callable> */
    public static function map(): array
    {
        if (self::$map !== null) {
            return self::$map;
        }

        $legacyActions = (static function() {
            return require_once __DIR__ . '/../../config/actions.php';
        })();

        $documentosActions = (static function() {
            return require_once __DIR__ . '/../../config/documentos-actions.php';
        })();

        $internalActions = [
            'PING' => function (Request $request) {
                return [
                    'pong' => true,
                    'action' => $request->action(),
                    'time' => date('c'),
                ];
            },
=======
        // Cargar archivos de configuración de acciones legacy
        $legacyActions = (static function() {
            return require __DIR__ . '/../../config/actions.php';
        })();

        $adminActions = (static function() {
            return require __DIR__ . '/../../config/admin-actions.php';
        })();

        $carwashActions = (static function() {
            return require __DIR__ . '/../../config/carwash-actions.php';
        })();

        $inventarioActions = (static function() {
            return require __DIR__ . '/../../config/inventario-actions.php';
        })();

        $documentosActions = (static function() {
            return require __DIR__ . '/../../config/documentos-actions.php';
        })();

        $internalActions = [
            'PING' => function (Request $req) {
                return [
                    'pong' => true,
                    'action' => $req->action(),
                    'time' => date('c'),
                ];
            },

        ];

        self::$map = array_merge(
            $internalActions,
            is_array($legacyActions) ? $legacyActions : [],
            is_array($adminActions) ? $adminActions : [],
            is_array($carwashActions) ? $carwashActions : [],
            is_array($inventarioActions) ? $inventarioActions : [],
            is_array($documentosActions) ? $documentosActions : []
        );

        return self::$map;
    }
}
            is_array($carwashActions) ? $carwashActions : [],
            is_array($inventarioActions) ? $inventarioActions : []
        );

        return self::$map; 
    }
}
>>>>>>> origin/main
