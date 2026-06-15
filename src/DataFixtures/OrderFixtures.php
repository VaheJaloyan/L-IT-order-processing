<?php

namespace App\DataFixtures;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\User;
use App\Enum\OrderStatusEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class OrderFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $john = $this->getReference(UserFixtures::REF_JOHN, User::class);

        // Pre-existing order
        // Allows GET /api/orders/{id} to be tested immediately without creating an order first
        $order = new Order();
        $order->setCustomer($john);
        $order->setStatus(OrderStatusEnum::created);

        // BOOK-001 × 2 × 15.50 = 3100 cents
        $book = new OrderItem();
        $book->setProductCode('BOOK-001');
        $book->setQuantity(2);
        $book->setUnitPrice(1550);
        $book->setSubtotal(3100);
        $manager->persist($book);

        // PEN-001 × 3 × 5.00 = 1500 cents
        $pen = new OrderItem();
        $pen->setProductCode('PEN-001');
        $pen->setQuantity(3);
        $pen->setUnitPrice(500);
        $pen->setSubtotal(1500);
        $manager->persist($pen);

        $order->addOrderItem($book);
        $order->addOrderItem($pen);

        $manager->persist($order);
        $manager->flush();
    }
}
