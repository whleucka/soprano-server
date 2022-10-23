<?php

namespace Celestial\Controllers;

use Constellation\Controller\Controller as BaseController;
use Constellation\Routing\Get;
use Constellation\View\Item;

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
        $item = new Item($this, "Users");
        $item->table_name = "users";
        $item->name_col = "uuid";
        $item->list_columns = [
            "id" => "ID",
            "uuid" => "UUID",
            "name" => "Name",
            "email" => "E-mail",
            "created_at" => "Created At",
            "updated_at" => "Updated At",
        ];
        $item->edit_columns = [
            "name" => "Name",
            "email" => "E-mail",
            "password" => "Password",
            "password_match" => "Password (again)",
        ];
        $item->processRequest();
    }
}
