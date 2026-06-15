<?php

namespace App\Dto\Order;

use App\Entity\Order;

readonly class OrderDetailResponseDto
{
    public function __construct(
        public int $id,
        public float $total,
        public string $status,
        public string $createdAt,
        public CustomerResponseDto $customer,
        /** @var OrderItemResponseDto[] */
        public array $items,
    ) {}

    public static function fromEntity(Order $order): self
    {
        return new self(
            id: $order->getId(),
            total: $order->getTotalAmount() / 100,
            status: $order->getStatus()->value,
            createdAt: $order->getCreatedAt()->format('Y-m-d H:i:s'),
            customer: CustomerResponseDto::fromEntity($order->getCustomer()),
            items: array_map(
                fn($item) => OrderItemResponseDto::fromEntity($item),
                $order->getOrderItems()->toArray()
            ),
        );
    }
}
