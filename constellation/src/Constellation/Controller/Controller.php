<?php

namespace Constellation\Controller;

use Celestial\Models\User;
use Constellation\Authentication\Auth;
use Constellation\Http\Request;
use Constellation\Routing\Router;
use Twig\Environment;
use Constellation\Validation\Validate;
use Constellation\Database\DB;

/**
 * @class Controller
 */
class Controller
{
    public ?User $user;
    public ?DB $db;
    public function __construct(
        protected Environment $twig,
        protected Request $request
    ) {
        $this->user = Auth::user();
        $this->db = @DB::getInstance();
    }

    /**
     * @return string Body of twig template
     */
    public function render(
        string $template,
        ?array $data = [],
        $echo = false
    ): ?string {
        $payload = [
            ...$data,
            "project_name" => $_ENV["PROJECT_NAME"],
            "errors" => Validate::$errors,
            // Functions for twig templates
            "fn" => new class {
                public function buildRoute($name, ...$vars)
                {
                    return Router::buildRoute($name, ...$vars);
                }

                public function old($field)
                {
                    $request = Request::getInstance()->getData();
                    return $request[$field] ?? "";
                }
            },
        ];
        if ($echo) {
            echo $this->twig->render($template, $payload);
            return null;
        }
        return $this->twig->render($template, $payload);
    }

    public function validateRequest(array $request_rules)
    {
        return Validate::request($this->request->getData(), $request_rules);
    }
}
