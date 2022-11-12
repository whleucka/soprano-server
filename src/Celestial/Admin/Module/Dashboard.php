<?php

namespace Celestial\Admin\Module;

use Constellation\Controller\Controller;
use Constellation\View\Item;

class Dashboard extends Item
{
    public function __construct(Controller $controller)
    {
        $this->output_table = false;
        $this->controller = $controller;
        parent::__construct($controller, "Dashboard");
    }

    protected function listView(): void
    {
        $this->render([
                "output" => $this->output(),
        ]);
    }

    private function output(): string
    {
        return "<p>Hello, {$this->controller->user->name}</p>";
    }
}
