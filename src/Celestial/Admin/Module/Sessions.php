<?php

namespace Celestial\Admin\Module;

use Constellation\Controller\Controller;
use Constellation\View\Item;

class Sessions extends Item
{
    public function __construct(Controller $controller)
    {
        $this->table_name = "sessions";
        $this->key_col = "sessions.id";
        $this->list_add = $this->list_delete = $this->list_edit = false;
        $this->list_columns = [
            "sessions.id as id" => "ID",
            "users.name as name" => "User",
            "sessions.ip as ip" => "IP",
            "sessions.url as url" => "URL",
            "sessions.created_at as created_at" => "Created",
        ];
        $this->list_type = [
            "id" => "text",
            "name" => "text",
            "ip" => "text",
            "url" => "text",
            "created_at" => "ago",
        ];
        $this->list_filters = ["users.name", "sessions.ip"];
        $this->extra_tables = "INNER JOIN users ON users.id = sessions.user_id";
        $this->default_order = "DESC";
        parent::__construct($controller, "Sessions");
    }
}