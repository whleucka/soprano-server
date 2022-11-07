<?php

namespace Celestial\Controllers;

use Constellation\Controller\Controller as BaseController;
use Constellation\Routing\{Get, Post};
use Celestial\Admin\Module\Users;
use Constellation\View\Item;

class AdminController extends BaseController
{
    #[Get("/admin", "Dashboard", ["auth", "module"])]
    public function index()
    {
        $item = new Item($this, "Dashboard");
        $item->init();
    }

    #[Post("/admin/users", null, ["auth"])]
    #[Get("/admin/users", "Users", ["auth", "module"])]
    public function users()
    {
        $users = new Users($this);
        $users->init();
    }
}
