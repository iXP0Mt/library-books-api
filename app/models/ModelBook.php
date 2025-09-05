<?php

namespace app\models;

use app\core\Database;
use app\core\Model;
use app\dto\BookDTO;
use app\util\CurrentUser;
use PDOException;

class ModelBook extends Model
{
    public function validUserBooks(array &$output): bool
    {
        $ownerUserId = CurrentUser::getUserId();

        if($ownerUserId === null) {
            $output = [
                "status" => "Error",
                "message" => "Server error"
            ];
            return false;
        }

        return true;
    }

    public function getUserBooks(array &$output): bool
    {
        try {
            $userBooks = Database::selectBooksByOwner(CurrentUser::getUserId());
        } catch (PDOException $e) {
            error_log("ERROR: " . $e->getMessage());
            $output = [
                "status" => "Error",
                "message" => "Server error"
            ];
            return false;
        }

        $output["books"] = array_map(fn(BookDTO $book) => [
            "book_id" => $book->bookId,
            "title" => $book->title,
        ], $userBooks);

        return true;
    }
}