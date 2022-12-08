<?php

namespace Celestial\Controllers\Admin\Modules;

use Celestial\Models\Customer;
use Constellation\Module\Module;
use Constellation\Authentication\Auth;
use Constellation\Controller\Controller;
use Constellation\Validation\Validate;

class Customers extends Module
{
    public function __construct()
    {
        $this->user = Auth::user();
        $this->title = "Customers";
        $this->name_col = "uuid";
        $this->table = "customers";
        $this->table_columns = [
            "uuid" => "UUID",
            "name" => "Name",
            "email" => "E-mail",
            "created_at" => "Created",
        ];
        $this->table_format = [
            "created_at" => "datetime-local",
        ];
        $this->table_filters = ["name", "email"];
        $this->form_columns = [
            "name" => "Name",
            "email" => "E-mail",
            "'' as password" => "Password",
            "'' as password_match" => "Password (again)",
        ];
        $this->form_control = [
            // Control by type
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
        parent::__construct("customers");
    }

    public function update(Controller $controller, $id)
    {
        // (on update existing record) If the password + password match field
        // are empty, then we do not need to change the password
        if (
            !$this->request->data["password"] &&
            !$this->request->data["password_match"]
        ) {
            unset($this->validate["password"]);
            unset($this->request->data["password"]);
            unset($this->request->data["password_match"]);
        }
        parent::update($controller, $id);
    }

    protected function validateRequest(Controller $controller, $id = null)
    {
        // Custom validation on email field
        Validate::$custom["email"] = function ($rule, $value) use ($id) {
            // Override the default message
            $customer = Customer::findByAttribute("email", $value);
            // No user found with this email
            if (!$customer) {
                return null;
            }
            // No changes required
            if ($customer && $customer->id == $id && $customer->email == $value) {
                return null;
            }
            if ($customer) {
                return "Email is already associated with another user";
            }
        };
        return parent::validateRequest($controller, $id);
    }

    protected function updateModule($id)
    {
        if (isset($this->dataset["password"])) {
            $this->dataset["password"] = Auth::hashPassword(
                $this->request->data["password"]
            );
        }
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
