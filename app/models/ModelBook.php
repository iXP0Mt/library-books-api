<?php

namespace app\models;

use app\core\Database;
use app\core\Model;
use app\dto\BookDTO;
use app\util\Constants;
use app\util\CurrentUser;
use Exception;
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

    public function validateCreateBook($phpInput, array &$output): ?array
    {
        if($this->validCreateBookAsJSON($phpInput)) {
            $data = [
                "title" => $phpInput['title'],
                "text" => $phpInput['text']
            ];
        } else if($this->validCreateBookAsFormData()) {
            if($_FILES['text']['error'] !== UPLOAD_ERR_OK) {
                return [
                    "status" => "Error",
                    "message" => match($_FILES['text']['error']) {
                        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => "File too large",
                        UPLOAD_ERR_NO_FILE => "No file was uploaded",
                        default => "Unknown error"
                    }
                ];
            }

            $data = [
                "title" => $_POST['title'],
                "text_url" => $_FILES['text']['tmp_name']
            ];
        } else {
            $output = [
                "status" => "Error",
                "message" => "Incorrect input data"
            ];
            return null;
        }

        if(strlen($data['title']) > Constants::MAX_BOOK_TITLE_LENGTH) {
            $output = [
                "status" => "Error",
                "message" => "Title too long"
            ];
            return null;
        }

        return $data;
    }

    public function createBook(array $data, array &$output): ?true
    {
        try {
            $newBook = new BookDTO(
                title: $data["title"],
                ownerUserId: CurrentUser::getUserId(),
                text: $this->getTextForCreateBook($data) ?? throw new Exception("Failed to extract text"),
            );

            Database::insertBook($newBook);
        } catch (Exception $e) {
            error_log("ERROR: " . $e->getMessage());
            $output = [
                "status" => "Error",
                "message" => "Server error"
            ];
            return null;
        }

        $output = [
            "status" => "OK",
            "message" => "Book created"
        ];
        return true;
    }

    private function validCreateBookAsJSON($phpInput): bool
    {
        if(!is_array($phpInput)) {
            return false;
        }

        if(
            !isset($phpInput["title"]) ||
            !isset($phpInput["text"])
        ) {
            return false;
        }

        $title = trim($phpInput["title"]);
        $text = trim($phpInput["text"]);

        if(
            empty($title) ||
            empty($text)
        ) {
            return false;
        }

        return true;
    }

    private function validCreateBookAsFormData(): bool
    {
        if(
            !isset($_POST["title"]) ||
            !isset($_FILES["text"])
        ) {
            return false;
        }

        $title = trim($_POST["title"]);

        if(
            empty($title)
        ) {
            return false;
        }

        return true;
    }

    private function getTextForCreateBook(array $data): ?string
    {
        if(isset($data['text_url'])) {
            $text = '';
            $filePointer = fopen($data['text_url'], 'r');
            if($filePointer) {
                while (!feof($filePointer)) {
                    $text .= fread($filePointer, 4096);
                }
                fclose($filePointer);
            }

            return $text;
        }
        else if(isset($data['text'])) {
            return $data['text'];
        }

        return null;
    }

    public function getBookInputValid($input, array &$output): bool
    {
        if(filter_var($input, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
            $output = [
                "status" => "Error",
                "message" => "Incorrect input data"
            ];
            return false;
        }

        return true;
    }

    public function getUsersBookById(int $bookId, array &$output): ?bool
    {
        try {
            $book = Database::selectUsersBookByBookId($bookId, CurrentUser::getUserId());
        } catch (PDOException $e) {
            error_log("ERROR: " . $e->getMessage());
            $output = [
                "status" => "Error",
                "message" => "Server error"
            ];
            return null;
        }

        if($book === false) {
            $output = [
                "status" => "Error",
                "message" => "Book not found"
            ];
            return false;
        }

        $output = [
            "title" => $book->title,
            "text" => $book->text,
        ];
        return true;
    }
}