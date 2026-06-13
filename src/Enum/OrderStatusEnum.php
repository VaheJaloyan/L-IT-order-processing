<?php

namespace App\Enum;

use Doctrine\ORM\Mapping\MappingAttribute;

enum OrderStatusEnum: string
{
    case Created = 'Created';
    case confirmed = 'Confirmed';
    case completed = 'Completed';
    case canceled = 'Canceled';
    case failed = 'Failed';

}
