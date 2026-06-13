<?php

namespace App\Service\Notification\Contract;

use App\Entity\Order;

interface OrderNotificationHandlerInterface
{
    public function handle(Order $order): void;
}
