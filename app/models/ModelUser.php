<?php

namespace app\models;

use app\core\Database;
use app\core\Model;
use app\dto\UserDTO;
use app\util\CurrentUser;
use app\util\ShareResult;
use PDOException;

class ModelUser extends Model
{
    public function shareValid($input, array &$output): ?bool
    {
        if(filter_var($input, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
            $output = [
                "status" => "Error",
                "message" => "Incorrect input data"
            ];
            return false;
        }


        $ownerUserId = CurrentUser::getUserId();

        if($ownerUserId === null) {
            $output = [
                "status" => "Error",
                "message" => "Server error"
            ];
            return null;
        }

        if($ownerUserId == $input) {
            $output = [
                "status" => "Error",
                "message" => "Incorrect input data"
            ];
            return false;
        }

        return true;
    }

    public function shareAccessToLibrary(int $granteeUserId, array &$output): ShareResult
    {
        try {
            $isSuccess = Database::insertShare(CurrentUser::getUserId(), $granteeUserId);
        } catch (PDOException $e) {
            error_log("ERROR: " . $e->getMessage());
            $output = [
                "status" => "Error",
                "message" => "Server error"
            ];
            return ShareResult::SERVER_ERROR;
        }

        if($isSuccess === null) {
            $output = [
                "status" => "Error",
                "message" => "Incorrect input data"
            ];
            return ShareResult::INVALID_USER;
        }

        if(!$isSuccess) {
            $output = [
                "status" => "Error",
                "message" => "Access already granted"
            ];
            return ShareResult::ALREADY_GRANTED;
        }

        $output = [
            "status" => "OK",
            "message" => "Access granted"
        ];
        return ShareResult::GRANTED;
    }

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