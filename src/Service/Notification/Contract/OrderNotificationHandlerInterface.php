<?php

namespace App\Service\Notification\Contract;

use App\Entity\Order;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.order_notification_handler')]
interface OrderNotificationHandlerInterface
{
    public function handle(Order $order): void;
}
