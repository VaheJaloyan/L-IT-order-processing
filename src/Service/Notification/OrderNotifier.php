<?php

namespace App\Service\Notification;

use App\Entity\Order;
use App\Service\Notification\Contract\OrderNotificationHandlerInterface;

class OrderNotifier
{

    /**
     * @param OrderNotificationHandlerInterface[] $handlers
     */

    public function __construct(
        private readonly iterable $handlers,
    ) {}

    public function notify(Order $order): void
    {
        foreach ($this->handlers as $handler) {
            $handler->handle($order);
        }
    }
}
