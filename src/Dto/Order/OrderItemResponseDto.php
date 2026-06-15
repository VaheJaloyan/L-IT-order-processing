<?php

namespace App\Dto\Order;

use App\Entity\OrderItem;

readonly class OrderItemResponseDto
{
    public function __construct(
        public string $productCode,
        public int $quantity,
        public float $unitPrice,
        public float $subtotal,
    ) {}

    public static function fromEntity(OrderItem $item): self
    {
        return new self(
            productCode: $item->getProductCode(),
            quantity: $item->getQuantity(),
            unitPrice: $item->getUnitPrice() / 100,
            subtotal: $item->getSubtotal() / 100,
        );
    }
}
