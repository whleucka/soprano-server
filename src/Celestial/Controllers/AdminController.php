<?php

namespace Celestial\Controllers;

use Constellation\Controller\Controller as BaseController;
use Constellation\Routing\Get;
use Constellation\View\{EditView,ListView};

class AdminController extends BaseController
{
    #[Get("/dashboard", "admin.dashboard", ["auth"])]
    public function index()
    {
        return $this->render("admin/dashboard.html");
    }

    #[Get("/users", "admin.users", ["auth"])]
    public function users()
    {
        $view = new ListView("Users", $this);
        $view->render();
    }
}
