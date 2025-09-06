<?php

namespace app\util;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    public static function generateToken(int $userId): string
    {
        $payload = [
            'sub' => $userId,
            "iat" => time(),
            "exp" => time() + 3600,
        ];

        return JWT::encode($payload, $_ENV["JWT_SECRET"], 'HS256');
    }

    public static function getUserId(string $token): ?int
    {
        $payload = [];
        if (!self::validToken($token, $payload)) {
            return null;
        }

        if (!isset($payload['sub'])) {
            return null;
        }

        return $payload['sub'];
    }

    private static function validToken(string $token, array &$payload): bool
    {
        $payload = self::decodeToken($token);

        if (!isset($payload['exp'])) {
            return false;
        }

        if ($payload['exp'] < time()) {
            return false;
        }

        return true;
    }

    private static function decodeToken(string $jwt): array
    {
        return (array)JWT::decode($jwt, new Key($_ENV["JWT_SECRET"], 'HS256'));
    }
}
