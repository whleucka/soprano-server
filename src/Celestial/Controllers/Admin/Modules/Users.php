<?php

namespace Celestial\Controllers\Admin\Modules;

use Celestial\Controllers\Admin\Module;

class Users extends Module
{
    public function __construct()
    {
        $this->title = "Users";
        parent::__construct("users");
    }
}
