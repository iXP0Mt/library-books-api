<?php

namespace app\dto;

readonly class ExternalBookDTO extends BookDTO
{
    public function __construct(
        public string $externalBookId,
        ?int $bookId = null,
        ?string $title = null,
        ?int $ownerUserId = null,
        ?string $text = null
    ) {
        parent::__construct(
            $bookId,
            $title,
            $ownerUserId,
            $text
        );
    }
}
