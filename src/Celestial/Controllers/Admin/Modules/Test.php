<?php

namespace Celestial\Controllers\Admin\Modules;

use Constellation\Module\Module;

class Test extends Module
{
    public function __construct()
    {
        $this->allow_insert = $this->allow_edit = $this->allow_delete = false;
        $this->title = "__Test__";
        parent::__construct("test");
    }
}
