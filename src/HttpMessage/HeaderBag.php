<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpMessage;

use InvalidArgumentException;

/**
 * PSR-7 Message about HTTP Header list
 * @internal
 */
final class HeaderBag
{
    // underscore to hyphen conversion, respects Symfony HeaderBag
    // @link https://github.com/symfony/symfony/blob/c26bb3af75ba8d9804bde07aa1adf4e4a050f461/src/Symfony/Component/HttpFoundation/HeaderBag.php#L23
    private const UPPER_CASE = '_ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const LOWER_CASE = '-abcdefghijklmnopqrstuvwxyz';
    private const OPTIONAL_WHITESPACE = " \t";

    /** @var array<string, string[]> $headers */
    private array $headers = [];

    /**
     * Constructor
     * @param array<string, mixed> $headers
     */
    public function __construct(array $headers = [])
    {
        foreach ($headers as $name => $value) {
            $this->add($name, $value);
        }
    }

    /**
     * Get all headers as array
     * @return array<string, string[]>
     */
    public function all(): array
    {
        return $this->headers;
    }

    /**
     * Get one header as string array
     * @param string $name
     * @return string[]
     * @throws InvalidArgumentException
     */
    public function get(string $name): array
    {
        $name = $this->formatName($name);
        return \array_key_exists($name, $this->headers) ? $this->headers[$name] : [];
    }

    /**
     * Add one header
     * @param string $name
     * @param mixed $value
     * @return void
     * @throws InvalidArgumentException
     */
    public function add(string $name, mixed $value): void
    {
        $name = $this->formatName($name);
        $value = $this->formatValue($value);
        if (!\array_key_exists($name, $this->headers)) {
            $this->headers[$name] = [];
        }
        $this->headers[$name] = \array_unique([...$this->headers[$name], ...$value]);
    }

    /**
     * Remove and add one header
     * @param string $name
     * @param mixed $value
     * @return void
     * @throws InvalidArgumentException
     */
    public function replace(string $name, mixed $value): void
    {
        $this->remove($name);
        $this->add($name, $value);
    }

    /**
     * Remove one header
     * @param string $name
     * @return void
     * @throws InvalidArgumentException
     */
    public function remove(string $name): void
    {
        $name = $this->formatName($name);
        unset($this->headers[$name]);
    }

    /**
     * Get whether the name of header exists
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        $name = $this->formatName($name);
        return \array_key_exists($name, $this->headers);
    }

    /**
     * Validate and get formatted header name
     * @link https://datatracker.ietf.org/doc/html/rfc7230#section-3.2
     * @param string $name
     * @return string
     * @throws InvalidArgumentException
     */
    private function formatName(string $name): string
    {
        if (!\preg_match('/^[a-zA-Z0-9\'`#$%&*+.^_|~!-]+$/D', $name)) {
            throw new InvalidArgumentException(\sprintf('Error: not RFC7230 compatible name : "%s"', $name));
        }
        return \strtr($name, self::UPPER_CASE, self::LOWER_CASE);
    }

    /**
     * Validate and get formatted header value list
     * @link https://datatracker.ietf.org/doc/html/rfc7230#section-3.2
     * @param mixed $value
     * @return string[]
     */
    private function formatValue(mixed $value): array
    {
        if (!\is_array($value)) {
            $value = [$value];
        }

        $formatted_values = [];
        foreach ($value as $v) {
            if (!\is_scalar($v)) {
                throw new InvalidArgumentException('Header value must be a string');
            }
            $trimmed_value = \trim((string) $v, self::OPTIONAL_WHITESPACE);

            if ($trimmed_value === '') {
                throw new InvalidArgumentException('Header value must be a present string');
            }

            if (!\preg_match('/^[\x20\x09\x21-\x7E\x80-\xFF]*$/D', $trimmed_value)) {
                throw new InvalidArgumentException('Invalid header value provided');
            }
            $formatted_values[] = $trimmed_value;
        }

        if (\count($formatted_values) === 0) {
            throw new InvalidArgumentException('Header value must be a present string');
        }

        return $formatted_values;
    }
}
