<?php

namespace App\Controller\Api;

use App\Dto\Order\CreateOrderDto;
use App\Repository\OrderRepository;
use App\Service\OrderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class OrdersController extends AbstractController
{

    public function __construct(
        private readonly OrderService $orderService,
        private readonly OrderRepository $orderRepository
    ) {
    }

    #[Route('/api/orders', name: 'app_api_orders_create', methods: ['POST'])]
    public function create(#[MapRequestPayload] CreateOrderDto $dto): JsonResponse
    {
        try {
            $order = $this->orderService->createOrder($dto);
        } catch (\Throwable $exception) {
            return $this->json([
                'message' => $exception->getMessage(),
            ], 500);
        }

        return $this->json([
            'id' => $order->getId(),
            'total' => $order->getTotalAmount() / 100, // cents → dollars
            'status' => $order->getStatus()->value,
        ], 201);
    }

    #[Route('/api/orders/{id}', name: 'app_api_orders_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $order = $this->orderRepository->findWithDetails($id);

        if ($order === null) {
            return $this->json(['message' => 'Order not found'], 404);
        }

        return $this->json([
            'id' => $order->getId(),
            'total' => $order->getTotalAmount() / 100,
            'status' => $order->getStatus()->value,
            'customer' => [
                'id' => $order->getCustomer()->getId(),
                'name' => $order->getCustomer()->getName(),
                'email' => $order->getCustomer()->getEmail(),
            ],
            'items' => array_map(
                fn($item) => [
                    'productCode' => $item->getProductCode(),
                    'quantity' => $item->getQuantity(),
                    'unitPrice' => $item->getUnitPrice() / 100,
                    'subtotal' => $item->getSubtotal() / 100,
                ],
                $order->getItems()->toArray()
            ),
            'createdAt' => $order->getCreatedAt()->format('Y-m-d H:i:s'),
        ]);
    }
}
