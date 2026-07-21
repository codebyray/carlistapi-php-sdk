<?php

namespace CodebyRay\CarListApi\Tests;

use CodebyRay\CarListApi\CarListApi;
use CodebyRay\CarListApi\Configuration;
use CodebyRay\CarListApi\Exceptions\AuthenticationException;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class ClientTest extends TestCase
{
    public function test_it_builds_versioned_urls_and_sends_bearer_token(): void
    {
        $history = [];
        $mock = new MockHandler([
            new Response(200, ['X-RateLimit-Remaining' => '99'], json_encode(['data' => [2025, 2026]], JSON_THROW_ON_ERROR)),
        ]);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($history));

        $sdk = new CarListApi(
            new Configuration(
                token: 'secret-token',
                baseUrl: 'https://example.test/api',
                version: 'v2',
                retryTimes: 0,
            ),
            new Client(['handler' => $stack]),
        );

        $response = $sdk->automotive()->years();

        self::assertSame(['data' => [2025, 2026]], $response->data);
        self::assertSame('99', $response->header('X-RateLimit-Remaining'));
        self::assertSame('https://example.test/api/v2/car-data/get-years/asc', (string) $history[0]['request']->getUri());
        self::assertSame('Bearer secret-token', $history[0]['request']->getHeaderLine('Authorization'));
    }

    public function test_it_throws_authentication_exception_for_401(): void
    {
        $mock = new MockHandler([
            new Response(401, [], json_encode(['message' => 'Invalid token'], JSON_THROW_ON_ERROR)),
        ]);

        $sdk = new CarListApi(
            new Configuration(token: 'bad-token', retryTimes: 0),
            new Client(['handler' => HandlerStack::create($mock)]),
        );

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid token');

        $sdk->automotive()->years();
    }

    public function test_it_accepts_a_named_token_argument(): void
    {
        $sdk = new CarListApi(token: 'named-arg-token');

        self::assertSame('named-arg-token', $sdk->client()->configuration()->token);
    }

    public function test_with_token_returns_a_new_sdk_instance(): void
    {
        $sdk = new CarListApi('first-token');
        $other = $sdk->withToken('second-token');

        self::assertNotSame($sdk, $other);
        self::assertSame('first-token', $sdk->client()->configuration()->token);
        self::assertSame('second-token', $other->client()->configuration()->token);
    }
}
