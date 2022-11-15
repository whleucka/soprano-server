<?php

namespace Celestial\Controllers\Admin\Modules;

use Celestial\Controllers\Admin\Module;

class Users extends Module
{
    public function __construct()
    {
        $this->title = "Users";
        $this->table = "users";
        $this->name_col = "uuid";
        $this->limit_clause = 1;
        $this->table_columns = [
            "id as id" => "ID",
            "uuid" => "UUID",
            "name" => "Name",
            "email" => "E-mail",
            "created_at" => "Created",
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
