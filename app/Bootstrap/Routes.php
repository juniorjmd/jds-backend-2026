<?php
declare(strict_types=1);

namespace App\Bootstrap;

use App\Core\Http\Request;

final class Routes
{
    /** @return array<string, callable> */
    public static function map(): array
    {
        $legacyActions = require __DIR__ . '/../../config/actions.php';

        return array_merge([
            'PING' => function (Request $req) {
                return [
                    'pong' => true,
                    'action' => $req->action(),
                    'time' => date('c'),
                ];
            },
        ], $legacyActions ?? []);
    }
}