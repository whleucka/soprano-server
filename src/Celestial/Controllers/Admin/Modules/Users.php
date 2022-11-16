<?php

namespace Celestial\Controllers\Admin\Modules;

use Celestial\Controllers\Admin\Module;
use Constellation\Authentication\Auth;

class Users extends Module
{
    public function __construct()
    {
        $this->title = "Users";
        $this->name_col = "uuid";
        $this->table = "users";
        $this->table_columns = [
            "id as id" => "ID",
            "uuid" => "UUID",
            "name" => "Name",
            "email" => "E-mail",
            "created_at" => "Created",
        ];
        $this->table_format = [
            "name" => function ($column, $value) {
                $user = Auth::user();
                if ($user->name == $value) {
                    return "<span style='color: royalblue;'>Me</span>";
                }
            },
        ];
        $this->form_columns = [
            "name" => "Name",
            "email" => "E-mail",
            "'' as password" => "Password",
            "'' as password_match" => "Password (again)",
            // These columns are part of the dataset, but are not rendered
            "uuid" => "UUID",
            "created_at" => "Created",
        ];
        $this->table_filters = ["name", "email"];
        parent::__construct("users");
    }
}
