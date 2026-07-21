<?php

namespace CodebyRay\CarListApi;

use CodebyRay\CarListApi\Exceptions\AuthenticationException;
use CodebyRay\CarListApi\Exceptions\AuthorizationException;
use CodebyRay\CarListApi\Exceptions\CarListApiException;
use CodebyRay\CarListApi\Exceptions\NotFoundException;
use CodebyRay\CarListApi\Exceptions\RateLimitException;
use CodebyRay\CarListApi\Exceptions\ServerException;
use CodebyRay\CarListApi\Exceptions\TransportException;
use CodebyRay\CarListApi\Exceptions\ValidationException;
use CodebyRay\CarListApi\Response\ApiResponse;
use GuzzleHttp\RequestOptions;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

final readonly class Client
{
    public function __construct(
        private ClientInterface $http,
        private Configuration $configuration,
    ) {}

    public function http(): ClientInterface
    {
        return $this->http;
    }

    public function configuration(): Configuration
    {
        return $this->configuration;
    }

    /** @param array<string, scalar|null> $query */
    public function get(string $path, array $query = []): ApiResponse
    {
        return $this->send('GET', $path, [RequestOptions::QUERY => $query]);
    }

    /** @param array<string, mixed> $data */
    public function post(string $path, array $data = []): ApiResponse
    {
        return $this->send('POST', $path, [RequestOptions::JSON => $data]);
    }

    /** @param array<string, mixed> $options */
    private function send(string $method, string $path, array $options): ApiResponse
    {
        $options = array_replace_recursive($this->defaultOptions(), $options);
        $attempt = 0;

        while (true) {
            try {
                $response = $this->http->request($method, $this->url($path), $options);
            } catch (ClientExceptionInterface $exception) {
                if ($attempt < $this->configuration->retryTimes) {
                    $attempt++;
                    $this->sleep();
                    continue;
                }

                throw new TransportException('Unable to connect to the Car List API.', 0, $exception);
            } catch (Throwable $exception) {
                throw new TransportException('The Car List API request failed before a response was received.', 0, $exception);
            }

            if ($this->shouldRetry($response) && $attempt < $this->configuration->retryTimes) {
                $attempt++;
                $this->sleep();
                continue;
            }

            return $this->handleResponse($response);
        }
    }

    private function handleResponse(ResponseInterface $response): ApiResponse
    {
        $status = $response->getStatusCode();
        $apiResponse = ApiResponse::fromPsrResponse($response);

        if ($status >= 200 && $status < 300) {
            return $apiResponse;
        }

        $payload = is_array($apiResponse->data) ? $apiResponse->data : [];
        $message = $this->message($payload, $status);

        throw match ($status) {
            401 => new AuthenticationException($message, 401),
            403 => new AuthorizationException($message, 403),
            404 => new NotFoundException($message, 404),
            422 => new ValidationException($message, 422),
            429 => new RateLimitException(
                $message,
                $this->intOrNull($payload['limit'] ?? null),
                $this->intOrNull($payload['used'] ?? null),
                $this->intOrNull($payload['remaining'] ?? null),
                is_string($payload['reset_at'] ?? null) ? $payload['reset_at'] : null,
            ),
            default => $status >= 500
                ? new ServerException($message, $status)
                : new CarListApiException($message, $status),
        };
    }

    /** @return array<string, mixed> */
    private function defaultOptions(): array
    {
        return [
            RequestOptions::HEADERS => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$this->configuration->token,
                'Content-Type' => 'application/json',
                'User-Agent' => $this->configuration->userAgent ?: Package::userAgent(),
            ],
            RequestOptions::HTTP_ERRORS => false,
            RequestOptions::TIMEOUT => $this->configuration->timeout,
            RequestOptions::CONNECT_TIMEOUT => $this->configuration->connectTimeout,
            RequestOptions::VERIFY => $this->configuration->verifySsl,
        ];
    }

    private function url(string $path): string
    {
        return rtrim($this->configuration->baseUrl, '/')
            .'/'.trim($this->configuration->version, '/')
            .'/'.ltrim($path, '/');
    }

    private function shouldRetry(ResponseInterface $response): bool
    {
        return $response->getStatusCode() === 429 || $response->getStatusCode() >= 500;
    }

    private function sleep(): void
    {
        if ($this->configuration->retrySleepMs > 0) {
            usleep($this->configuration->retrySleepMs * 1000);
        }
    }

    /** @param array<string, mixed> $payload */
    private function message(array $payload, int $status): string
    {
        foreach (['message', 'error'] as $key) {
            if (is_string($payload[$key] ?? null) && trim($payload[$key]) !== '') {
                return $payload[$key];
            }
        }

        return 'Car List API request failed with HTTP '.$status.'.';
    }

    private function intOrNull(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }
}
