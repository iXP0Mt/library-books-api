<?php

namespace app\models;

use app\core\Database;
use app\core\Model;
use app\util\JwtService;
use Firebase\JWT\JWT;
use PDOException;

class ModelRegistration extends Model
{
    public function isValid($data, array &$output): bool
    {
        if (!is_array($data)) {
            $output = [
                "status" => "Error",
                "message" => "Incorrect input data"
            ];
            return false;
        }

        if (
            !isset($data['login']) ||
            !isset($data['password']) ||
            !isset($data['password_confirm'])
        ) {
            $output = [
                "status" => "Error",
                "message" => "Incorrect input data"
            ];
            return false;
        }

        $login = trim($data['login']);
        $password = trim($data['password']);
        $password_confirm = trim($data['password_confirm']);

        if (
            empty($login) ||
            empty($password) ||
            empty($password_confirm)
        ) {
            $output = [
                "status" => "Error",
                "message" => "Incorrect input data"
            ];
            return false;
        }

        if (strlen($login) < 3 || strlen($login) > 64) {
            $output =  [
                "status" => "Error",
                "message" => "Login must be between 3 and 64 characters"
            ];
            return false;
        }

        if (!preg_match("/^[a-zA-Z0-9]*$/", $login)) {
            $output =  [
                "status" => "Error",
                "message" => "Login must be contain only letters, numbers and underscores"
            ];
            return false;
        }

        if (strlen($password) < 8 || strlen($password) > 64) {
            $output =  [
                "status" => "Error",
                "message" => "Password must be between 8 and 64 characters"
            ];
            return false;
        }

        if ($password !== $password_confirm) {
            $output =  [
                "status" => "Error",
                "message" => "Passwords do not match"
            ];
            return false;
        }

        return true;
    }

    public function registration(array $data, array &$output): ?bool
    {
        $hashPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        try {
            $result = Database::insertUser($data['login'], $hashPassword);
        } catch (PDOException $e) {
            error_log("ERROR: " . $e->getMessage());
            $output = [
                "status" => "Error",
                "message" => "Server error"
            ];
            return null;
        }

        if ($result === 0) {
            $output = [
                "status" => "Error",
                "message" => "Login already taken"
            ];
            return false;
        }


        $output = [
            "status" => "OK",
            "token" => JwtService::generateToken($result),
            "user_info" => [
                "user_id" => $result
            ]
        ];
        return true;
    }
}
