<?php

namespace app\core;

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
}