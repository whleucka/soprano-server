<?php

namespace Constellation\Tests\Routing\Controllers;

use Constellation\Routing\Get;
use Constellation\Controller\Controller;

class BasicController extends Controller
{
    #[Get("/basic", "basic.index")]
    public function index()
    {
        return "Hello, world";
    }

    #[Get("/basic/{collision}", "basic.collision")]
    public function collision()
    {
        return "It'sa me, Mario";
    }

    #[Get("/basic/{collision}/{here}", "basic.collision-here")]
    public function collision_here()
    {
        return 42;
    }

    #[Get("/basic/{collision}/{here}/{there}", "basic.collision-here")]
    public function collision_there()
    {
        return 420;
    }

    #[Get("/other", "basic.other")]
    public function other()
    {
        return "Hello, again";
    }

    #[Get("/test/{name}/{?age}", "test.name-age", ["test"])]
    public function index1($name, $age)
    {
        return "Hello, {$name}. You're {$age}.";
    }
}
