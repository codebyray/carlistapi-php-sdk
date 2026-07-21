<?php

namespace CodebyRay\CarListApi\Response;

use Psr\Http\Message\ResponseInterface;

final readonly class ApiResponse
{
    /** @param array<string, list<string>> $headers */
    public function __construct(
        public mixed $data,
        public int $status,
        public array $headers,
        public string $body,
    ) {}

    public static function fromPsrResponse(ResponseInterface $response): self
    {
        $body = (string) $response->getBody();
        $decoded = json_decode($body, true);

        return new self(
            data: json_last_error() === JSON_ERROR_NONE ? $decoded : $body,
            status: $response->getStatusCode(),
            headers: $response->getHeaders(),
            body: $body,
        );
    }

    public function header(string $name): ?string
    {
        foreach ($this->headers as $key => $values) {
            if (strcasecmp($key, $name) === 0) {
                return $values[0] ?? null;
            }
        }

        return null;
    }
}
