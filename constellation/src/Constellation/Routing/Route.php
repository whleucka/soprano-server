<?php

namespace Constellation\Routing;

use Attribute;

/**
 * @class Route
 */
#[Attribute]
class Route
{
    private $params = [];

    public function __construct(
        private string $method,
        private string $uri,
        private ?string $name = null,
        private string|array $middleware = [],
        private ?string $class_name = null,
        private ?string $endpoint = null
    ) {
    }

    public function getUri()
    {
        return $this->uri;
    }
    public function getName()
    {
        return $this->name;
    }
    public function getMiddleware()
    {
        return $this->middleware;
    }
    public function getMethod()
    {
        return $this->method;
    }
    public function getClassName()
    {
        return $this->class_name;
    }
    public function getEndpoint()
    {
        return $this->endpoint;
    }
    public function setParams(array $params)
    {
        $this->params = $params;
    }
    public function getParams()
    {
        return $this->params;
    }
}
