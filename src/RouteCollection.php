<?php

declare(strict_types=1);

namespace Freeze\Component\Router;

use SplHeap;

/**
 * @template-implements SplHeap<Route>
 */
final class RouteCollection extends SplHeap
{
    /**
     * @param Route $value1
     * @param Route $value2
     * @return int
     */
    protected function compare($value1, $value2): int
    {
        return \strlen($value1->pattern) <=> \strlen($value2->pattern);
    }
}
