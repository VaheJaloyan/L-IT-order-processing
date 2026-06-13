<?php

namespace App\MessageHandler;

use App\Message\OrderCreatedMessage;
use App\Repository\OrderRepository;
use App\Service\Notification\OrderNotifier;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class OrderCreatedMessageHandler
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly OrderNotifier $notifier
    ) {
    }

    public function __invoke(OrderCreatedMessage $message): void
    {
        $order = $this->orderRepository->find($message->orderId);

        if ($order === null) {
            return;
        }
        $this->notifier->notify($order);
    }
}
