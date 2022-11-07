<?php

namespace Celestial\Controllers;

use Constellation\Controller\Controller as BaseController;
use Constellation\Routing\{Get, Post};
use Celestial\Admin\Module\{Dashboard, Users};

class AdminController extends BaseController
{
    #[Get("/admin", "Dashboard", ["auth", "module"])]
    public function index()
    {
        $users = new Dashboard($this);
        $users->init();
    }

    #[Post("/admin/users", null, ["auth"])]
    #[Get("/admin/users", "Users", ["auth", "module"])]
    public function users()
    {
        $users = new Users($this);
        $users->init();
    }
}
