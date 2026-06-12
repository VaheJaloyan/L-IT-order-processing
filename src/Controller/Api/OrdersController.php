<?php

namespace App\Controller\Api;

use App\Dto\CreateOrderDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class OrdersController extends AbstractController
{
    #[Route('/api/orders', name: 'app_api_orders_create', methods: ['POST'])]
    public function create(#[MapRequestPayload] CreateOrderDto $dto): JsonResponse
    {
        return $this->json([
            'message' => 'Order created successfully',
            'data' => [
	            'product' => $dto->productName,
	            'quantity' => $dto->quantity
            ]
        ]);
    }

	#[Route('/api/orders/{id}', name: 'app_api_orders_show', methods: ['GET'])]
	public function show(string $order): JsonResponse
	{
		return $this->json($order);
	}
}
