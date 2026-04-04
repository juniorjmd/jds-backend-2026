<?php
declare(strict_types=1);

namespace App\Core\Http;

final class Request
{
    private array $payload;

    public function __construct(array $payload = [])
    {
        $this->payload = $payload;
    }

    public static function fromGlobals(): self
    {
        $body = file_get_contents('php://input');
        $parsedBody = json_decode($body, true);

        if (!is_array($parsedBody)) {
            $parsedBody = [];
        }

        $payload = array_merge($_POST, $_GET, $parsedBody);

        return new self($payload);
    }

    public function action(): string
    {
        return (string) $this->input('action', '');
    }

    public function input(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $this->payload)) {
            return $this->payload[$key];
        }

        $legacyKey = '_' . $key;

        if (array_key_exists($legacyKey, $this->payload)) {
            return $this->payload[$legacyKey];
        }

        return $default;
    }

    public function all(): array
    {
        return $this->payload;
    }
}
