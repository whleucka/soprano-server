<?php

namespace Celestial\Kernel;

use Celestial\Config\Application;
use Constellation\Database\DB;
use Constellation\Routing\Router;
use Constellation\Http\Request;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use DI;

return [
    Environment::class => function () {
        $loader = new FilesystemLoader(Application::$view["view_path"]);
        return new Environment($loader, [
            "cache" => Application::$view["cache_path"],
            "auto_reload" => true,
        ]);
    },
    Request::class => function () {
        if ($_SERVER["REQUEST_METHOD"] === "GET") {
            $data = $_GET;
        } else if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $data = $_POST;
        } else {
            $data = json_decode(file_get_contents('php://input'), true);
        }
        return new Request(
            $_SERVER["REQUEST_URI"],
            $_SERVER["REQUEST_METHOD"],
            $data,
        );
    },
    Router::class => DI\autowire()->constructorParameter(
        "config",
        Application::$router
    ),
    DB::class => DI\autowire()->constructorParameter("config", [
        "show_errors" => $_ENV["DB_SHOW_ERRORS"] == "true",
        "path" => $_ENV["DB_PATH"],
        "type" => $_ENV["DB_TYPE"],
        "host" => $_ENV["DB_HOST"],
        "dbname" => $_ENV["DB_NAME"],
        "username" => $_ENV["DB_USER"],
        "password" => $_ENV["DB_PASSWORD"],
        "port" => $_ENV["DB_PORT"],
    ]),
];
