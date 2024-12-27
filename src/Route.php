<?php

declare(strict_types=1);

namespace Freeze\Component\Router;

use Freeze\Component\Router\Contract\EndpointInterface;

final class Route
{
    public readonly string $pattern;
    public array $arguments = [];
    public readonly array $requestMethods;

    public static function fromCallable(
            string $pattern,
            callable $endpoint,
            string $requestMethod,
            string ...$requestMethods
    ): Route {
        return new Route($pattern, new CallableEndpoint($endpoint), $requestMethod, ...$requestMethods);
    }

    public function __construct(
            string $pattern,
            public readonly EndpointInterface $endpoint,
            string $requestMethod,
            string ...$requestMethods
    ) {
        $this->pattern = $this->parsePattern($pattern);

        $requestMethods[] = $requestMethod;
        $this->requestMethods = $requestMethods;
    }

    private function parsePattern(string $pattern): string
    {
        return \preg_replace_callback(
                '~\{([^}]+)}~',
                function (array $matches): string {
                    $argument = \explode(':', $matches[1]);
                    $this->arguments[$argument[0]] = null;

                    return match ($argument[1] ?? null) {
                        'number' => '(\\d+)',
                        'string' => '([A-Za-z]+)',
                        default => '(.*)'
                    };
                },
                $pattern
        );
    }
}
