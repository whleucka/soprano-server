<?php

namespace Celestial\Controllers;

use Celestial\Models\User;
use Constellation\Authentication\Auth;
use Constellation\Controller\Controller as BaseController;
use Constellation\Routing\{Get, Post, Router};
use Constellation\Validation\Validate;
use Exception;

class AuthController extends BaseController
{
    public $home_route = "home.home";
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
        exit();
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
            "password" => ["required", "string"],
        ]);
        $fail = false;
        if ($data) {
            $user = User::findByAttribute("email", $data->email);
            if ($user) {
                if (Auth::checkPassword($user, $data->password)) {
                    Auth::signIn($user);
                    $this->redirectHome();
                } else {
                    $fail = true;
                }
            } else {
                $fail = true;
            }

            if ($fail) {
                Validate::$errors["password"][] = "bad email or password";
            }
        }
        return $this->sign_in();
    }

    #[Post("/register", "auth.register-post")]
    public function register_post()
    {
        $data = $this->validateRequest([
            "name" => ["required", "string"],
            "email" => ["required", "string", "email"],
            "password" => [
                "required",
                "string",
                "match",
                "min_length=8",
                "uppercase=1",
                "lowercase=1",
                "symbol=1",
            ],
        ]);
        if ($data) {
            $user = User::findByAttribute("email", $data->email);
            if ($user) {
                Validate::$errors["email"][] =
                    "this email is already associated with another user";
            } else {
                $registered = Auth::register([
                    "email" => $data->email,
                    "name" => $data->name,
                    "password" => $data->password,
                ]);
                if ($registered) {
                    $user = User::findByAttribute("email", $data->email);
                    Auth::signIn($user);
                    $this->redirectHome();
                }
            }
        }
        return $this->register();
    }

    private function redirectHome()
    {
        $route = Router::findRoute($this->home_route);
        if ($route) {
            $uri = $route->getUri();
            header("Location: $uri");
            exit();
        }
        throw new Exception("Home route not configured");
    }
}
