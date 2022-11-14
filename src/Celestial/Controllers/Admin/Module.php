<?php

namespace Celestial\Controllers\Admin;

use Constellation\Controller\Controller;
use Constellation\Database\DB;
use Constellation\Http\Request;

class Module
{
    protected DB $db;
    protected Request $request;
    protected mixed $data = null;

    public function __construct(public ?string $name = null)
    {
        // Get database instance
        $this->db = DB::getInstance();
        // Get request
        $this->request = Request::getInstance();
    }

    public function index(Controller $controller)
    {
        echo $controller->render("layouts/module.html");
    }

    public function create(Controller $controller)
    {
        echo $controller->render("layouts/module.html");
    }

    public function edit(Controller $controller, $id)
    {
        echo $controller->render("layouts/module.html");
    }

    public function store()
    {
        echo "store()";
    }

    public function update()
    {
        echo "update()";
    }

    public function destroy()
    {
        echo "destroy()";
    }
}
