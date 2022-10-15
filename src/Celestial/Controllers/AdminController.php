<?php

namespace Celestial\Controllers;

use Constellation\Controller\Controller as BaseController;
use Constellation\Routing\Get;

class AdminController extends BaseController
{
    #[Get("/dashboard", "admin.dashboard", ["auth"])]
    public function index()
    {
        return $this->render("admin/dashboard.html");
    }
}
