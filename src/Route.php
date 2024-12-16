<?php

declare(strict_types=1);

namespace Freeze\Component\Router;

use Psr\Http\Server\RequestHandlerInterface;

final readonly class Route
{
    public array $requestMethods;

    public static function fromCallable(string $pattern, callable $handler, string ...$requestMethods): Route
    {
        return new Route($pattern, new CallableRequestHandler($handler), ...$requestMethods);
    }

    public function __construct(
            public string $pattern,
            public RequestHandlerInterface $handler,
            string ...$requestMethods
    ) {
        $this->requestMethods = $requestMethods;
    }
}
