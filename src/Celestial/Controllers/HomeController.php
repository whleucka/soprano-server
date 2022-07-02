<?php

namespace Celestial\Controllers;

use Constellation\Controller\Controller as BaseController;
use Constellation\Routing\Get;

class HomeController extends BaseController
{
    #[Get("/", "home.index")]
    public function index()
    {
        return $this->twig->render("home/index.html", [
            "greeting" => "Hello, world!",
        ]);
    }

    #[Get("/home", "home.home", ["auth"])]
    public function home()
    {
        return $this->twig->render("home/home.html", [
            "greeting" => "Welcome home!",
        ]);
    }
}
