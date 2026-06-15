<?php

namespace App\Dto;

readonly class ErrorResponseDto
{
    public function __construct(
        public string $message,
    ) {}
}
