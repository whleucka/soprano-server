<?php

namespace Celestial\Controllers\Admin\Modules;

use Constellation\Module\Module;
use Constellation\Authentication\Auth;

class Sessions extends Module
{
    public function __construct()
    {
        $this->user = Auth::user();
        $this->allow_insert = $this->allow_edit = $this->allow_delete = false;
        $this->show_table_actions = false;
        $this->title = "Sessions";
        $this->table = "sessions";
        $this->table_columns = [
            "users.name" => "User",
            "ip" => "IP",
            "url" => "URL",
            "sessions.created_at" => "Created",
        ];
        $this->table_format = [
            "created_at" => "datetime-local",
        ];
        $this->extra_tables = [
            "INNER JOIN users ON users.id = sessions.user_id",
        ];
        $this->table_filters = ["ip", "name"];
        $this->order_by_clause = "sessions.created_at";
        $this->sort_clause = "DESC";
        $this->filter_links = [
            "Me" => "user_id = " . $this->user?->id,
            "Others" => "user_id != " . $this->user?->id,
        ];
        parent::__construct("sessions");
    }
}
