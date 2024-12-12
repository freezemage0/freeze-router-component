<?php

declare(strict_types=1);

namespace Freeze\Component\Router;

use Psr\Http\Server\RequestHandlerInterface;

final class Route
{
    public readonly array $requestMethods;

    public function __construct(
            public readonly string $pattern,
            public readonly RequestHandlerInterface $handler,
            string ...$requestMethods
    )
    {
        $this->requestMethods = $requestMethods;
    }
}
