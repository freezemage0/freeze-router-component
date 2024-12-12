<?php

declare(strict_types=1);

namespace Freeze\Component\Router\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class Route
{
    public readonly array $requestMethods;

    public function __construct(
            public readonly string $pattern,
            string ...$requestMethods
    ) {
        $this->requestMethods = $requestMethods;
    }
}
