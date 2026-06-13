<?php

namespace App\Dto\Order;

use Symfony\Component\Validator\Constraints as Assert;

class OrderItemDataDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'Product code is required')]
        #[Assert\Length(max: 100)]
        public readonly string $productCode,
        #[Assert\NotNull]
        #[Assert\Positive(message: 'Quantity must be greater than 0')]
        public readonly int $quantity,
        #[Assert\NotNull]
        #[Assert\PositiveOrZero(message: 'Price cannot be negative')]
        public readonly float $price,
    ) {
    }
}
