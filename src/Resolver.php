<?php

declare(strict_types=1);

namespace Freeze\Component\Router;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

final class Resolver
{
    private const DEFAULT_CHUNK_SIZE = 10;

    public const ROUTE_ATTRIBUTE            = 'Freeze.Router.Route';
    public const RESOLVE_RESULT_ATTRIBUTE   = 'Freeze.Router.ResolveResult';
    public const RESOLVE_RESULT_NOT_ALLOWED = 'NOT_ALLOWED';
    public const RESOLVE_RESULT_NOT_FOUND   = 'NOT_FOUND';
    public const RESOLVE_RESULT_FOUND       = 'FOUND';

    private RouteCollection $routes;

    public function __construct(
            private readonly int $chunkSize = Resolver::DEFAULT_CHUNK_SIZE
    ) {
        $this->routes = new RouteCollection();
    }

    public function add(Route $route): void
    {
        $this->routes->insert($route);
    }

    public function resolve(ServerRequestInterface $request): ServerRequestInterface
    {
        $this->routes->rewind();

        while ($this->routes->valid()) {
            $chunk = [];
            for ($i = 0; $i < $this->chunkSize && $this->routes->valid(); $i += 1) {
                $chunk[] = $this->routes->current();
                $this->routes->next();
            }

            $route = $this->resolveChunk($request, $chunk);
            if ($route === null) {
                continue;
            }

            $request = $request->withAttribute(Resolver::ROUTE_ATTRIBUTE, $route);

            return $request->withAttribute(
                    Resolver::RESOLVE_RESULT_ATTRIBUTE,
                    \in_array($request->getMethod(), $route->requestMethods, true)
                            ?
                            Resolver::RESOLVE_RESULT_FOUND
                            :
                            Resolver::RESOLVE_RESULT_NOT_ALLOWED
            );
        }

        return $request->withAttribute(Resolver::ROUTE_ATTRIBUTE, null)->withAttribute(
                Resolver::RESOLVE_RESULT_ATTRIBUTE,
                Resolver::RESOLVE_RESULT_NOT_FOUND
        );
    }

    private function resolveChunk(RequestInterface $request, array $routes): ?Route
    {
        $map = [];
        $pattern = [];

        foreach ($routes as $index => $route) {
            $map["route_{$index}"] = $route;

            $pattern[] = "(?<route_{$index}>{$route->pattern})";
        }

        $pattern = '~' . \implode('|', $pattern) . '~';

        if (!\preg_match($pattern, $request->getRequestTarget(), $matches)) {
            return null;
        }

        foreach ($map as $id => $route) {
            if (!empty($matches[$id])) {
                return $route;
            }
        }

        return null;
    }
}
