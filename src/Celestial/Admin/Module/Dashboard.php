<?php

namespace Celestial\Admin\Module;

use Constellation\Controller\Controller;
use Constellation\View\Item;

class Dashboard extends Item
{
    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
        parent::__construct($controller, "Dashboard");
    }

    public function output(): string
    {
        return "<p>Hello, {$this->controller->user->name}</p>";
    }
}
