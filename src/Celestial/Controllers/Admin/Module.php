<?php

namespace Celestial\Controllers\Admin;

class Module
{
    public function index() {
        echo $this->name . PHP_EOL;
        echo "index()";
    }

    public function create() {
        echo $this->name . PHP_EOL;
        echo "create()";
    }

    public function edit($id) {
        echo $this->name . ' ' . $id . PHP_EOL;
        echo "edit()";
    }

    public function store() {
        echo "store()";
    }

    public function update() {
        echo "update()";
    }

    public function destroy() {
        echo "destroy()";
    }
}
