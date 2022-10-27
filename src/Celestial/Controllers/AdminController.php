<?php

namespace Celestial\Controllers;

use Celestial\Models\User;
use Constellation\Authentication\Auth;
use Constellation\Controller\Controller as BaseController;
use Constellation\Routing\{Get, Post};
use Constellation\Validation\Validate;
use Constellation\View\Item;
use Ramsey\Uuid\Uuid;

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
            "id" => "text",
            "uuid" => "text",
            "name" => "text",
            "email" => "text",
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
            "name" => function ($row, $col) {
                if ($row[$col] == "William Hleucka") {
                    return "Me";
                }
                return $row[$col];
            },
        ];
        $item->edit_columns = [
            "name" => "Name",
            "email" => "E-mail",
            "'' as password" => "Password",
            "'' as password_match" => "Password (again)",
            // These columns are part of the dataset but are not rendered
            "uuid" => "UUID",
            "created_at" => "Created",
        ];
        $item->edit_type = [
            "name" => "input",
            "email" => "email",
            "password" => "password",
            "password_match" => "password",
        ];
        $item->validate = [
            "name" => ["required"],
            "email" => ["required", "email"],
            "password" => [
                "required",
                "match",
                "min_length=8",
                "uppercase=1",
                "lowercase=1",
                "symbol=1",
            ],
        ];
        $item->edit_override = [
            "password" => function ($row, $col) use ($item) {
                if (in_array($item->mode, ["insert", "save"])) {
                    return Auth::hashPassword($row[$col]);
                }
                return "";
            },
        ];
        $item->edit_default["uuid"] = Uuid::uuid4()->toString();
        $item->edit_default["created_at"] = date("Y-m-d H:i:s");
        Validate::$custom["email"] = function ($rule, $value) use ($item) {
            $user = User::findByAttribute("email", $value);
            if ($item->mode == "insert") {
                if ($user) {
                    Validate::$errors["email"][] =
                        "This email is already associated with another user";
                    return false;
                }
            } elseif ($item->mode == "save") {
                if ($user && $user->email != $value) {
                    Validate::$errors["email"][] =
                        "This email is already associated with another user";
                    return false;
                }
            }
            return true;
        };
        $item->init();
    }
}
