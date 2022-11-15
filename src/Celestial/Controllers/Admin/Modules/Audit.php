<?php

namespace Celestial\Controllers\Admin\Modules;

use Celestial\Controllers\Admin\Module;

class Audit extends Module
{
    public function __construct()
    {
        $this->title = "Audit";
        parent::__construct("audit");
    }
}
