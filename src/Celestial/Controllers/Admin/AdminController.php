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
            $module = new $class;
            if (property_exists($module, 'name')) {
                $this->modules[$module->name] = $class;
            }
        }
    }

    private function getModule($name)
    {
        if (key_exists($name, $this->modules)) {
            return new $this->modules[$name];
        }
        // TODO make a nice module not found page
        header("HTTP/1.0 404 Not Found");
        echo "Module not found";
        die;
    }

    #[Get("/admin/{module}", "module.index")]
    /**
     * Show all module items (view)
     */
    public function index($module_name)
    {
        $module = $this->getModule($module_name);
        $module->index();
    }

    #[Get("/admin/{module}/create", "module.create")]
    /**
     * Create a new module item (view)
     */
    public function create($module_name)
    {
        $module = $this->getModule($module_name);
        $module->create();
    }

    #[Get("/admin/{module}/{item}/edit", "module.edit")]
    /**
     * Edit and existing module item (view)
     */
    public function edit($module_name, $item)
    {
        $module = $this->getModule($module_name);
        $module->edit($item);
    }

    #[Post("/admin/{module}", "module.store")]
    /**
     * Store a new module item in the database
     */
    public function store()
    {
    }

    #[Patch("/admin/{module}/{item}", "module.update")]
    /**
     * Update an existing module item in the database
     */
    public function update()
    {
    }

    #[Delete("/admin/{module}/{item}", "module.destroy")]
    /**
     * Destroy an existing module item in the database
     */
    public function destroy()
    {
    }
}
