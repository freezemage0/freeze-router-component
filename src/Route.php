<?php

declare(strict_types=1);

namespace Freeze\Component\Router;

use Freeze\Component\Router\Contract\EndpointInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class Route
{
    public array $requestMethods;

    public static function fromCallable(string $pattern, callable $handler, string ...$requestMethods): Route
    {
        return new Route($pattern, new CallableEndpoint($handler), ...$requestMethods);
    }

    public function __construct(
            public string $pattern,
            public EndpointInterface $endpoint,
            string ...$requestMethods
    ) {
        $this->requestMethods = $requestMethods;
    }
}
