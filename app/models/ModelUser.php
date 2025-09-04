<?php

namespace app\models;

use app\core\Database;
use app\core\Model;
use app\dto\UserDTO;
use PDOException;

class ModelUser extends Model
{
    public function getListUsers(array &$output): bool
    {
        try {
            $users = Database::selectUsers();
        } catch (PDOException $e) {
            error_log("ERROR: " . $e->getMessage());
            $output = [
                "status" => "Error",
                "message" => "Server error"
            ];
            return false;
        }

        $output = array_map(
            fn(UserDTO $user) => [
                "user_id" => $user->id,
                "login" => $user->login
            ], $users
        );
        return true;
    }
}