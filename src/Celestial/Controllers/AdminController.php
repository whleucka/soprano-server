<?php

namespace Celestial\Controllers;

use Constellation\Controller\Controller;
use Constellation\Routing\{Get, Post, Delete, Patch};

class AdminController extends Controller
{
    #[Get("/admin/{module}", "module.index")]
    public function index()
    {
    }

    #[Get("/admin/{module}/create", "module.create")]
    public function create()
    {
    }

    #[Post("/admin/{module}", "module.store")]
    public function store()
    {
    }

    #[Get("/admin/{module}/{item}/edit", "module.edit")]
    public function edit()
    {
    }

    #[Patch("/admin/{module}/{item}", "module.update")]
    public function update()
    {
    }

    #[Delete("/admin/{module}/{item}", "module.destroy")]
    public function destroy()
    {
    }
}
