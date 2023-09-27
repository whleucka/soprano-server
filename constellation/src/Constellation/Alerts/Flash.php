<?php

namespace Constellation\Alerts;

class Flash
{
    private static $statuses = [
        "error",
        "database",
        "success",
        "warning",
        "info",
    ];

    public static function addFlash($status, $message)
    {
        if (in_array($status, self::$statuses)) {
            if (
                isset($_SESSION["flash"][$status]) &&
                in_array($message, $_SESSION["flash"][$status])
            ) {
                return;
            }
            $_SESSION["flash"][$status][] = $message;
        }
    }

    public static function hasFlash()
    {
        return isset($_SESSION["flash"]);
    }

    public static function hasStatus($status): bool
    {
        return isset($_SESSION["flash"][$status]);
    }

    public static function clearStatus($status)
    {
        unset($_SESSION["flash"][$status]);
    }

    public static function getSessionFlash()
    {
        $alerts = "";
        foreach (self::$statuses as $status) {
            if (isset($_SESSION["flash"][$status])) {
                foreach ($_SESSION["flash"][$status] as $key => $message) {
                    $alerts .= self::alert($status, $message);
                    unset($_SESSION["flash"][$status][$key]);
                }
            }
        }
        return $alerts;
    }

    public static function alert($status, $message)
    {
        $var = match ($status) {
            "error" => self::error($message),
            "success" => self::success($message),
            "warning" => self::warning($message),
            "database" => self::database($message),
            "info" => self::info($message),
        };
        return $var;
    }

    public static function error($message)
    {
        return "<div class='flash error'>
            <div><strong>&#9762;</strong></div><div>{$message}</div>
        </div>";
    }
    public static function success($message)
    {
        return "<div class='flash success'>
            <div><strong>&#10003;</strong></div><div>{$message}</div>
        </div>";
    }
    public static function warning($message)
    {
        return "<div class='flash warning'>
            <div><strong>&#9888;</strong></div><div>{$message}</div>
        </div>";
    }
    public static function database($message)
    {
        return "<div class='flash database'>
            <div><strong>&#9888;</strong></div><div>{$message}</div>
        </div>";
    }
    public static function info($message)
    {
        return "<div class='flash info'>
            <div><strong>&#128712;</strong></div><div>{$message}</div>
        </div>";
    }
}
