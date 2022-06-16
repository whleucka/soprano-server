<?php

namespace Celestial\Config;

class Application
{
    public static $container = [
        "definitions" => [],
    ];
    public static $router = [
        "controller_path" => __DIR__ . "/../Controllers",
    ];
}
