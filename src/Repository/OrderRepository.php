<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * Finds an order by ID and loads its customer and order items.
     *
     * @param int $id
     *
     * @return Order|null Null if no order with the given ID exists
     */
    public function findWithDetails(int $id): ?Order
    {
        return $this->createQueryBuilder('o')
            ->leftJoin('o.customer', 'customer')
            ->addSelect('customer')
            ->leftJoin('o.orderItems', 'orderItems')
            ->addSelect('orderItems')
            ->where('o.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
