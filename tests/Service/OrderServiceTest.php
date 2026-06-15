<?php

namespace App\Tests\Service;

use App\Dto\Order\CreateOrderDto;
use App\Dto\Order\CustomerDataDto;
use App\Dto\Order\OrderItemDataDto;
use App\Entity\User;
use App\Enum\OrderStatusEnum;
use App\Message\OrderCreatedMessage;
use App\Repository\UserRepository;
use App\Service\OrderService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(OrderService::class)]
class OrderServiceTest extends TestCase
{
    private Stub&EntityManagerInterface $entityManager;
    private Stub&UserRepository $userRepository;
    private Stub&MessageBusInterface $messageBus;
    private OrderService $orderService;

    protected function setUp(): void
    {
        $this->entityManager = $this->createStub(EntityManagerInterface::class);
        $this->userRepository = $this->createStub(UserRepository::class);
        $this->messageBus     = $this->createStub(MessageBusInterface::class);

        $this->entityManager
            ->method('wrapInTransaction')
            ->willReturnCallback(function (callable $fn) {
                $order = $fn();
                $ref = new \ReflectionProperty($order, 'id');
                $ref->setValue($order, 1);
                return $order;
            });

        $this->messageBus
            ->method('dispatch')
            ->willReturnCallback(fn(object $msg) => new Envelope($msg));

        $this->orderService = new OrderService(
            $this->entityManager,
            $this->userRepository,
            $this->messageBus,
        );
    }

    public function testCreateOrderSetsStatusToCreated(): void
    {
        $this->userRepository->method('findOrCreate')->willReturn($this->makeUser());

        $order = $this->orderService->createOrder($this->makeDto());

        self::assertSame(OrderStatusEnum::created, $order->getStatus());
    }

    public function testCreateOrderAttachesCustomerToOrder(): void
    {
        $user = $this->makeUser();
        $this->userRepository->method('findOrCreate')->willReturn($user);

        $order = $this->orderService->createOrder($this->makeDto());

        self::assertSame($user, $order->getCustomer());
    }

    public function testCreateOrderCallsFindOrCreateWithCustomerData(): void
    {
        $dto = $this->makeDto();

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository
            ->expects(self::once())
            ->method('findOrCreate')
            ->with($dto->customer)
            ->willReturn($this->makeUser());

        (new OrderService($this->entityManager, $userRepository, $this->messageBus))
            ->createOrder($dto);
    }

    public function testCreateOrderAddsAllItemsToOrder(): void
    {
        $this->userRepository->method('findOrCreate')->willReturn($this->makeUser());

        $dto = new CreateOrderDto(
            customer: new CustomerDataDto(name: 'John', email: 'john@example.com'),
            items: [
                new OrderItemDataDto(productCode: 'A', quantity: 1, price: 10.00),
                new OrderItemDataDto(productCode: 'B', quantity: 2, price: 5.00),
                new OrderItemDataDto(productCode: 'C', quantity: 3, price: 2.00),
            ],
        );

        $order = $this->orderService->createOrder($dto);

        self::assertCount(3, $order->getOrderItems());
    }

    public function testCreateOrderConvertsItemPriceToCents(): void
    {
        $this->userRepository->method('findOrCreate')->willReturn($this->makeUser());

        $dto = new CreateOrderDto(
            customer: new CustomerDataDto(name: 'John', email: 'john@example.com'),
            items: [new OrderItemDataDto(productCode: 'BOOK-001', quantity: 1, price: 15.99)],
        );

        $order = $this->orderService->createOrder($dto);

        self::assertSame(1599, $order->getOrderItems()->first()->getUnitPrice());
    }

    public function testCreateOrderCalculatesSubtotalPerItem(): void
    {
        $this->userRepository->method('findOrCreate')->willReturn($this->makeUser());

        $dto = new CreateOrderDto(
            customer: new CustomerDataDto(name: 'John', email: 'john@example.com'),
            items: [new OrderItemDataDto(productCode: 'PEN-001', quantity: 3, price: 5.00)],
        );

        $order = $this->orderService->createOrder($dto);

        // 3 × 500 cents = 1500
        self::assertSame(1500, $order->getOrderItems()->first()->getSubtotal());
    }

    public function testCreateOrderAccumulatesTotalFromAllItems(): void
    {
        $this->userRepository->method('findOrCreate')->willReturn($this->makeUser());

        $dto = new CreateOrderDto(
            customer: new CustomerDataDto(name: 'John', email: 'john@example.com'),
            items: [
                new OrderItemDataDto(productCode: 'BOOK-001', quantity: 2, price: 15.50), // 3100 cents
                new OrderItemDataDto(productCode: 'PEN-001',  quantity: 3, price: 5.00),  // 1500 cents
            ],
        );

        $order = $this->orderService->createOrder($dto);

        self::assertSame(4600, $order->getTotalAmount());
    }

    public function testCreateOrderDispatchesOrderCreatedMessage(): void
    {
        $this->userRepository->method('findOrCreate')->willReturn($this->makeUser());

        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(OrderCreatedMessage::class))
            ->willReturnCallback(fn(object $msg) => new Envelope($msg));

        (new OrderService($this->entityManager, $this->userRepository, $messageBus))
            ->createOrder($this->makeDto());
    }

    // --- helpers ---

    private function makeUser(): User
    {
        $user = new User();
        $user->setName('John Doe');
        $user->setEmail('john@example.com');
        return $user;
    }

    private function makeDto(): CreateOrderDto
    {
        return new CreateOrderDto(
            customer: new CustomerDataDto(name: 'John Doe', email: 'john@example.com'),
            items: [new OrderItemDataDto(productCode: 'BOOK-001', quantity: 1, price: 10.00)],
        );
    }
}