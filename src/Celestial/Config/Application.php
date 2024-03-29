<?php

namespace Celestial\Config;

define("ROOT", __DIR__ . "/../../../");

class Application
{
    public static $soprano = [
        "music_path" => "/music",
    ];
    public static $allowed_origins = [
        "domains" => [
            "http://localhost:3000",
            "https://soprano.williamhleucka.com",
        ]
    ];
    public static $environment = [
        "environment_path" => ROOT,
    ];
    public static $router = [
        "controller_path" => ROOT . "src/Celestial/Controllers",
    ];
    public static $migrations = [
        "migrations_path" => ROOT . "migrations",
    ];
    public static $model = [
        "model_path" => ROOT . "src/Celestial/Models",
    ];
    public static $view = [
        "view_path" => ROOT . "views",
        "cache_path" => ROOT . "views/.cache",
    ];
    public static $middleware = [
        "middleware_path" => ROOT . "src/Celestial/Middleware",
    ];
    public static $storage = [
        "storage_path" => ROOT . 'storage',
        "public_storage_path" => ROOT . 'public/storage',
    ];
}
