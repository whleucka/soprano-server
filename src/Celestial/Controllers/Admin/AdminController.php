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
            if (property_exists($module, "name")) {
                $this->modules[$module->name] = $class;
            }
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
    public function index($module_name)
    {
        $module = $this->getModule($module_name);
        $module->index($this);
    }

    /**
     * Create a new module item (view)
     */
    #[Get("/admin/module/{module}/create", "module.create", ["auth"])]
    public function create($module_name)
    {
        $module = $this->getModule($module_name);
        $module->create($this);
    }

    /**
     * Edit and existing module item (view)
     */
    #[Get("/admin/module/{module}/{item}/edit", "module.edit", ["auth"])]
    public function edit($module_name, $item)
    {
        $module = $this->getModule($module_name);
        $module->edit($this,$item);
    }

    /**
     * Store a new module item in the database
     */
    #[Post("/admin/module/{module}", "module.store", ["auth"])]
    public function store()
    {
    }

    /**
     * Update an existing module item in the database
     */
    #[Patch("/admin/module/{module}/{item}", "module.update", ["auth"])]
    public function update()
    {
    }

    /**
     * Destroy an existing module item in the database
     */
    #[Delete("/admin/module/{module}/{item}", "module.destroy", ["auth"])]
    public function destroy()
    {
    }
}
