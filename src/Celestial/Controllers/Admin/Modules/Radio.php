<?php

namespace Celestial\Controllers\Admin\Modules;

use Constellation\Authentication\Auth;
use Constellation\Module\Module;

class Radio extends Module
{
    public function __construct()
    {
        $this->user = Auth::user();
        $this->title = "Radio";
        $this->name_col = "id";
        $this->table = "radio";
        $this->table_columns = [
            "id" => "ID",
            "station_name" => "Station Name",
            "location" => "Location",
            "created_at" => "Created",
        ];
        $this->table_format = [
            "created_at" => "datetime-local",
        ];
        $this->table_filters = ["station_name", "location"];
        $this->form_columns = [
            "station_name" => "Station Name",
            "location" => "Location",
            "src_url" => "Source URL",
            "cover_url" => "Cover URL",
        ];
        $this->form_control = [
            "station_name" => "input",
            "location" => "input",
            "src_url" => "input",
            "cover_url" => "input",
        ];
        $this->validate = [
            "station_name" => ["required"],
            "src_url" => ["required"],
        ];
        parent::__construct("customers");
    }
}

