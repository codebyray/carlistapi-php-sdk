# Car List API PHP SDK

Official PHP SDK for the **Car List API**.

The Car List API PHP SDK provides a simple, framework-independent interface for accessing the Car List API. It supports Automotive, Powersports, and VIN Decoder endpoints using authenticated requests.

The SDK works with any PHP application, including:

- Plain PHP
- Laravel
- Symfony
- Slim
- WordPress
- Custom Composer projects

---

## Requirements

- PHP 8.2 or higher
- Composer
- A valid Car List API API token

---

## Installation

Install the SDK using Composer:

```bash
composer require codebyray/carlistapi-php-sdk
```

---

## Configuration

Create a new client using your API token.

```php
use CodebyRay\CarListApi\CarListApi;

$carList = new CarListApi(
    token: 'your-api-token'
);
```

By default the SDK uses:

| Option | Default |
|---------|----------|
| Base URL | `https://carlistapi.com/api` |
| API Version | `v1` |
| Timeout | 15 seconds |
| Connect Timeout | 5 seconds |
| Retries | 2 |

To customize these values:

```php
use CodebyRay\CarListApi\Configuration;

$config = new Configuration(
    token: 'your-api-token',
    baseUrl: 'https://carlistapi.com/api',
    version: 'v1',
    timeout: 15,
    connectTimeout: 5,
    retryTimes: 2,
    retrySleepMs: 200,
);

$carList = new CarListApi($config);
```

---

# Quick Start

## Automotive

Retrieve all supported years.

```php
$years = $carList
    ->automotive()
    ->years()
    ->data;
```

Retrieve all makes.

```php
$makes = $carList
    ->automotive()
    ->makes(2026)
    ->data;
```

Retrieve models.

```php
$models = $carList
    ->automotive()
    ->models(2026, 'Toyota')
    ->data;
```

Retrieve complete vehicle information.

```php
$vehicle = $carList
    ->automotive()
    ->details($vehicleUuid)
    ->data;
```

---

## Powersports

Retrieve supported powersports types.

```php
$types = $carList
    ->powersports()
    ->types()
    ->data;
```

Retrieve available models.

```php
$models = $carList
    ->powersports()
    ->modelsByYearMakeAndType(
        'ATV / Utility',
        2026,
        'Can-Am'
    )
    ->data;
```

---

## VIN Decoder

Decode a VIN.

```php
$vehicle = $carList
    ->vinDecoder()
    ->decode(
        '1HGCM82633A004352'
    )
    ->data;
```

Decode using a known model year.

```php
$vehicle = $carList
    ->vinDecoder()
    ->decode(
        '1HGCM82633A004352',
        modelYear: 2003
    )
    ->data;
```

The SDK automatically normalizes VINs by removing spaces, hyphens, and converting letters to uppercase before sending requests.

---

# Responses

Every successful request returns an `ApiResponse` object.

```php
$response = $carList
    ->automotive()
    ->years();

$response->data;
$response->status;
$response->headers;
```

Retrieve a specific response header.

```php
$response->header('X-RateLimit-Remaining');
```

---

# Error Handling

The SDK throws strongly typed exceptions.

```php
try {

    $vehicle = $carList
        ->vinDecoder()
        ->decode($vin);

} catch (AuthenticationException $e) {

    //

} catch (RateLimitException $e) {

    //

} catch (ValidationException $e) {

    //

}
```

Available exceptions include:

| Exception | Description |
|------------|-------------|
| AuthenticationException | Invalid or missing API token |
| AuthorizationException | Insufficient permissions |
| ValidationException | Invalid request |
| NotFoundException | Resource not found |
| RateLimitException | Rate limit exceeded |
| ServerException | Server error |
| TransportException | Network or connection failure |

---

# API Versioning

The SDK supports configurable API versions.

```php
$config = new Configuration(
    token: 'your-api-token',
    version: 'v1',
);
```

When a future version of the Car List API becomes available, updating the SDK configuration is all that is required.

---

# User Agent

Requests automatically include a User-Agent identifying the SDK.

Example:

```text
codebyray/carlistapi-php-sdk/1.0.0
```

You may override the User-Agent if desired.

```php
$config = new Configuration(
    token: 'your-api-token',
    userAgent: 'my-application/2.0'
);
```

---

# Endpoint Coverage

The SDK currently supports:

### Automotive

- Years
- Makes
- Models
- Trims
- Engines
- Vehicle IDs
- Vehicle Details
- Logos
- Body Styles
- Fuel Types
- Drive Types
- Door Counts

### Powersports

- Types
- Years
- Makes
- Models
- Sub Models
- Vehicle IDs
- Vehicle Details
- Logos

### VIN Decoder

- VIN Decode
- VIN Decode with Model Year

---

# Documentation

Complete API documentation is available at:

**https://carlistapi.com/docs**

---

# Support

If you discover a bug or would like to request a feature, please open an issue on GitHub.

GitHub:

**https://github.com/codebyray/carlistapi-php-sdk**

---

# License

This project is licensed under the MIT License.
