<?php

namespace App\Service\Notification;

use App\Entity\Order;
use App\Service\Notification\Contract\OrderNotificationHandlerInterface;
use Psr\Log\LoggerInterface;

class EmailNotificationHandler implements OrderNotificationHandlerInterface
{

    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    public function handle(Order $order): void
    {
        // mocked — just log it
        $this->logger->info('Email notification sent', [
            'order_id' => $order->getId(),
            'customer' => $order->getCustomer()->getEmail(),
        ]);
    }
}
