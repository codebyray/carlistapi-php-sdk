<?php

namespace CodebyRay\CarListApi;

use InvalidArgumentException;

final readonly class Configuration
{
    public function __construct(
        public string $token,
        public string $baseUrl = 'https://carlistapi.com/api',
        public string $version = 'v1',
        public int $timeout = 15,
        public int $connectTimeout = 5,
        public int $retryTimes = 2,
        public int $retrySleepMs = 200,
        public ?string $userAgent = null,
        public bool $verifySsl = true,
    ) {
        if (trim($this->token) === '') {
            throw new InvalidArgumentException('A Car List API bearer token is required.');
        }

        if (trim($this->baseUrl) === '') {
            throw new InvalidArgumentException('A Car List API base URL is required.');
        }

        if (trim($this->version, '/') === '') {
            throw new InvalidArgumentException('A Car List API version is required, for example v1.');
        }

        if ($this->timeout < 1 || $this->connectTimeout < 1) {
            throw new InvalidArgumentException('Timeout values must be at least one second.');
        }

        if ($this->retryTimes < 0 || $this->retrySleepMs < 0) {
            throw new InvalidArgumentException('Retry values cannot be negative.');
        }
    }

    /** @param array<string, mixed> $values */
    public static function fromArray(array $values): self
    {
        return new self(
            token: (string) ($values['token'] ?? ''),
            baseUrl: (string) ($values['base_url'] ?? 'https://carlistapi.com/api'),
            version: (string) ($values['version'] ?? 'v1'),
            timeout: (int) ($values['timeout'] ?? 15),
            connectTimeout: (int) ($values['connect_timeout'] ?? 5),
            retryTimes: (int) ($values['retry_times'] ?? 2),
            retrySleepMs: (int) ($values['retry_sleep_ms'] ?? 200),
            userAgent: isset($values['user_agent']) ? (string) $values['user_agent'] : null,
            verifySsl: (bool) ($values['verify_ssl'] ?? true),
        );
    }

    public function withToken(string $token): self
    {
        return new self(
            token: $token,
            baseUrl: $this->baseUrl,
            version: $this->version,
            timeout: $this->timeout,
            connectTimeout: $this->connectTimeout,
            retryTimes: $this->retryTimes,
            retrySleepMs: $this->retrySleepMs,
            userAgent: $this->userAgent,
            verifySsl: $this->verifySsl,
        );
    }
}
