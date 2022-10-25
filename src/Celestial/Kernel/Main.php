<?php

namespace Celestial\Kernel;

use Dotenv\Dotenv;
use Celestial\Config\Application;
use Celestial\Middleware\RouteMiddleware;
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
    public Container $container;

    public function __construct($router_enabled = true)
    {
        /**
         * Begin bootstrapping the Application
         */
        $this->configureContainer()
            ->loadEnvironment()
            ->initDatabase();
        /**
         * Routing will be optional, which is useful
         * in scripting scenarios.
         */
        if ($router_enabled) {
            $this->initRouter()
                ->initRoute()
                ->initController()
                ->prepareResponse()
                ->executeResponse();
        }
    }

    /**
     * Configure the DI container
     * @return Main
     */
    private function configureContainer(): Main
    {
        $this->container = Container::getInstance()
            ->setDefinitions(__DIR__ . "/config.php")
            ->build();
        return $this;
    }

    /**
     * Load environment variables (ie, .env .env.local, etc)
     * @return Main
     */
    private function loadEnvironment(): Main
    {
        $environment_path = Application::$environment["environment_path"];
        $dotenv = Dotenv::createImmutable($environment_path);
        $dotenv->load();
        return $this;
    }

    /**
     * Initialize the database connection
     * @return Main
     */
    private function initDatabase(): Main
    {
        $this->db =
            $_ENV["DB_TYPE"] != "none"
                ? $this->container->get(DB::class)
                : null;
        return $this;
    }

    /**
     * Initialize the application router
     * @return Main
     */
    private function initRouter(): Main
    {
        $this->router = $this->container->get(Router::class);
        return $this;
    }

    /**
     * The route matched via URI regex pattern
     * @return Main
     */
    private function initRoute(): Main
    {
        $this->router->registerRoutes();
        $route = $this->router->matchRoute()->getRoute();
        if ($route) {
            // We found a route, invoke route middleware
            $middleware = $this->container->get(RouteMiddleware::class);
            $middleware->process();
            $this->route = $route;
        } else {
            $this->router->pageNotFound();
        }
        return $this;
    }

    /**
     * The route controller
     * @return Main
     */
    private function initController(): Main
    {
        $class_name = $this->route?->getClassName();
        $this->controller = $this->container->get($class_name);
        return $this;
    }

    /**
     * The response is prepared before execution
     * @return Main
     */
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

    /**
     * The response invoked
     * @return Main
     */
    public function executeResponse(): void
    {
        $this->response->execute();
    }
}
