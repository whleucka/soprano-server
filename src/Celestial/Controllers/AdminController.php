<?php

namespace Celestial\Controllers;

use Constellation\Controller\Controller as BaseController;
use Constellation\Routing\{Get, Post};
use Constellation\View\Item;

class AdminController extends BaseController
{
    #[Get("/admin", "admin.index", ["auth"])]
    public function index()
    {
        return $this->render("admin/dashboard.html");
    }

    #[Post("/admin/users", "admin.users", ["auth"])]
    #[Get("/admin/users", "admin.users", ["auth"])]
    public function users()
    {
        $item = new Item($this, "Users");
        $item->rows_per_page = 5;
        $item->table_name = "users";
        $item->name_col = "uuid";
        $item->list_columns = [
            "id" => "ID",
            "uuid" => "UUID",
            "name" => "Name",
            "email" => "E-mail",
            ".1 as pct" => "Percent",
            "created_at" => "Created",
        ];
        $item->list_type = [
            "pct" => "pct",
            "created_at" => "ago",
        ];
        $item->list_align = [
            "id" => "right",
            "pct" => "right",
        ];
        $item->list_format = [
            "name" => function ($col, $val) {
                return "<span style='color: red'>{$val}</span>";
            },
        ];
        $item->list_override = [
            "name" => function ($item, $col) {
                if ($item[$col] == "William Hleucka") {
                    return "Me";
                }
                return $item[$col];
            },
        ];
        $item->edit_columns = [
            "name" => "Name",
            "email" => "E-mail",
        ];
        $item->edit_type = [
            "name" => "input",
            "email" => "input",
        ];
        $item->validate = [
            "name" => ["required"],
        ];
        $item->init();
    }
}
