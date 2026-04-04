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
        ];

        self::$map = array_merge(
            $internalActions,
            is_array($legacyActions) ? $legacyActions : [],
            is_array($documentosActions) ? $documentosActions : []
        );

        return self::$map;
    }
}
