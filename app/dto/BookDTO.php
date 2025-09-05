<?php

namespace app\dto;

readonly class BookDTO
{
    public function __construct(
        public int $bookId,
        public string $title,
    ) {}
}