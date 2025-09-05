<?php

namespace app\util;

abstract class CurrentUser
{
    private static ?int $user_id = null;

    public static function getUserId(): ?int
    {
        return self::$user_id;
    }

    public static function setUserId(?int $user_id): void
    {
        self::$user_id = $user_id;
    }
}