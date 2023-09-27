<?php

namespace Constellation\Routing;

use Attribute;

/**
 * @class Options
 */
#[Attribute]
class Options extends Route
{
    public function __construct(
        private string $method,
        private string $uri,
        private ?string $name = null,
        private string|array $middleware = []
    ) {
        parent::__construct("OPTIONS", $uri, $name, $middleware);
    }
}
