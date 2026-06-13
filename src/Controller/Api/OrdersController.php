<?php

namespace App\Controller\Api;

use App\Dto\ErrorResponseDto;
use App\Dto\Order\CreateOrderDto;
use App\Dto\Order\OrderDetailResponseDto;
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
    ) {}

    #[Route('/api/orders', name: 'app_api_orders_create', methods: ['POST'])]
    public function create(#[MapRequestPayload] CreateOrderDto $dto): JsonResponse
    {
        $order = $this->orderService->createOrder($dto);

        return $this->json(OrderDetailResponseDto::fromEntity($order), 201);
    }

    #[Route('/api/orders/{id}', name: 'app_api_orders_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $order = $this->orderRepository->findWithDetails($id);

        if ($order === null) {
            return $this->json(new ErrorResponseDto('Order not found'), 404);
        }

        return $this->json(OrderDetailResponseDto::fromEntity($order));
    }
}
