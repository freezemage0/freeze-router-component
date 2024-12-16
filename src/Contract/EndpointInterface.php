<?php

declare(strict_types=1);

namespace Freeze\Component\Router\Contract;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface EndpointInterface
{
    public function process(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface;
}
