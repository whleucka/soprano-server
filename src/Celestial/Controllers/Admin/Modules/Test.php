<?php

namespace Celestial\Controllers\Admin\Modules;

use Constellation\Module\Module;

class Test extends Module
{
    public function __construct()
    {
        $this->title = "__Test__";
        $this->table = "test";
        $this->form_columns = [
            "email" => "E-mail",
            "checkbox" => "Checkbox",
            "color" => "Colour",
            "date" => "Date",
            "datetime_local" => "Datetime (local)",
            "file" => "File",
            "image" => "Image",
            "month" => "Month",
            "number" => "Number",
            "password" => "Password",
            "radio" => "Radio",
            "range" => "Range",
            "search" => "Search",
            "tel" => "Telephone",
            "time" => "Time",
            "url" => "URL",
            "week" => "Week",
            "text" => "Text",
            "textarea" => "Textarea",
        ];
        $this->form_control = [
            "email" => "email",
            "checkbox" => "checkbox",
            "color" => "color",
            "date" => "date",
            "datetime_local" => "datetime-local",
            "file" => "file",
            "image" => "image",
            "month" => "month",
            "number" => "number",
            "password" => "password",
            "radio" => "radio",
            "range" => "range",
            "search" => "search",
            "tel" => "tel",
            "time" => "time",
            "url" => "url",
            "week" => "week",
            "text" => "text",
            "textarea" => "textarea",
        ];
        parent::__construct("test");
    }
}
