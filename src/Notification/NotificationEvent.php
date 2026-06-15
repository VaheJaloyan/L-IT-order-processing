<?php

namespace App\Notification;

/**
 * Carries all data a notification channel handler needs for a single event.
 * Decouples handlers from any specific entity by passing only serializable scalars.
 */
class NotificationEvent
{
    /**
     * @param NotificationEventType $type    The event type
     * @param array<string, mixed>  $payload Arbitrary key→value data relevant to the event
     */
    public function __construct(
        public readonly NotificationEventType $type,
        public readonly array $payload,
    ) {}
}
