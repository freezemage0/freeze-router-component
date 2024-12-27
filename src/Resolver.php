<?php

declare(strict_types=1);

namespace Freeze\Component\Router;

use Freeze\Component\Router\Contract\ResolverInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

final class Resolver implements ResolverInterface
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

    /**
     * @param array<array-key, Route> $routes
     */
    private function resolveChunk(RequestInterface $request, array $routes): ?Route
    {
        $pattern = [];

        foreach ($routes as $route) {
            $pattern[] = "({$route->pattern})";
        }

        $pattern = '~^(?:' . \implode(" | \\n", $pattern) . ')$~x';

        if (!\preg_match($pattern, $request->getRequestTarget(), $matches)) {
            return null;
        }

        for ($i = 1, $length = count($matches); $i < $length; $i += 1) {
            $match = $matches[$i] ?? null;
            if (empty($match)) {
                continue;
            }

            $route = $routes[$i];
            foreach (\array_keys($route->arguments) as $index => $argument) {
                $route->arguments[$argument] = $matches[$i + $index];
            }

            return $route;
        }

        return null;
    }
}
