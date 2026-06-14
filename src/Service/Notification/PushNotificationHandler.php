<?php

namespace App\Service\Notification;

use App\Notification\NotificationEvent;
use App\Notification\NotificationEventType;
use App\Service\Notification\Contract\NotificationHandlerInterface;
use Psr\Log\LoggerInterface;

class PushNotificationHandler implements NotificationHandlerInterface
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    public function supports(NotificationEvent $event): bool
    {
        return in_array($event->type, [NotificationEventType::OrderCreated], true);
    }

    public function handle(NotificationEvent $event): void
    {
        // mocked — just log it
        $this->logger->info('Push notification sent'.time(), $event->payload);
    }
}
