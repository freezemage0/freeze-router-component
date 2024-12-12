<?php

declare(strict_types=1);

namespace Freeze\Component\Router;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class RoutingMiddleware implements MiddlewareInterface
{
    public function __construct(
            private readonly Router $router
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $request = $this->router->resolve($request);

        /** @var Route|null $route */
        $route = $request->getAttribute(Router::ROUTE_ATTRIBUTE);

        return match ($request->getAttribute(Router::RESOLVE_RESULT_ATTRIBUTE)) {
            Router::RESOLVE_RESULT_NOT_FOUND => $response->withStatus(404, 'Not Found'),
            Router::RESOLVE_RESULT_NOT_ALLOWED => $response->withStatus(405, 'Method Not Allowed'),
            default => $route?->handler->handle($request) ?? $response->withStatus(404, 'Not Found'),
        };
    }
}
