<?php
declare(strict_types=1);

namespace App\Core\Http;

final class Response
{
    public static function ok(mixed $payload = null): void
    {
        http_response_code(200);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'data' => $payload,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function fail(string $code, string $message, int $httpStatus = 400, array $meta = []): void
    {
        http_response_code($httpStatus);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error' => array_merge([
                'code' => $code,
                'message' => $message,
            ], $meta),
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
