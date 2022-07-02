<?php

namespace Celestial\Config;

class Application
{
    public static $environment = [
        "environment_path" => __DIR__ . "/../../../",
    ];
    public static $router = [
        "controller_path" => __DIR__ . "/../../../src/Celestial/Controllers",
    ];
    public static $migrations = [
        "migrations_path" => __DIR__ . "/../../../migrations",
    ];
    public static $view = [
        "view_path" => __DIR__ . "/../../../views",
        "cache_path" => __DIR__ . "/../../../cache",
    ];
}
