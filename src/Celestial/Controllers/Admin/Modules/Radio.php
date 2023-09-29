<?php

namespace Celestial\Controllers\Admin\Modules;

use Constellation\Module\Module;
use Constellation\Validation\Validate;

class Radio extends Module
{
    public function __construct()
    {
        $this->title = "Radio";
        $this->name_col = "id";
        $this->table = "radio";
        $this->table_columns = [
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
        // testing location custom validate
        $this->validate = [
            "location" => fn($col_name, $value) => strpos($value, ',') !== false,
            "station_name" => ["required"],
            "src_url" => ["required", "reg_ex=\.m3u8"],
        ];
        Validate::$messages["reg_ex"] = "Source URL must be .m3u8";
        Validate::$messages["location"] = "Location must be formatted with commas. Eg) City, Province, Country";
        parent::__construct("radio");
    }
}
