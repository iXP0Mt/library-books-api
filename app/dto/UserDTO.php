<?php

namespace app\dto;

readonly class UserDTO
{
    public function __construct(
        public int $id,
        public string $login,
        public string $hashPassword,
    ) {
    }
}
