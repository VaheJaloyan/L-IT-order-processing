<?php

namespace App\Dto\Order;

use App\Entity\User;

readonly class CustomerResponseDto
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
    ) {}

    public static function fromEntity(User $user): self
    {
        return new self(
            id: $user->getId(),
            name: $user->getName(),
            email: $user->getEmail(),
        );
    }
}
