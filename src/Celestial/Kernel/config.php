<?php

namespace Celestial\Kernel;

use Celestial\Config\Application;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

return [
    Environment::class => function () {
        $loader = new FilesystemLoader(Application::$view["view_path"]);
        return new Environment($loader, [
            "cache" => Application::$view["cache_path"],
            "auto_reload" => true,
        ]);
    },
];
