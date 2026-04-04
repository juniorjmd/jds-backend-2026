<?php
declare(strict_types=1);

namespace App\Bootstrap;

use App\Core\Http\Request;

final class Routes
{
    private static ?array $map = null;

    /** @return array<string, callable> */
    public static function map(): array
    {
        if (self::$map !== null) {
            return self::$map;
        }

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

        $ventasActions = (static function() {
            return require __DIR__ . '/../../config/ventas-actions.php';
        })();

        $personasActions = (static function() {
            return require __DIR__ . '/../../config/personas-actions.php';
        })();

        $datosInicialesActions = (static function() {
            return require __DIR__ . '/../../config/datosiniciales-actions.php';
        })();

        $vehiculosActions = (static function() {
            return require __DIR__ . '/../../config/vehiculos-actions.php';
        })();

        $genericActions = (static function() {
            return require __DIR__ . '/../../config/generic-actions.php';
        })();

        $internalActions = [
            'PING' => function (Request $request) {
                return [
                    'pong' => true,
                    'action' => $request->action(),
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
            is_array($documentosActions) ? $documentosActions : [],
            is_array($ventasActions) ? $ventasActions : [],
            is_array($personasActions) ? $personasActions : [],
            is_array($datosInicialesActions) ? $datosInicialesActions : [],
            is_array($vehiculosActions) ? $vehiculosActions : [],
            is_array($genericActions) ? $genericActions : []
        );

        return self::$map;
    }
}
