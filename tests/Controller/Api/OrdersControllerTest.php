<?php

namespace App\Tests\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

final class OrdersControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $connection = $entityManager->getConnection();
        $connection->executeStatement('DROP SCHEMA public CASCADE');
        $connection->executeStatement('CREATE SCHEMA public');

        // Re-apply all migrations on the clean schema
        $application = new Application(static::$kernel);
        $application->setAutoExit(false);
        $application->run(new ArrayInput([
            'command' => 'doctrine:migrations:migrate',
            '--no-interaction' => true,
        ]), new NullOutput());
    }

    public function testCreateOrderReturnsCreatedResponse(): void
    {
        $this->client->request(
            'POST',
            '/api/orders',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'customer' => [
                    'email' => 'john@example.com',
                    'name'  => 'John Doe',
                ],
                'items' => [
                    ['productCode' => 'BOOK-001', 'quantity' => 2, 'price' => 15.50],
                    ['productCode' => 'PEN-001',  'quantity' => 3, 'price' => 5.00],
                ],
            ])
        );

        self::assertResponseStatusCodeSame(201);

        $data = json_decode($this->client->getResponse()->getContent(), true);

        self::assertArrayHasKey('id', $data);
        self::assertArrayHasKey('total', $data);
        self::assertArrayHasKey('status', $data);
        self::assertArrayHasKey('customer', $data);
        self::assertArrayHasKey('items', $data);

        self::assertSame(46.0, $data['total']);
        self::assertSame('created', $data['status']);

        self::assertSame('john@example.com', $data['customer']['email']);
        self::assertSame('John Doe', $data['customer']['name']);

        self::assertCount(2, $data['items']);
    }

    public function testCreateOrderReusesExistingCustomer(): void
    {
        $payload = json_encode([
            'customer' => ['email' => 'john@example.com', 'name' => 'John Doe'],
            'items'    => [['productCode' => 'BOOK-001', 'quantity' => 1, 'price' => 10.00]],
        ]);

        // First order — creates the customer
        $this->client->request('POST', '/api/orders', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);
        $first = json_decode($this->client->getResponse()->getContent(), true);

        // Second order with the same email — must reuse the same customer record
        $this->client->request('POST', '/api/orders', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);
        $second = json_decode($this->client->getResponse()->getContent(), true);

        // Both orders belong to the same customer id — no duplicate was created
        self::assertSame($first['customer']['id'], $second['customer']['id']);
    }

    public function testCreateOrderValidationFailsWhenItemsMissing(): void
    {
        $this->client->request(
            'POST',
            '/api/orders',
            [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'customer' => ['email' => 'john@example.com', 'name' => 'John Doe'],
                // "items" is intentionally missing
            ])
        );

        self::assertResponseStatusCodeSame(422);

        $data = json_decode($this->client->getResponse()->getContent(), true);

        self::assertArrayHasKey('errors', $data);
    }

    public function testCreateOrderValidationFailsWhenItemsArrayIsEmpty(): void
    {
        $this->client->request(
            'POST',
            '/api/orders',
            [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'customer' => ['email' => 'john@example.com', 'name' => 'John Doe'],
                'items'    => [],
            ])
        );

        self::assertResponseStatusCodeSame(422);

        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertArrayHasKey('errors', $data);
    }

    public function testCreateOrderValidationFailsWhenCustomerEmailInvalid(): void
    {
        $this->client->request(
            'POST',
            '/api/orders',
            [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'customer' => ['email' => 'not-an-email', 'name' => 'John Doe'],
                'items'    => [['productCode' => 'BOOK-001', 'quantity' => 1, 'price' => 10.00]],
            ])
        );

        self::assertResponseStatusCodeSame(422);

        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertArrayHasKey('errors', $data);
    }

    public function testGetOrderReturnsOrderWithCustomerAndItems(): void
    {
        // First create an order so we have an id to fetch
        $this->client->request(
            'POST', '/api/orders', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'customer' => ['email' => 'jane@example.com', 'name' => 'Jane Doe'],
                'items'    => [['productCode' => 'MUG-001', 'quantity' => 1, 'price' => 12.00]],
            ])
        );
        $created = json_decode($this->client->getResponse()->getContent(), true);

        // Now fetch it by id
        $this->client->request('GET', '/api/orders/' . $created['id']);

        self::assertResponseStatusCodeSame(200);

        $data = json_decode($this->client->getResponse()->getContent(), true);

        // Full detail response must include customer and items
        self::assertSame($created['id'], $data['id']);
        self::assertArrayHasKey('customer', $data);
        self::assertArrayHasKey('items', $data);
        self::assertSame('jane@example.com', $data['customer']['email']);
        self::assertCount(1, $data['items']);
        self::assertSame(12.0, $data['total']);
    }

    public function testGetOrderReturns404WhenNotFound(): void
    {
        $this->client->request('GET', '/api/orders/999999');

        self::assertResponseStatusCodeSame(404);

        $data = json_decode($this->client->getResponse()->getContent(), true);

        self::assertArrayHasKey('message', $data);
    }



    public function testCreateOrderValidationFailsWhenCustomerMissing(): void
    {
        $this->client->request(
            'POST',
            '/api/orders',
            [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'items' => [['productCode' => 'BOOK-001', 'quantity' => 1, 'price' => 10.00]],
            ])
        );

        self::assertResponseStatusCodeSame(422);

        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertArrayHasKey('errors', $data);
    }

    public function testCreateOrderRollsBackOnFailure(): void
    {
        // Force a failure by sending an item with a negative price which passes
        // basic validation but violates the PositiveOrZero constraint — confirming
        // the 422 is returned and no order is persisted
        $this->client->request(
            'POST',
            '/api/orders',
            [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'customer' => ['email' => 'john@example.com', 'name' => 'John Doe'],
                'items'    => [['productCode' => 'BOOK-001', 'quantity' => 1, 'price' => -5.00]],
            ])
        );

        self::assertResponseStatusCodeSame(422);

        // Confirm no order was created — the GET for any ID returns 404
        $this->client->request('GET', '/api/orders/1');
        self::assertResponseStatusCodeSame(404);
    }
}
