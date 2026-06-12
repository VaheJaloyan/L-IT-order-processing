<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateOrderDto
{
    public function __construct(
        #[Assert\NotBlank]
        public readonly string $productName,
        #[Assert\GreaterThan(0)]
        public readonly int $quantity,
    ) {
    }
}
