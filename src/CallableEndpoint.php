<?php

declare(strict_types=1);

namespace Freeze\Component\Router;

use Freeze\Component\Router\Contract\EndpointInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class CallableEndpoint implements EndpointInterface
{
    private \Closure $handler;

    public function __construct(callable $handler)
    {
        $this->handler = $handler(...);
    }

    public function process(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return \call_user_func($this->handler, $request, $response);
    }
}
