<?php
declare(strict_types=1);

namespace App\Modules\Auth;

use App\Core\Http\Request;

final class AuthContext
{
    public function resolve(Request $request): array
    {
        $userId = (int) $request->input('usuario_id', 0);

        return [
            'success' => $userId > 0,
            'user_id' => $userId,
        ];
    }
}
