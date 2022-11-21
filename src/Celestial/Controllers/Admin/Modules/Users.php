<?php

namespace Celestial\Controllers\Admin\Modules;

use Celestial\Models\User;
use Constellation\Module\Module;
use Constellation\Authentication\Auth;
use Constellation\Controller\Controller;
use Constellation\Validation\Validate;

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
            "'' as password" => "Password",
            "'' as password_match" => "Password (again)",
        ];
        $this->form_control = [
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
        parent::__construct("users");
    }

    protected function validateRequest(Controller $controller, $id = null)
    {
        // Custom validation on email field
        Validate::$custom["email"] = function ($rule, $value) use ($id) {
            // Override the default message
            $user = User::findByAttribute("email", $value);
            // No user found with this email
            if (!$user) {
                return null;
            }
            // No changes required
            if ($user && $user->id == $id && $user->email == $value) {
                return null;
            }
            if ($user) {
                return "Email is already associated with another user";
            }
        };
        return parent::validateRequest($controller, $id);
    }

    protected function hasDeletePermission($id)
    {
        // For now, you cannot delete your own account
        // TODO user permissions
        return parent::hasDeletePermission($id) && $id != $this->user->id;
    }

    protected function updateModule($id)
    {
        $this->dataset["password"] = Auth::hashPassword(
            $this->request->data["password"]
        );
        $this->dataset["created_at"] = date("Y-m-d H:i:s");
        unset($this->dataset["password_match"]);
        return parent::updateModule($id);
    }

    protected function storeModule()
    {
        $this->dataset["uuid"] = Auth::generateUuid();
        $this->dataset["password"] = Auth::hashPassword(
            $this->request->data["password"]
        );
        $this->dataset["created_at"] = date("Y-m-d H:i:s");
        unset($this->dataset["password_match"]);
        return parent::storeModule();
    }
}
