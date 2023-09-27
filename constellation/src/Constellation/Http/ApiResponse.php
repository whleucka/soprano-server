<?php

namespace Constellation\Http;
use Celestial\Config\Application;

/**
 * @class ApiResponse
 */
class ApiResponse implements IResponse
{
    private array $payload = [];

    public function __construct(private mixed $data)
    {
        if (isset($_SERVER["HTTP_ORIGIN"])) {
            $http_origin = $_SERVER["HTTP_ORIGIN"];
            $allowed_origins = Application::$allowed_origins["domains"];

            if (!empty($allowed_origins)) {
                if (in_array($http_origin, $allowed_origins)) {
                    header("Access-Control-Allow-Origin: {$http_origin}");
                }
            } else {
                header("Access-Control-Allow-Origin: *");
            }
        }
    }

    public function prepare(): void
    {
        $this->payload["success"] =
            $this->data["success"] ?? http_response_code() === 200;
        $this->payload["code"] = $this->data["code"] ?? http_response_code();
        $this->payload["message"] = $this->data["message"] ?? "";
        $this->payload["ts"] = time();
        $this->payload["date"] = date("Y-m-d H:i:s");
        $this->payload["data"] = $this->data["payload"] ?? null;
    }

    public function execute(): never
    {
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode($this->payload, JSON_PRETTY_PRINT);
        exit();
    }
}
