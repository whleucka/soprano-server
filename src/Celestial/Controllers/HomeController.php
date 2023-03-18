<?php

namespace Celestial\Controllers;

use Constellation\Controller\Controller as BaseController;
use Constellation\Routing\{Post, Get};

class HomeController extends BaseController
{
    #[Get("/", "home.index")]
    public function index()
    {
        return $this->render("home/index.html", [
            "greeting" => "Hello, world!",
        ]);
    }

    #[Post("/api/v1/test", "home.test", ["api"])]
    public function test()
    {
        return [
            "payload" => "test!!!",
        ];
    }

    #[Get("/api/v1/answer", "home.answer", ["api"])]
    public function answer()
    {
        return [
            "message" =>
                "'The Answer to the Great Question... Of Life, the Universe and Everything... Is... Forty-two,' said Deep Thought, with infinite majesty and calm.",
            "payload" => 42,
        ];
    }
}
