<?php

namespace App\Notification;

/**
 * Central registry of all notification event types.
 * Add a new case here when introducing a new event — no other shared code needs to change.
 */
enum NotificationEventType: string
{
    case OrderCreated = 'order.created';
}
