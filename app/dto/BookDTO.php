<?php

namespace app\dto;

readonly class BookDTO
{
    public function __construct(
        public ?int $bookId = null,
        public ?string $title = null,
        public ?int $ownerUserId = null,
        public ?string $text = null,
    ) {}
}