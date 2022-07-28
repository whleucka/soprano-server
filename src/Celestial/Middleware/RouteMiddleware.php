<?php

namespace Celestial\Middleware;

use Constellation\Authentication\Auth;
use Constellation\Http\Request;
use Constellation\Routing\Router;

class RouteMiddleware implements MiddlewareInterface
{
    public function __construct(
        private Request $request,
        private Router $router
    ) {
    }

    public function process()
    {
        $route = $this->router->getRoute();
        if ($route) {
            $middlewares = $route->getMiddleware();
            foreach ($middlewares as $middleware) {
                $this->processMiddleware($middleware);
            }
        }
    }

    protected function processMiddleware(string $middleware)
    {
        match ($middleware) {
            "auth" => $this->routeAuth(),
            default => fn() => null,
        };
    }

    private function routeAuth()
    {
        if (!Auth::isSignedIn()) {
            $route = Router::findRoute("auth.sign-in");
            if ($route) {
                $uri = $route->getUri();
                header("Location: $uri");
                exit();
            }
        }
    }
}
