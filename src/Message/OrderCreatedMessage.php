<?php

namespace App\Message;

/**
 * Message dispatched to the Symfony Messenger bus when a new order is successfully created.
 */
class OrderCreatedMessage
{
    /**
     * @param int $orderId ID of the newly created order
     */
    public function __construct(public readonly int $orderId) {}
}
