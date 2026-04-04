<?php
declare(strict_types=1);

namespace App\Core\Http;

 
class Response
{
    private int $status = 200;
    private array $data = [];

    public static function ok(mixed $data, int $status = 200): void
    {
        self::sendJsonResponse([ 
            'ok' => true,
            'data' => $data,
            'error' => null,
        ], $status);
    }

    public static function fail(string $code, string $message, int $status = 400, ?array $meta = null): void
    {
 
        self::sendJsonResponse([ 
            'ok' => false,
            'data' => null,
            'error' => [
                'code' => $code,
                'message' => $message,
                'meta' => $meta,
            ],
        ], $status);
    }

    public static function noContent(): void
    {
        http_response_code(204);
        self::cors();
        exit;
    }

    private static function sendJsonResponse(array $payload, int $status): void
    {
        http_response_code($status);
        self::cors();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    private static function cors(): void
    {
        // Mínimo (luego lo hacemos configurable por config/cors.php)
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Session-Token');
    }

    // Instance methods for chaining
    public function status(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function json(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function send(): void
    {
        http_response_code($this->status);
        self::cors();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($this->data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
