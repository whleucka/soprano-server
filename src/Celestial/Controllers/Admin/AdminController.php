<?php

namespace Celestial\Controllers\Admin;

use Constellation\Controller\Controller;
use Constellation\Routing\{Get, Post, Delete, Patch};
use Composer\Autoload\ClassMapGenerator;

class AdminController extends Controller
{
    private array $modules = [];
    public array $sidebar_links = [];
    private array $module_map = [];

    private function getModule($name)
    {
        if (!$this->module_map) {
            $this->module_map = ClassMapGenerator::createMap(
                __DIR__ . "/Modules"
            );
        }
        foreach ($this->module_map as $class => $path) {
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

    private function getSidebarLinks()
    {
        if (!$this->module_map) {
            $this->module_map = ClassMapGenerator::createMap(
                __DIR__ . "/Modules"
            );
        }
        foreach ($this->module_map as $class => $path) {
            $module = new $class();
            $this->sidebar_links[] = [
                "module" => $module->module,
                "title" => $module->title ?? $module->module,
                "uri" => "/admin/module/{$module->module}",
            ];
        }
        usort($this->sidebar_links, function ($one, $two) {
            return $one["title"] <=> $two["title"];
        });
    }

    #[Get("/admin", "admin.index", ["auth"])]
    public function admin()
    {
        $this->index("dashboard");
    }

    /**
     * Show all module items (view)
     */
    #[Get("/admin/module/{module}", "module.index", ["auth"])]
    public function index($module)
    {
        $module = $this->getModule($module);
        $this->getSidebarLinks();
        $module->index($this);
    }

    /**
     * Create a new module item (view)
     */
    #[Get("/admin/module/{module}/create", "module.create", ["auth"])]
    public function create($module)
    {
        $module = $this->getModule($module);
        $this->getSidebarLinks();
        $module->create($this);
    }

    /**
     * Edit and existing module item (view)
     */
    #[Get("/admin/module/{module}/{id}/edit", "module.edit", ["auth"])]
    public function edit($module, $id)
    {
        $module = $this->getModule($module);
        $this->getSidebarLinks();
        $module->edit($this, $id);
    }

    /**
     * Store a new module item in the database
     */
    #[Post("/admin/module/{module}/store", "module.store", ["auth"])]
    public function store($module)
    {
        $module = $this->getModule($module);
        $module->store($this);
    }

    /**
     * Update an existing module item in the database
     */
    #[Post("/admin/module/{module}/{id}/update", "module.update", ["auth"])]
    #[Patch("/admin/module/{module}/{id}/save", "module.save", ["auth"])]
    public function update($module, $id)
    {
        $module = $this->getModule($module);
        $module->update($this, $id);
    }

    /**
     * Destroy an existing module item in the database
     */
    #[Post("/admin/module/{module}/{id}/destroy", "module.destroy", ["auth"])]
    #[Delete("/admin/module/{module}/{id}/delete", "module.delete", ["auth"])]
    public function destroy($module, $id)
    {
        $module = $this->getModule($module);
        $module->destroy($this, $id);
    }
}
