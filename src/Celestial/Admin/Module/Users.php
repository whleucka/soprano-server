<?php

namespace Celestial\Admin\Module;

use Constellation\Controller\Controller;
use Constellation\View\Item;
use Celestial\Models\User;
use Constellation\Authentication\Auth;
use Constellation\Validation\Validate;
use Ramsey\Uuid\Uuid;

class Users extends Item
{
    public function __construct(Controller $controller)
    {
        $this->rows_per_page = 5;
        $this->table_name = "users";
        $this->name_col = "uuid";

        $this->list_columns = [
            "id" => "ID",
            "uuid" => "UUID",
            "name" => "Name",
            "email" => "E-mail",
            "created_at" => "Created At",
        ];
        $this->list_type = [
            "id" => "text",
            "uuid" => "text",
            "name" => "text",
            "email" => "text",
            "created_at" => "text",
        ];
        $this->list_align = [
            "id" => "right",
        ];
        $this->list_filters = ["name", "email"];

        $this->filter_links = [
            "Me" => "name='{$controller->user->name}'",
            "Others" => "name!='{$controller->user->name}'",
        ];
        $this->date_filter = "users.created_at";

        $this->edit_columns = [
            "name" => "Name",
            "email" => "E-mail",
            "'' as password" => "Password",
            "'' as password_match" => "Password (again)",
            // These columns are part of the dataset, but are not rendered
            "uuid" => "UUID",
            "created_at" => "Created",
        ];
        $this->edit_type = [
            "name" => "input",
            "email" => "email",
            "password" => "password",
            "password_match" => "password",
        ];
        $this->validate = [
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
        $this->edit_override = [
            "password" => function ($row, $col) {
                if (in_array($this->mode, ["insert", "save"])) {
                    return Auth::hashPassword($row[$col]);
                }
                return "";
            },
        ];

        // Insert default values
        $this->edit_default["uuid"] = Uuid::uuid4()->toString();
        $this->edit_default["created_at"] = date("Y-m-d H:i:s");

        // Custom validation on email
        Validate::$custom["email"] = function ($rule, $value) {
            $user = User::findByAttribute("email", $value);
            if ($this->mode == "insert") {
                if ($user) {
                    Validate::$errors["email"][] =
                        "This email is already associated with another user";
                    return false;
                }
            } elseif ($this->mode == "save") {
                if ($user && $user->email != $value) {
                    Validate::$errors["email"][] =
                        "This email is already associated with another user";
                    return false;
                }
            }
            return true;
        };

        // Example of list action
        //$this->list_actions[] = [
        //    "name" => "boss",
        //    "method" => "POST",
        //    "class" => "test",
        //    "title" => "A mouseover title",
        //    "label" => "Test",
        //    "onSubmit" => "alert(`Ding dong`);",
        //    "confirm" => "Are you a boss?",
        //    "processRequest" => function ($request) {
        //        Flash::addFlash("success", "You're a boss");
        //    },
        //    //"validate" => function($request) {
        //    //    Flash::addFlash("error", "Not today, bro");
        //    //    return false;
        //    //},
        //];
        //$this->list_format = [
        //    "name" => function ($col, $val) {
        //        return "<span style='color: royalblue;'>{$val}</span>";
        //    },
        //];
        //$this->list_override = [
        //    "name" => function ($row, $col) {
        //        if ($row[$col] == "William Hleucka") {
        //            return "Me";
        //        }
        //        return $row[$col];
        //    },
        //];
        parent::__construct($controller, "Users");
    }

    protected function hasDeletePermission(?string $id): bool
    {
        if ($id == $this->controller->user->id) {
            return false;
        }
        return parent::hasDeletePermission($id);
    }
}
