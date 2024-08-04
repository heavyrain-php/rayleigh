# Rayleigh PHP Simple, Short, Smart Framework

## Features

## KEEP IT SIMPLE, NOT EASY

- All things are explicit, understandable.
- No magic method.
- No dynamic types.
- All have static types(with [PHPStan](https://phpstan.org/)).

## SHORTER IS BETTER THAN ANYTHING

- short is simple.

## SMART to adopt PHP community

- [PHP Standards Recommendations](https://www.php-fig.org/psr/) compatible implementation components
    - [PSR-1: Basic Coding Standard](https://www.php-fig.org/psr/psr-1)
    - [PSR-3: Logger Interface](https://www.php-fig.org/psr/psr-3)
    - [PSR-4: Autoloading Standard](https://www.php-fig.org/psr/psr-4)
    - [PSR-6: Caching Interface](https://www.php-fig.org/psr/psr-6): WIP
    - [PSR-7: HTTP Message Interface](https://www.php-fig.org/psr/psr-7): WIP
    - [PSR-11: Container Interface](https://www.php-fig.org/psr/psr-11): WIP
    - [PSR-12: Extended Coding Style Guide](https://www.php-fig.org/psr/psr-12): WIP
    - [PSR-14: Event Dispatcher](https://www.php-fig.org/psr/psr-14): WIP
    - [PSR-15: HTTP Handlers](https://www.php-fig.org/psr/psr-15): WIP
    - [PSR-16: Simple Cache](https://www.php-fig.org/psr/psr-16): WIP
    - [PSR-17: HTTP Factories](https://www.php-fig.org/psr/psr-17): WIP
    - [PSR-18: HTTP Client](https://www.php-fig.org/psr/psr-18): WIP
    - [PSR-20: Clock](https://www.php-fig.org/psr/psr-20)
    - [PER Coding Style 2.0](https://www.php-fig.org/per/coding-style/): WIP
- Smart DI Container, it can bind and resolve anything
- Smart Database Abstraction with PDO: TODO
- Supports [RoadRunner](https://roadrunner.dev/) to adopt wider protocols.
- IDL Support
    - [OpenAPI 3.0](https://www.openapis.org/): TODO
    - [gRPC](https://grpc.io/): TODO
    - anything else?

## Requirements

- PHP ~8.1.0|~8.2.0|~8.3.0
- No third-party requirements!(There is an adapter for these)

## Development

```sh
$ git clone https://github.com/heavyrain-php/rayleigh.git
$ cd rayleigh
$ composer install
$ composer lint
$ composer test
```

CI is at GitHub Actions.

## License

See [LICENSE](./LICENSE).

## Code of conduct

See [CODE_OF_CONDUCT.md](./CODE_OF_CONDUCT.md).

## Contributing

See [CONTRIBUTING.md](./CONTRIBUTING.md).

## Security

See [SECURITY.md](./SECURITY.md).
