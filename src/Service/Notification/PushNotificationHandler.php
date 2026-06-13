<?php

namespace App\Service\Notification;

use App\Entity\Order;
use Psr\Log\LoggerInterface;

class PushNotificationHandler
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    public function handle(Order $order): void
    {
        // mocked — just log it
        $this->logger->info('Push notification sent', [
            'order_id' => $order->getId(),
            'customer' => $order->getCustomer()->getEmail(),
        ]);
    }
}
