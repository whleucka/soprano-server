<?php

namespace Celestial\Controllers\Admin\Modules;

use Celestial\Controllers\Admin\Module;

class Sessions extends Module
{
    public function __construct()
    {
        $this->allow_insert = $this->allow_edit = $this->allow_delete = false;
        $this->show_table_actions = false;
        $this->title = "Sessions";
        $this->table = "sessions";
        $this->table_columns = [
            "id" => "ID",
            "user_id" => "User",
            "ip" => "IP",
            "url" => "URL",
            "created_at" => "Created",
        ];
        $this->table_format = [
            "created_at" => "ago",
        ];
        $this->table_filters = ["ip"];
        $this->order_by_clause = "created_at";
        $this->sort_clause = "DESC";
        parent::__construct("sessions");
    }
}
