<?php

namespace Celestial\Admin\Module;

use Constellation\Controller\Controller;
use Constellation\View\Item;

class Audit extends Item
{
    public function __construct(Controller $controller)
    {
        $this->table_name = "audit";
        $this->key_col = "audit.id";
        $this->default_sort = "id";
        $this->list_add = $this->list_delete = $this->list_edit = false;
        $this->list_columns = [
            "audit.id as id" => "ID",
            "users.name as name" => "User",
            "audit.table_name as table_name" => "Table",
            "audit.table_id as table_id" => "ID",
            "audit.field as field" => "Field",
            "ifnull(audit.old_value, 'NULL') as old_value" => "Old Value",
            "ifnull(audit.new_value, 'NULL') as new_value" => "New Value",
            "audit.message as message" => "Message",
            "audit.created_at as created_at" => "Created At",
        ];
        $this->list_type = [
            "id" => "text",
            "name" => "text",
            "table_name" => "text",
            "table_id" => "text",
            "field" => "text",
            "old_value" => "text",
            "new_value" => "text",
            "message" => "text",
            "created_at" => "text",
        ];
        $this->list_filters = ["table_name", "field", "message"];
        $this->filter_links = [
            "All" => "1=1",
            "Register" => "message='REGISTER'",
            "Update" => "message='UPDATE'",
            "Insert" => "message='INSERT'",
            "Delete" => "message='DELETE'",
        ];
        $this->date_filter = "audit.created_at";
        $this->extra_tables = "INNER JOIN users ON users.id = audit.user_id";
        $this->default_order = "DESC";
        parent::__construct($controller, "Audit");
    }
}
