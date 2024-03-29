<?php

namespace Celestial\Controllers\Admin;

use Celestial\Models\User;
use Constellation\Authentication\Auth;
use Constellation\Controller\Controller as BaseController;
use Constellation\Routing\{Get, Post, Router};
use Constellation\Validation\Validate;
use Exception;

class AuthController extends BaseController
{
    public $redirect_route = "admin.index";

    /**
     * Views
     */
    #[Get("/admin/sign-in", "auth.sign-in")]
    public function sign_in()
    {
        return $this->render("admin/auth/sign-in.html");
    }

    #[Get("/admin/sign-out", "auth.sign-out")]
    public function sign_out()
    {
        Auth::signOut();
        header("Location: /");
        exit();
    }

    #[Get("/admin/register", "auth.register")]
    public function register()
    {
        return $this->render("admin/auth/register.html", ['enabled' => $_ENV['REGISTER_ENABLED'] == 'true']);
    }

    //#[Get("/admin/forgot-password", "auth.forgot-password")]
    //public function forgot_password()
    //{
    //    return $this->render("admin/auth/forgot-password.html");
    //}

    //#[Get("/admin/reset-password", "auth.reset-password")]
    //public function reset_password()
    //{
    //    return $this->render("admin/auth/reset-password.html");
    //}

    /**
     * Requests
     */
    #[Post("/admin/sign-in", "auth.sign-in-post")]
    public function sign_in_post()
    {
        $data = $this->validateRequest([
            "email" => ["required", "string", "email"],
            "password" => ["required", "string"],
        ]);
        if ($data) {
            $user = User::findByAttribute("email", $data->email);
            if ($user) {
                if (Auth::checkPassword($user, $data->password)) {
                    Auth::signIn($user);
                    $this->redirectHome();
                }
            }
            Validate::$errors["password"][] = "bad email or password";
        }
        return $this->sign_in();
    }

    #[Post("/admin/register", "auth.register-post")]
    public function register_post()
    {
        if ($_ENV['REGISTER_ENABLED'] != 'true') return $this->register();
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
                    "this email address is already associated with another user";
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
        $route = Router::findRoute($this->redirect_route);
        if ($route) {
            $uri = $route->getUri();
            header("Location: $uri");
            exit();
        }
        throw new Exception("Route not configured");
    }
}
