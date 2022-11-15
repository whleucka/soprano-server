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
    protected string $title = "";

    public function __construct(public ?string $module = null)
    {
        // Get database instance
        $this->db = DB::getInstance();
        // Get request
        $this->request = Request::getInstance();
    }

    protected function getTable()
    {
        ob_start();
        include __DIR__ . "/Table.php";
        $table = ob_get_clean();
        return $table;
    }

    protected function getForm()
    {
        ob_start();
        include __DIR__ . "/Form.php";
        $form = ob_get_clean();
        return $form;
    }

    public function index(Controller $controller)
    {
        $table = $this->getTable();
        echo $controller->render("admin/table.html", [
            "table" => $table,
        ]);
    }

    public function create(Controller $controller)
    {
        $form = $this->getForm();
        echo $controller->render("admin/form.html", [
            "form" => $form,
        ]);
    }

    public function edit(Controller $controller, $id)
    {
        $form = $this->getForm();
        echo $controller->render("admin/form.html", [
            "form" => $form,
        ]);
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
