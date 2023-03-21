<?php

namespace Celestial\Controllers;

use Constellation\Controller\Controller as BaseController;
use Constellation\Routing\Get;

class HomeController extends BaseController
{
    #[Get("/blog/{slug}", "blog.index")]
    public function index()
    {
        return $this->render("blog/index.html", [
            "greeting" => "Hello, world!",
        ]);
    }
}
