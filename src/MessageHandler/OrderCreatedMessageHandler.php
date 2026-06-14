<?php

namespace App\MessageHandler;

use App\Message\OrderCreatedMessage;
use App\Notification\NotificationEvent;
use App\Notification\NotificationEventType;
use App\Repository\OrderRepository;
use App\Service\Notification\Notifier;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Handles the OrderCreatedMessage by sending notifications for the newly created order.
 */
#[AsMessageHandler]
class OrderCreatedMessageHandler
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly Notifier $notifier
    ) {
    }

    /**
     * Fetches the order by ID and triggers all configured notifiers.
     * Silently returns if the order no longer exists.
     *
     * @param OrderCreatedMessage $message
     */
    public function __invoke(OrderCreatedMessage $message): void
    {
        $order = $this->orderRepository->find($message->orderId);

        if ($order === null) {
            return;
        }

        $this->notifier->notify(new NotificationEvent(NotificationEventType::OrderCreated, [
            'order_id' => $order->getId(),
            'customer' => $order->getCustomer()->getEmail(),
        ]));
    }
}