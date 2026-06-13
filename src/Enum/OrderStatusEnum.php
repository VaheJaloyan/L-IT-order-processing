<?php

namespace App\Enum;

enum OrderStatusEnum: string
{
    case created = 'created';
    case confirmed = 'confirmed';
    case completed = 'completed';
    case canceled = 'canceled';
    case failed = 'failed';
}
