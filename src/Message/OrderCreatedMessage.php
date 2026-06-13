<?php

namespace App\Message;

class OrderCreatedMessage
{
    public function __construct(public readonly int $orderId) {}
}
