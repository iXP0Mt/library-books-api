<?php

namespace app\util;

use Firebase\JWT\JWT;

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
}