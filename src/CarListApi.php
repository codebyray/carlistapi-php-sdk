<?php

namespace CodebyRay\CarListApi;

use CodebyRay\CarListApi\Resources\Automotive;
use CodebyRay\CarListApi\Resources\Powersports;
use CodebyRay\CarListApi\Resources\VinDecoder;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Client\ClientInterface;

final readonly class CarListApi
{
    private Client $client;

    public function __construct(
        string|Configuration $token,
        ?ClientInterface $httpClient = null,
    ) {
        $configuration = is_string($token)
            ? new Configuration(token: $token)
            : $token;

        $this->client = new Client(
            http: $httpClient ?? new GuzzleClient(),
            configuration: $configuration,
        );
    }

    /** @param array<string, mixed> $configuration */
    public static function fromArray(array $configuration, ?ClientInterface $httpClient = null): self
    {
        return new self(Configuration::fromArray($configuration), $httpClient);
    }

    public function withToken(string $token): self
    {
        return new self($this->client->configuration()->withToken($token), $this->client->http());
    }

    public function automotive(): Automotive
    {
        return new Automotive($this->client);
    }

    public function powersports(): Powersports
    {
        return new Powersports($this->client);
    }

    public function vinDecoder(): VinDecoder
    {
        return new VinDecoder($this->client);
    }

    public function client(): Client
    {
        return $this->client;
    }
}
