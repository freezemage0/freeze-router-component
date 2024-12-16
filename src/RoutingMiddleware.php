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
            private readonly Resolver $router
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $request = $this->router->resolve($request);

        /** @var ?Route $route */
        $route = $request->getAttribute(Resolver::ROUTE_ATTRIBUTE);

        return match ($request->getAttribute(Resolver::RESOLVE_RESULT_ATTRIBUTE)) {
            Resolver::RESOLVE_RESULT_NOT_FOUND => $response->withStatus(404, 'Not Found'),
            Resolver::RESOLVE_RESULT_NOT_ALLOWED => $response->withStatus(405, 'Method Not Allowed'),
            default => $route?->endpoint->process($request, $response, $route->getArguments()) ?? $response->withStatus(404, 'Not Found'),
        };
    }
}
