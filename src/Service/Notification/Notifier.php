<?php

namespace App\Service\Notification;

use App\Notification\NotificationEvent;
use App\Service\Notification\Contract\NotificationHandlerInterface;

/**
 * Dispatches a NotificationEvent to all registered handlers that support it.
 */
class Notifier
{
    /**
     * @param iterable<NotificationHandlerInterface> $handlers Tagged with app.notification_handler
     */
    public function __construct(
        private readonly iterable $handlers,
    ) {}

    /**
     * Iterates all handlers and invokes those that support the event.
     *
     * @param NotificationEvent $event
     */
    public function notify(NotificationEvent $event): void
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($event)) {
                $handler->handle($event);
            }
        }
    }
}