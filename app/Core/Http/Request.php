<?php
declare(strict_types=1);

namespace App\Core\Http;

final class Request
{
    public function __construct(
        private string $method,
        private array $headers,
        private array $body,
        private array $query,
        private array $server
    ) {}

    public static function fromGlobals(): self
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $headers = self::getAllHeadersSafe();

        $raw = file_get_contents('php://input') ?: '';
        $body = [];
        if ($raw !== '') {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $body = $decoded;
            }
        }

        return new self($method, $headers, $body, $_GET ?? [], $_SERVER ?? []);
    }

    public function method(): string { return strtoupper($this->method); }
    public function headers(): array { return $this->headers; }
    public function body(): array { return $this->body; }
    public function query(): array { return $this->query; }

    public function action(): ?string
    {
          return $this->body['action']
            ?? $this->query['action']
            ?? null;
    }

    public function header(string $name): ?string
    {
        $key = strtolower($name);
        return $this->headers[$key] ?? null;
    }


    public function all(): array
        {
            return $this->body;
        }

    public function input(string $key, mixed $default = null): mixed
        {
            return $this->body[$key]
                ?? $this->query[$key]
                ?? $default;
        }

    public function bearerToken(): ?string
    {
        $authorization = $this->header('authorization');

        if ($authorization === null) {
            return null;
        }

        if (preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches) !== 1) {
            return null;
        }

        return trim($matches[1]) !== '' ? trim($matches[1]) : null;
    }
    private static function getAllHeadersSafe(): array
    {
        $headers = [];
        foreach ($_SERVER as $k => $v) {
            if (str_starts_with($k, 'HTTP_')) {
                $name = strtolower(str_replace('_', '-', substr($k, 5)));
                $headers[$name] = (string)$v;
            }
        }
        // Content-Type y Authorization a veces no vienen como HTTP_*
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $headers['content-type'] = (string)$_SERVER['CONTENT_TYPE'];
        }
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers['authorization'] = (string)$_SERVER['HTTP_AUTHORIZATION'];
        }
        return $headers;
    }
}
