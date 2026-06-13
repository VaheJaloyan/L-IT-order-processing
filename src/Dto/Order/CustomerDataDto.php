<?php

namespace App\Dto\Order;

use Symfony\Component\Validator\Constraints as Assert;

class CustomerDataDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'Customer Name is required')]
        #[Assert\Length(max: 255)]
        public readonly string $name,
        #[Assert\NotBlank(message: 'Customer email is required')]
        #[Assert\Email(message: 'Customer email is not valid')]
        #[Assert\Length(max: 255)]
        public readonly string $email,
    ) {
    }
}
