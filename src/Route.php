<?php

declare(strict_types=1);

namespace Freeze\Component\Router;

use Freeze\Component\Router\Contract\EndpointInterface;

final readonly class Route
{
    private array $arguments;
    public array  $requestMethods;

    public static function fromCallable(
            string $pattern,
            callable $endpoint,
            string $requestMethod,
            string ...$requestMethods
    ): Route {
        return new Route($pattern, new CallableEndpoint($endpoint), $requestMethod, ...$requestMethods);
    }

    public function __construct(
            public string $pattern,
            public EndpointInterface $endpoint,
            string $requestMethod,
            string ...$requestMethods
    ) {
        $requestMethods[] = $requestMethod;

        $this->requestMethods = $requestMethods;
    }

    public function withArguments(array $arguments): Route
    {
        $route = new Route($this->pattern, $this->endpoint, ...$this->requestMethods);
        $route->arguments = $arguments;

        return $route;
    }

    public function getArguments(): array
    {
        return $this->arguments ??= [];
    }
}
