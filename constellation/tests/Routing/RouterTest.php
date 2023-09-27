<?php

declare(strict_types=1);

namespace Constellation\Tests\Routing;

use Constellation\Container\Container;
use Constellation\Http\Request;
use Constellation\Routing\Router;
use PHPUnit\Framework\TestCase;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Constellation\Database\DB;

/**
 * @class RouterTest
 */
class RouterTest extends TestCase
{
    private $config;
    private $db_config;
    private $router;

    public function setUp(): void
    {
        $this->config = [
            "controller_path" => __DIR__ . "/Controllers",
        ];
        $this->db_config = [
            "type" => "sqlite",
            "path" => __DIR__ . "/../db/test.db",
        ];
        $container = Container::getInstance();
        $container->setDefinitions([
            Router::class => \DI\autowire()->constructorParameter(
                "config",
                $this->config
            ),
            DB::class => \DI\autowire()->constructorParameter(
                "config",
                $this->db_config
            ),
            Environment::class => function () {
                $loader = new FilesystemLoader(__DIR__ . "/views");
                return new Environment($loader, [
                    "cache" => __DIR__ . "/cache",
                    "auto_reload" => true,
                ]);
            },
        ]);
        $container->build();
        $this->router = Router::getInstance();
    }

    public function testRouterInstance()
    {
        $this->assertInstanceOf(Router::class, $this->router);
    }

    public function testRouterCollision()
    {
        $router = new Router($this->config, new Request("/basic/surprise"));
        $router->registerRoutes()->matchRoute();
        $this->assertSame("/basic/{collision}", $router->getRoute()->getUri());
        $this->assertSame("GET", $router->getRoute()->getMethod());
        $this->assertSame(["surprise"], $router->getRoute()->getParams());
        $router = new Router($this->config, new Request("/basic/surprise/cheese"));
        $router->registerRoutes()->matchRoute();
        $this->assertSame("/basic/{collision}/{here}", $router->getRoute()->getUri());
        $this->assertSame("GET", $router->getRoute()->getMethod());
        $this->assertSame(["surprise", "cheese"], $router->getRoute()->getParams());
        $router = new Router($this->config, new Request("/basic/surprise/cheese/bacon"));
        $router->registerRoutes()->matchRoute();
        $this->assertSame("/basic/{collision}/{here}/{there}", $router->getRoute()->getUri());
        $this->assertSame("GET", $router->getRoute()->getMethod());
        $this->assertSame(["surprise", "cheese", "bacon"], $router->getRoute()->getParams());
    }

    public function testRouterMatchRoute()
    {
        $router = new Router($this->config, new Request("/other"));
        $router->registerRoutes()->matchRoute();
        $this->assertSame($router->getRoute()->getUri(), "/other");
        $this->assertSame($router->getRoute()->getMethod(), "GET");
    }

    public function testRouterGetRouteByName()
    {
        $route = Router::findRoute("test.name-age");
        $this->assertSame("/test/{name}/{?age}", $route->getUri());
        $this->assertSame("test.name-age", $route->getName());
        $this->assertSame(["test"], $route->getMiddleware());
    }

    public function testRouterRouteParams()
    {
        $router = new Router(
            $this->config,
            new Request("/test/william/35")
        );
        $router->registerRoutes()->matchRoute();
        $this->assertSame(
            $router->getRoute()->getUri(),
            "/test/{name}/{?age}"
        );
        $this->assertSame($router->getRoute()->getParams(), [
            "william",
            "35",
        ]);
    }

    public function testRouterRouteOptionalParam()
    {
        $router = new Router(
            $this->config,
            new Request("/test/william")
        );
        $router->registerRoutes()->matchRoute();
        $this->assertSame(
            $router->getRoute()->getUri(),
            "/test/{name}/{?age}"
        );
        $this->assertSame($router->getRoute()->getParams(), ["william"]);
    }

    public function testRouterBuildRoute()
    {
        $uri = Router::buildRoute("test.name-age", "william", 21);
        $this->assertSame("/test/william/21", $uri);
    }

    public function testRouteEndpoint()
    {
        $router = new Router(
            $this->config,
            new Request("/test/william/18")
        );
        $router->registerRoutes()->matchRoute();
        $route = $router->getRoute();
        $class_name = $route->getClassName();
        $endpoint = $route->getEndpoint();
        $params = $route->getParams();
        $container = Container::getInstance();
        $class = $container->get($class_name);
        $response = $class->$endpoint(...$params);
        $this->assertSame("Hello, william. You're 18.", $response);
    }
}
