<?php

namespace app\models;

use app\core\Model;
use app\util\CurrentUser;
use app\util\JwtService;
use Throwable;

class ModelMiddleware extends Model
{
    public function checkAccess(array &$output): bool
    {
        $token = $this->getBearerToken();

        try {
            $userId = JwtService::getUserId($token);
        } catch (Throwable $e) {
            $output = [
                "status" => "Error",
                "message" => "Unauthorized",
            ];
            return false;
        }

        if ($userId === null) {
            $output = [
                "status" => "Error",
                "message" => "Unauthorized",
            ];
            return false;
        }

        CurrentUser::setUserId($userId);
        return true;
    }

    private function getBearerToken(): ?string
    {
        $headers = $this->getAuthorizationHeader();

        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    private function getAuthorizationHeader(): ?string
    {
        $headers = null;

        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }

        return $headers;
    }
}
