<?php

namespace Celestial\Controllers\Admin\Modules;

use Celestial\Controllers\Admin\Module;

class Sessions extends Module
{
    public function __construct()
    {
        $this->title = "Sessions";
        parent::__construct("sessions");
    }
}
