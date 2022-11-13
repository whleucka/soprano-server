<?php

namespace Celestial\Controllers\Admin;

use Constellation\Controller\Controller;
use Constellation\Routing\{Get, Post, Delete, Patch};
use Composer\Autoload\ClassMapGenerator;

class AdminController extends Controller
{
    private array $modules = [];

    public function __construct()
    {
        // Map the modules by name & class
        $module_map = ClassMapGenerator::createMap(__DIR__ . "/Modules");
        foreach ($module_map as $class => $path) {
            $module = new $class();
            if (property_exists($module, "name")) {
                $this->modules[$module->name] = $class;
            }
        }
    }

    private function getModule($name)
    {
        if (key_exists($name, $this->modules)) {
            return new ($this->modules[$name])();
        }
        // TODO make a nice module not found page
        header("HTTP/1.0 404 Not Found");
        echo "Module not found";
        die();
    }

    /**
     * Show all module items (view)
     */
    #[Get("/admin/{module}", "module.index")]
    public function index($module_name)
    {
        $module = $this->getModule($module_name);
        $module->index();
    }

    /**
     * Create a new module item (view)
     */
    #[Get("/admin/{module}/create", "module.create")]
    public function create($module_name)
    {
        $module = $this->getModule($module_name);
        $module->create();
    }

    /**
     * Edit and existing module item (view)
     */
    #[Get("/admin/{module}/{item}/edit", "module.edit")]
    public function edit($module_name, $item)
    {
        $module = $this->getModule($module_name);
        $module->edit($item);
    }

    /**
     * Store a new module item in the database
     */
    #[Post("/admin/{module}", "module.store")]
    public function store()
    {
    }

    /**
     * Update an existing module item in the database
     */
    #[Patch("/admin/{module}/{item}", "module.update")]
    public function update()
    {
    }

    /**
     * Destroy an existing module item in the database
     */
    #[Delete("/admin/{module}/{item}", "module.destroy")]
    public function destroy()
    {
    }
}
