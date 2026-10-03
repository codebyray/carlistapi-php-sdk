<?php

namespace CodebyRay\CarListApi\Tests;

use CodebyRay\CarListApi\CarListApi;
use CodebyRay\CarListApi\Configuration;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ResourceRoutesTest extends TestCase
{
    public static function routeProvider(): array
    {
        return require __DIR__ . '/Fixtures/routes.php';
    }

    #[DataProvider('routeProvider')]
    public function test_resource_method_uses_documented_route(
        string $resource,
        string $method,
        array $arguments,
        string $path,
        string $httpMethod,
    ): void {
        $history = [];
        $stack = HandlerStack::create(new MockHandler([new Response(200, [], '{}')]));
        $stack->push(Middleware::history($history));

        $sdk = new CarListApi(
            new Configuration(token: 'test-token', baseUrl: 'https://example.test/api', retryTimes: 0),
            new Client(['handler' => $stack]),
        );

        $sdk->{$resource}()->{$method}(...$arguments);

        self::assertCount(1, $history);
        self::assertSame($httpMethod, $history[0]['request']->getMethod());
        self::assertSame('/api/v1/' . $path, $history[0]['request']->getUri()->getPath());
    }
}
