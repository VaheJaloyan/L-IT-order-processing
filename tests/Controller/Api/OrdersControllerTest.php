<?php

namespace App\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class OrdersControllerTest extends WebTestCase
{
    public function testStore(): void
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/orders',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'product' => 'book',
                'quantity' => 1,
            ])
        );

        self::assertResponseIsSuccessful();
    }
}
