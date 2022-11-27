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

    /**
     * getTable returns a table string. However, it can return
     * any string which will be rentered as the table var
     */
    protected function getTable()
    {
        return "<p>Hello, {$this->user->name}</p>";
    }
}
