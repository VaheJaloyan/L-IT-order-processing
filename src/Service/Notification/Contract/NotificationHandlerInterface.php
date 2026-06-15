<?php

namespace App\Service\Notification\Contract;

use App\Notification\NotificationEvent;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Implemented by every notification channel (email, push, SMS, etc.).
 * Tagged handlers are collected by Notifier and invoked when they support the event type.
 */
#[AutoconfigureTag('app.notification_handler')]
interface NotificationHandlerInterface
{
    /**
     * Sends a notification for the given event.
     *
     * @param NotificationEvent $event
     */
    public function handle(NotificationEvent $event): void;

    /**
     * Returns true if this handler should process the given event.
     *
     * @param NotificationEvent $event
     */
    public function supports(NotificationEvent $event): bool;
}