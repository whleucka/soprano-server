<?php

namespace Celestial\Controllers;

use Constellation\Controller\Controller as BaseController;
use Constellation\Routing\Get;

class HomeController extends BaseController
{
    #[Get("/", "home.index")]
    public function index()
    {
        return "Nothing to see here.";
    }
}
