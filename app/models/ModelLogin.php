<?php

namespace app\models;

use app\core\Database;
use app\core\Model;
use app\util\Constants;
use app\util\JwtService;
use Firebase\JWT\JWT;
use PDOException;

class ModelLogin extends Model
{
    public function isValid(array $input, array &$output): bool
    {
        if(
            !isset($input['login']) ||
            !isset($input['password'])
        ) {
            $output = [
                "status" => "Error",
                "message" => "Incorrect input data"
            ];
            return false;
        }

        $login = trim($input['login']);
        $password = trim($input['password']);

        if(
            empty($login) ||
            empty($password)
        ) {
            $output = [
                "status" => "Error",
                "message" => "Incorrect input data"
            ];
            return false;
        }

        if(strlen($login) < Constants::MIN_LOGIN_LENGTH || strlen($login) > Constants::MAX_LOGIN_LENGTH) {
            $output =  [
                "status" => "Error",
                "message" => sprintf("Login must be between %d and %d characters", Constants::MIN_LOGIN_LENGTH, Constants::MAX_LOGIN_LENGTH)
            ];
            return false;
        }

        if(!preg_match("/^[a-zA-Z0-9]*$/", $login)) {
            $output =  [
                "status" => "Error",
                "message" => "Login must be contain only letters, numbers and underscores"
            ];
            return false;
        }

        if(strlen($password) < Constants::MIN_PASSWORD_LENGTH || strlen($password) > Constants::MAX_PASSWORD_LENGTH) {
            $output =  [
                "status" => "Error",
                "message" => sprintf("Password must be between %d and %d characters", Constants::MIN_PASSWORD_LENGTH, Constants::MAX_PASSWORD_LENGTH)
            ];
            return false;
        }


        return true;
    }

    public function login(array $input, array &$output): ?bool
    {
        try {
            $user = Database::selectUserByLogin($input['login']);
        } catch (PDOException $e) {
            error_log("ERROR: " . $e->getMessage());
            return null;
        }

        if($user === null) {
            $output = [
                "status" => "Error",
                "message" => "Login or password is incorrect"
            ];
            return false;
        }

        if(!password_verify($input['password'], $user->hashPassword)) {
            $output = [
                "status" => "Error",
                "message" => "Login or password is incorrect"
            ];
            return false;
        }

        $output = [
            "status" => "OK",
            "token" => JwtService::generateToken($user->id),
            "user_info" => [
                "user_id" => $user->id,
            ]
        ];
        return true;
    }
}