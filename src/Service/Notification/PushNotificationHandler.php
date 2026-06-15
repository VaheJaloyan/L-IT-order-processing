<?php

namespace App\Service\Notification;

use App\Notification\NotificationEvent;
use App\Notification\NotificationEventType;
use App\Service\Notification\Contract\NotificationHandlerInterface;
use Psr\Log\LoggerInterface;

final class PushNotificationHandler implements NotificationHandlerInterface
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
        $this->logger->info('Push notification sent'.time(), $event->payload);
    }
}
