<?php

namespace Celestial\Controllers;

use Constellation\Controller\Controller as BaseController;
use Constellation\Routing\Get;

class HomeController extends BaseController
{
    #[Get("/", "home.index")]
    public function index()
    {
        return $this->render("home/index.html", [
            "greeting" => "Hello, world!",
        ]);
    }

    #[Get("/home", "home.home", ["auth"])]
    public function home()
    {
        return $this->render("home/index.html", [
            "greeting" => "Welcome home!",
        ]);
    }

    #[Get("/api/v1/answer", "home.home", ["api"])]
    public function answer()
    {
        return [
            "message" => "Time is an illusion.",
            "payload" => 42
        ];
    }
}
