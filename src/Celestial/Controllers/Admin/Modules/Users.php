<?php

namespace Celestial\Controllers\Admin\Modules;

use Constellation\Module\Module;
use Constellation\Authentication\Auth;

class Users extends Module
{
    public function __construct()
    {
        $this->user = Auth::user();
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
                if ($this->user->name == $value) {
                    return "<span style='color: royalblue;'>Me</span>";
                }
            },
        ];
        $this->table_filters = ["name", "email"];
        $this->filter_links = [
            "Me" => "id = " . $this->user?->id,
            "Others" => "id != " . $this->user?->id,
        ];
        $this->form_columns = [
            "name" => "Name",
            "email" => "E-mail",
        ];
        $this->form_control = [
            "name" => "text",
            "email" => "email",
        ];
        parent::__construct("users");
    }

    protected function hasDeletePermission($id)
    {
        // For now, you cannot delete your own account
        // TODO user permissions
        return parent::hasDeletePermission($id) && $id != $this->user->id;
    }
}
