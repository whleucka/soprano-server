<?php

namespace Constellation\Authentication;

use Celestial\Models\User;
use Ramsey\Uuid\Uuid;

class Auth
{
    static ?User $user = null;

    public static function isSignedIn()
    {
        return isset($_SESSION["user"]);
    }

    public static function generateUuid()
    {
        return Uuid::uuid4()->toString();
    }

    public static function user($refresh = false)
    {
        if (self::isSignedIn()) {
            $user_id = isset($_SESSION["user"])
                ? intval($_SESSION["user"])
                : null;
            if (is_null(self::$user)) {
                self::$user = User::find([$user_id]);
            }
            if ($refresh) {
                self::$user->refresh();
            }
            return self::$user;
        }
        return null;
    }

    public static function checkPassword(User $user, string $password)
    {
        return password_verify($password, $user->password);
    }

    public static function hashPassword(string $password)
    {
        return password_hash($password, PASSWORD_ARGON2I);
    }

    public static function signIn(User $user): void
    {
        $_SESSION["user"] = $user->id;
    }

    public static function signOut(): void
    {
        if (!empty($_SESSION)) {
            $_SESSION = [];
            session_destroy();
        }
    }

    public static function register(array $attributes)
    {
        $attributes["uuid"] = self::generateUuid();
        $attributes["password"] = self::hashPassword($attributes["password"]);
        $attributes["created_at"] = date("Y-m-d H:i:s");
        return User::create($attributes);
    }
}
