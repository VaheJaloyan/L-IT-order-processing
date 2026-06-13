<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateOrderDto
{
    public function __construct(
        #[Assert\NotNull]
        #[Assert\Valid]  // <-- tells Symfony to also validate the nested DTO
        public readonly CustomerDataDto $customer,
        #[Assert\NotNull]
        #[Assert\Count(min: 1, minMessage: 'Order must have at least one item')]
        #[Assert\Valid]  // <-- validates each ItemData in the array
            /** @var OrderItemDataDto[] */
        public readonly array $items,
    ) {
    }
}
