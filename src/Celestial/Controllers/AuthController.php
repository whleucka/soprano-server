<?php

namespace Celestial\Controllers;

use Constellation\Authentication\Auth;
use Constellation\Controller\Controller as BaseController;
use Constellation\Routing\{Get,Post};

class AuthController extends BaseController
{
    /**
     * Views
     */
    #[Get("/sign-in", "auth.sign-in")]
    public function sign_in()
    {
        return $this->render("auth/sign-in.html");
    }

    #[Get("/sign-out", "auth.sign-out")]
    public function sign_out()
    {
        Auth::signOut();
        header("Location: /");
        exit;
    }

    #[Get("/register", "auth.register")]
    public function register()
    {
        return $this->render("auth/register.html");
    }

    #[Get("/forgot-password", "auth.forgot-password")]
    public function forgot_password()
    {
        return $this->render("auth/forgot-password.html");
    }

    #[Get("/reset-password", "auth.reset-password")]
    public function reset_password()
    {
        return $this->render("auth/reset-password.html");
    }

    /**
     * Requests
     */
    #[Post("/sign-in", "auth.sign-in-post")]
    public function sign_in_post()
    {
        $data = $this->validateRequest([
            "email" => ["required", "string", "email"],
            "password" => ["required", "string", "password"],
        ]);
        print_r($data);exit;
        return $this->sign_in();
    }

    #[Post("/register", "auth.register-post")]
    public function register_post()
    {
        die(sprintf("<pre>%s</pre>", "wip: register post"));
        return $this->register();
    }
}
