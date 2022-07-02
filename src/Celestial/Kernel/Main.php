<?php

namespace Celestial\Kernel;

use Celestial\Config\Application;
use Constellation\Container\Container;
use Constellation\Http\Request;
use Constellation\Routing\Route;
use Constellation\Routing\Router;
use Constellation\Controller\Controller;
use Constellation\Http\{ApiResponse, WebResponse, IResponse as Response};

/**
 * @class Main
 * Responsibilities:
 *   - Initialize router
 *   - Match route to URI pattern
 *   - Invoke controller and call endpoint
 */
class Main
{
    private Router $router;
    private ?Route $route;
    private Controller $controller;
    private Response $response;

    public function __construct()
    {
        $this->configureContainer()
            ->initRouter()
            ->initRoute()
            ->initController()
            ->initResponse()
            ->execute();
    }

    private function configureContainer()
    {
        $this->container = Container::getInstance()
            ->setDefinitions(__DIR__ . "/config.php")
            ->build();
        return $this;
    }

    private function initRouter()
    {
        $router_config = Application::$router;
        $this->router = new Router(
            $router_config,
            new Request(
                $_SERVER["REQUEST_URI"],
                $_SERVER["REQUEST_METHOD"],
                $_REQUEST
            )
        );
        return $this;
    }

    private function initRoute()
    {
        $this->router->registerRoutes();
        $route = $this->router->matchRoute()->getRoute();
        if ($route) {
            $this->route = $route;
        } else {
            $this->router->pageNotFound();
        }
        return $this;
    }

    private function initController()
    {
        $class_name = $this->route?->getClassName();
        $this->controller = $this->container->get($class_name);
        return $this;
    }

    private function initResponse()
    {
        $endpoint = $this->route?->getEndpoint();
        $params = $this->route?->getParams();
        $middleware = $this->route?->getMiddleware();
        $data = $this->controller->$endpoint(...$params);
        $this->response = in_array("api", $middleware)
            ? new ApiResponse($data)
            : new WebResponse($data);
        $this->response->prepare();
        return $this;
    }

    public function execute()
    {
        $this->response->execute();
    }
}
