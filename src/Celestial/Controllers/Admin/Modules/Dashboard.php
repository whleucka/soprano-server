<?php

namespace Celestial\Controllers\Admin\Modules;

use Constellation\Module\Module;

class Dashboard extends Module
{
    public function __construct()
    {
        $this->allow_insert = $this->allow_edit = $this->allow_delete = false;
        $this->title = "Dashboard";
        parent::__construct("dashboard");
    }
}

