<?php

namespace Celestial\Kernel;

use Dotenv\Dotenv;
use Celestial\Config\Application;
use Constellation\Container\Container;
use Constellation\Routing\Route;
use Constellation\Routing\Router;
use Constellation\Controller\Controller;
use Constellation\Database\DB;
use Constellation\Http\{ApiResponse, WebResponse, IResponse as Response};

/**
 * @class Main
 * Responsibilities:
 *   - Read environment variables
 *   - Establish DB connection
 *   - Initialize router
 *   - Match route to URI pattern
 *   - Invoke controller and call endpoint
 *   - Server response
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
            ->loadEnvironment()
            ->initDatabase()
            ->initRouter()
            ->initRoute()
            ->initController()
            ->prepareResponse()
            ->executeResponse();
    }

    private function configureContainer(): Main
    {
        $this->container = Container::getInstance()
            ->setDefinitions(__DIR__ . "/config.php")
            ->build();
        return $this;
    }

    private function loadEnvironment(): Main
    {
        $environment_path = Application::$environment["environment_path"];
        $dotenv = Dotenv::createImmutable($environment_path);
        $dotenv->load();
        return $this;
    }

    private function initDatabase(): Main
    {
        $this->database = $this->container->get(DB::class);
        return $this;
    }

    private function initRouter(): Main
    {
        $this->router = $this->container->get(Router::class);
        return $this;
    }

    private function initRoute(): Main
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

    private function initController(): Main
    {
        $class_name = $this->route?->getClassName();
        $this->controller = $this->container->get($class_name);
        return $this;
    }

    private function prepareResponse(): Main
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

    public function executeResponse(): void
    {
        $this->response->execute();
    }
}
