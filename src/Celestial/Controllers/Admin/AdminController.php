<?php

namespace Celestial\Controllers\Admin;

use Constellation\Controller\Controller;
use Constellation\Routing\{Get, Post, Delete, Patch};
use Composer\Autoload\ClassMapGenerator;

class AdminController extends Controller
{
    private array $modules = [];

    private function getModule($name)
    {
        // Map the modules by name & class
        $module_map = ClassMapGenerator::createMap(__DIR__ . "/Modules");
        foreach ($module_map as $class => $path) {
            $module = new $class();
            $this->modules[$module->module] = $class;
        }
        if (key_exists($name, $this->modules)) {
            return new ($this->modules[$name])();
        }
        // TODO make a nice module not found page
        header("HTTP/1.0 404 Not Found");
        echo "Module not found";
        die();
    }

    #[Get("/admin", "admin.index", ["auth"])]
    public function admin()
    {
        return $this->render("layouts/module.html");
    }

    /**
     * Show all module items (view)
     */
    #[Get("/admin/module/{module}", "module.index", ["auth"])]
    public function index($module)
    {
        $module = $this->getModule($module);
        $module->index($this);
    }

    /**
     * Create a new module item (view)
     */
    #[Get("/admin/module/{module}/create", "module.create", ["auth"])]
    public function create($module)
    {
        $module = $this->getModule($module);
        $module->create($this);
    }

    /**
     * Edit and existing module item (view)
     */
    #[Get("/admin/module/{module}/{id}/edit", "module.edit", ["auth"])]
    public function edit($module, $id)
    {
        $module = $this->getModule($module);
        $module->edit($this, $id);
    }

    /**
     * Store a new module item in the database
     */
    #[Post("/admin/module/{module}", "module.store", ["auth"])]
    public function store($module)
    {
    }

    /**
     * Update an existing module item in the database
     */
    #[Patch("/admin/module/{module}/{id}", "module.update", ["auth"])]
    public function update($module, $id)
    {
    }

    /**
     * Destroy an existing module item in the database
     */
    #[Delete("/admin/module/{module}/{id}", "module.destroy", ["auth"])]
    public function destroy($module, $id)
    {
    }
}
