<?php

declare(strict_types=1);

namespace Freeze\Component\Router;

use Psr\Http\Message\ServerRequestInterface;

final class Resolver
{
    public const ROUTE_ATTRIBUTE            = 'Freeze.Router.Route';
    public const RESOLVE_RESULT_ATTRIBUTE   = 'Freeze.Router.ResolveResult';
    public const RESOLVE_RESULT_NOT_ALLOWED = 'NOT_ALLOWED';
    public const RESOLVE_RESULT_NOT_FOUND   = 'NOT_FOUND';
    public const RESOLVE_RESULT_FOUND       = 'FOUND';

    private RouteCollection $routes;

    public function __construct()
    {
        $this->routes = new RouteCollection();
    }

    public function add(Route $route): void
    {
        $this->routes->insert($route);
    }

    public function resolve(ServerRequestInterface $request): ServerRequestInterface
    {
        $map = [];
        $pattern = [];

        $index = 0;
        foreach ($this->routes as $route) {
            $map["route_{$index}"] = $route;

            $pattern[] = "(?<route_{$index}>{$route->pattern})";
            $index += 1;
        }

        $pattern = '~' . \implode('|', $pattern) . '~';

        if (!\preg_match($pattern, $request->getRequestTarget(), $matches)) {
            return $request->withAttribute(Resolver::RESOLVE_RESULT_ATTRIBUTE, Resolver::RESOLVE_RESULT_NOT_FOUND);
        }

        foreach ($map as $id => $route) {
            if (!empty($matches[$id])) {
                return $request
                        ->withAttribute(Resolver::ROUTE_ATTRIBUTE, $route)
                        ->withAttribute(Resolver::RESOLVE_RESULT_ATTRIBUTE, Resolver::RESOLVE_RESULT_FOUND);
            }
        }

        return $request->withAttribute(Resolver::RESOLVE_RESULT_ATTRIBUTE, Resolver::RESOLVE_RESULT_NOT_FOUND);
    }
}
