<?php

namespace app\core;

use app\dto\BookDTO;
use app\dto\UserDTO;
use PDO;
use PDOException;

class Database
{
    public static function pdo(): PDO
    {
        static $pdo;
        if(!$pdo)
        {
            $dsn = 'mysql:dbname='.$_ENV['DB_NAME'].';host='.$_ENV['DB_HOST'].';charset=utf8';
            $pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return $pdo;
    }

    public static function insertUser(string $login, $hashPassword): int
    {
        try {
            $stmt = self::pdo()->prepare('INSERT INTO users (login, password) VALUES (:login,:password);');
            $stmt->bindValue(':login', $login);
            $stmt->bindValue(':password', $hashPassword);
            $stmt->execute();

            return (int)self::pdo()->lastInsertId();

        } catch (PDOException $e) {
            if($e->errorInfo[1] == 1062) {
                return 0;
            }

            throw $e;
        }
    }

    public static function selectUserByLogin(string $login): ?UserDTO
    {
        $stmt = self::pdo()->prepare('SELECT user_id, login, password FROM users WHERE login = :login');
        $stmt->bindValue(':login', $login);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row === false) {
            return null;
        }

        return new UserDTO($row['user_id'], $row['login'], $row['password']);
    }

    /**
     * @return UserDTO[]
     */
    public static function selectUsers(): array
    {
        $stmt = self::pdo()->query('SELECT user_id, login FROM users ORDER BY user_id');

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $users = [];
        foreach ($rows as $row) {
            $users[] = new UserDTO($row['user_id'], $row['login'], "");
        }

        return $users;
    }

    public static function insertShare(int $ownerUserId, int $granteeUserId): ?bool
    {
        try {
            $stmt = self::pdo()->prepare('INSERT INTO shares (owner_user_id, grantee_user_id) VALUES (:owner_user_id, :grantee_user_id);');
            $stmt->bindValue(':owner_user_id', $ownerUserId, PDO::PARAM_INT);
            $stmt->bindValue(':grantee_user_id', $granteeUserId, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            if(self::isDuplicateEntry($e)) {
                return false;
            } else if(self::isConstraintFailure($e)) {
                return null;
            }
            throw $e;
        }

        return true;
    }

    /**
     * @param int $ownerUserId
     * @return BookDTO[]
     */
    public static function selectBooksByOwner(int $ownerUserId): array
    {
        $stmt = self::pdo()->prepare("SELECT book_id, title FROM books WHERE owner_user_id = :owner_user_id AND is_deleted = '0';");
        $stmt->bindValue(':owner_user_id', $ownerUserId);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $books = [];
        foreach ($rows as $row) {
            $books[] = new BookDTO($row['book_id'], $row['title']);
        }

        return $books;
    }

    public static function insertBook(BookDTO $book): true
    {
        $stmt = self::pdo()->prepare('INSERT INTO books (owner_user_id, title, text) VALUES (:owner_user_id, :title, :text);');
        $stmt->bindValue(':owner_user_id', $book->ownerUserId);
        $stmt->bindValue(':title', $book->title);
        $stmt->bindValue(':text', $book->text);
        $stmt->execute();

        return true;
    }

    public static function selectUsersBookByBookId(int $bookId, int $ownerUserId): BookDTO|false
    {
        $stmt = self::pdo()->prepare('
            SELECT b.title, b.text 
            FROM books b
            WHERE b.book_id = :book_id 
	            AND (
	                b.owner_user_id = :owner_user_id 
                        OR EXISTS(
                            SELECT 1 
                            FROM shares s
                            WHERE s.owner_user_id = b.owner_user_id
                                AND s.grantee_user_id
                        )
	                );
        ');
        $stmt->bindValue(':book_id', $bookId);
        $stmt->bindValue(':owner_user_id', $ownerUserId);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row === false) {
            return false;
        }

        return new BookDTO(
            title: $row['title'],
            text: $row['text'],
        );
    }

    private static function isDuplicateEntry(PDOException $e): bool
    {
        return $e->errorInfo[1] == 1062;
    }

    private static function isConstraintFailure(PDOException $e): bool
    {
        return $e->errorInfo[1] == 1452;
    }
}