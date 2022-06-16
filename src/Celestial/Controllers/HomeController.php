<?php

namespace Celestial\Controllers;

use Constellation\Controller\Controller as BaseController;
use Constellation\Routing\Get;

class HomeController extends BaseController
{
    #[Get("/", "home.index")]
    public function index()
    {
        echo "Index";
    }

    #[Get("/home", "home.index", ["auth"])]
    public function home()
    {
        echo "Home";
    }
}
