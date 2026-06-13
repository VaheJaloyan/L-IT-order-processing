<?php

namespace App\Entity;

use App\Enum\OrderStatusEnum;
use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: 'orders')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $total_amount = null;

    #[ORM\Column(enumType: OrderStatusEnum::class)]
    private ?OrderStatusEnum $status = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $deleted_at = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $customer = null;

    /**
     * @var Collection<int, OrderItem>
     */
    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'order', cascade: ['persist'], orphanRemoval: true)]
    private Collection $orderItems;

    /**
     * Initializes an empty order with a zero total and current timestamps.
     */
    public function __construct()
    {
        $this->orderItems = new ArrayCollection();
        $this->total_amount = 0;
        $this->created_at = new \DateTimeImmutable();
        $this->updated_at = new \DateTimeImmutable();
    }

    /** @return ?int */
    public function getId(): ?int
    {
        return $this->id;
    }

    /** @param string $id */
    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    /** @return ?int Total order amount in the smallest currency unit */
    public function getTotalAmount(): ?int
    {
        return $this->total_amount;
    }

    /** @param int $total_amount Total amount in the smallest currency unit */
    public function setTotalAmount(int $total_amount): static
    {
        $this->total_amount = $total_amount;

        return $this;
    }

    /** @return ?OrderStatusEnum */
    public function getStatus(): ?OrderStatusEnum
    {
        return $this->status;
    }

    /** @param OrderStatusEnum $status */
    public function setStatus(OrderStatusEnum $status): static
    {
        $this->status = $status;

        return $this;
    }

    /** @return ?string */
    public function getNotes(): ?string
    {
        return $this->notes;
    }

    /** @param ?string $notes */
    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    /** @return ?\DateTimeImmutable */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    /** @param \DateTimeImmutable $created_at */
    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    /** @return ?\DateTimeImmutable */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    /** @param \DateTimeImmutable $updated_at */
    public function setUpdatedAt(\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /** @return ?\DateTimeImmutable Null if the order has not been soft-deleted */
    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deleted_at;
    }

    /** @param ?\DateTimeImmutable $deleted_at Pass null to restore a soft-deleted order */
    public function setDeletedAt(?\DateTimeImmutable $deleted_at): static
    {
        $this->deleted_at = $deleted_at;

        return $this;
    }

    /** @return ?User */
    public function getCustomer(): ?User
    {
        return $this->customer;
    }

    /** @param ?User $customer */
    public function setCustomer(?User $customer): static
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    /**
     * Adds an item to the order and increments the total amount by the item's subtotal.
     * Has no effect if the item is already part of this order.
     *
     * @param OrderItem $orderItem The item to add
     */
    public function addOrderItem(OrderItem $orderItem): static
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems->add($orderItem);
            $orderItem->setOrder($this);
            $this->total_amount += $orderItem->getSubtotal();
        }

        return $this;
    }

    /** @param OrderItem $orderItem The item to remove */
    public function removeOrderItem(OrderItem $orderItem): static
    {
        if ($this->orderItems->removeElement($orderItem)) {
            if ($orderItem->getOrder() === $this) {
                $orderItem->setOrder(null);
            }
        }

        return $this;
    }

    /**
     * Doctrine PreUpdate lifecycle callback.
     * Refreshes `updated_at`.
     */
    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updated_at = new \DateTimeImmutable();
    }
}
