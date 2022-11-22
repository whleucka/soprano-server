<?php

namespace Celestial\Controllers\Admin\Modules;

use Constellation\Authentication\Auth;
use Constellation\Module\Module;

class Audit extends Module
{
    public function __construct()
    {
        $this->user = Auth::user();
        $this->allow_insert = $this->allow_edit = $this->allow_delete = false;
        $this->table = "audit";
        $this->title = "Audit";
        $this->table_columns = [
            "audit.id" => "ID",
            "users.name" => "User",
            "table_name" => "Table",
            "table_id" => "ID",
            "field" => "Field",
            "old_value" => "Old Value",
            "new_value" => "New Value",
            "message" => "Message",
            "audit.created_at" => "Created",
        ];
        $this->table_format = [
            "created_at" => "datetime-local",
        ];
        $this->extra_tables = ["INNER JOIN users ON users.id = user_id"];
        $this->table_filters = [
            "field",
            "old_value",
            "new_value",
            "name",
            "message",
        ];
        $this->order_by_clause = "created_at";
        $this->sort_clause = "DESC";
        $this->filter_links = [
            "Me" => "user_id = " . $this->user?->id,
            "Others" => "user_id != " . $this->user?->id,
        ];
        parent::__construct("audit");
    }
}
