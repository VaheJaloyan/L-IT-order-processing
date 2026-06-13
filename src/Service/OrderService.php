<?php

namespace App\Service;

use App\Dto\Order\CreateOrderDto;
use App\Dto\Order\OrderItemDataDto;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Enum\OrderStatusEnum;
use App\Message\OrderCreatedMessage;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class OrderService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly MessageBusInterface $messageBus,
    ) {}

    public function createOrder(CreateOrderDto $data): Order
    {
        $order = $this->entityManager->wrapInTransaction(function () use ($data): Order {
            $user = $this->userRepository->findOrCreate($data->customer);
            $this->entityManager->persist($user);

            $order = new Order();
            $order->setCustomer($user);
            $order->setStatus(OrderStatusEnum::created);
            $this->entityManager->persist($order);

            foreach ($data->items as $item) {
                $order->addOrderItem($this->createOrderItem($order, $item));
            }
            $this->entityManager->flush();

            return $order;
        });

        $this->messageBus->dispatch(new OrderCreatedMessage($order->getId()));

        return $order;
    }

    private function createOrderItem(Order $order, OrderItemDataDto $itemData): OrderItem
    {
        // convert float price to cents — do it once here, never elsewhere
        $unitPriceInCents = (int) round($itemData->price * 100);
        $subtotal = $unitPriceInCents * $itemData->quantity;

        $item = new OrderItem();
        $item->setOrder($order);
        $item->setProductCode($itemData->productCode);
        $item->setQuantity($itemData->quantity);
        $item->setUnitPrice($unitPriceInCents);
        $item->setSubtotal($subtotal);

        return $item;
    }


}
