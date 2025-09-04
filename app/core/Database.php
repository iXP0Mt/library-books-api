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
            $config = require 'config/database.php';
            $dsn = 'mysql:dbname='.$config['db_name'].';host='.$config['db_host'].';charset=utf8';
            try {
                $pdo = new PDO($dsn, $config['db_user'], $config['db_pass']);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                error_log("ERROR: " . $e->getMessage());
            }
        }
        return $pdo;
    }

    public static function insertUser(string $login, $hashPassword): ?int
    {
        try {
            $stmt = self::pdo()->prepare('INSERT INTO users (login, password) VALUES (:login,:password);');
            $stmt->bindValue(':login', $login);
            $stmt->bindValue(':password', $hashPassword);
            $stmt->execute();

            return self::pdo()->lastInsertId();

        } catch (PDOException $e) {
            if($e->errorInfo[1] == 1062) {
                return 0;
            }

            error_log("ERROR: " . $e->getMessage());
            return null;
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
}