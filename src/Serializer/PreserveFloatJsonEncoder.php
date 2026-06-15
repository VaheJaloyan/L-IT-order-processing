<?php

namespace App\Serializer;

use Symfony\Component\Serializer\Encoder\JsonEncoder;

class PreserveFloatJsonEncoder extends JsonEncoder
{
    public function encode(mixed $data, string $format, array $context = []): string
    {
        $context['json_encode_options'] = ($context['json_encode_options'] ?? 0) | \JSON_PRESERVE_ZERO_FRACTION;

        return parent::encode($data, $format, $context);
    }
}
