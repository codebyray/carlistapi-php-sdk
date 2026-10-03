# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/)
and this project adheres to [Semantic Versioning](https://semver.org/).

---

## [Unreleased]

### Changed

- Retry transient connection and HTTP 5xx failures only for GET requests. VIN decode POST requests are sent once to avoid a second quota-counted decode when a response is lost.

### Tests

- Cover every public resource method against the documented API v1 route and HTTP method.
- Verify VIN decode is not retried after connection failures or HTTP 5xx responses.

## [0.1.1] - 2026-09-25

### Fixed

- Support PSR-18 clients that do not provide Guzzle's `request()` method.
- Stop retrying HTTP 429 quota responses.
- Correct the year-filtered makes example in the README.

## [0.1.0] - 2026-07-20

### Added

- Initial public release
- Automotive API client
- Powersports API client
- VIN Decoder client
- Bearer token authentication
- Configurable API version support
- Automatic retry handling
- Request timeout configuration
- Response header access
- Rate limit information
- Strongly typed exceptions
- Framework-independent PHP SDK
- PHPUnit test suite
- Complete documentation
