<?php

declare(strict_types=1);

namespace Freeze\Component\Router\Contract;

use Freeze\Component\Router\Route;
use Psr\Http\Message\ServerRequestInterface;

interface ResolverInterface
{
    public function add(Route $route): void;

    public function resolve(ServerRequestInterface $request): ServerRequestInterface;
}
