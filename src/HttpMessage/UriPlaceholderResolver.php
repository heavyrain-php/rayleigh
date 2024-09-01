<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpMessage;

use Psr\Http\Message\ServerRequestInterface;

/* readonly */ class UriPlaceholderResolver
{
    public function resolve(ServerRequestInterface $request, string $path): ServerRequestInterface|bool
    {
        $count = 0;
        // Replace placeholder to named capture group
        $regexp_pattern = \preg_replace('#\{(\w+)\}#', '(?P<$1>[^\/]+)', $path, count: $count);
        if ($count === 0) {
            return false; // no placeholder
        }

        $matches = [];
        $actual_path = $request->getUri()->getPath();

        if (\preg_match('#^' . $regexp_pattern . '$#', $actual_path, $matches)) {
            /** @var array<string, string> */
            $result = \array_filter($matches, fn(string|int $key): bool => !\is_int($key), \ARRAY_FILTER_USE_KEY);

            foreach ($result as $key => $value) {
                $request = $request->withAttribute($key, $value);
            }
            return $request;
        }

        return false; // @codeCoverageIgnore
    }
}
