<?php

declare(strict_types=1);

namespace Freeze\Component\Router;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class CallableRequestHandler implements RequestHandlerInterface
{
    private \Closure $handler;

    public function __construct(callable $handler)
    {
        $this->handler = $handler(...);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return \call_user_func($this->handler, $request);
    }
}
