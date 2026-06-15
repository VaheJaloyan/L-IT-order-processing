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
#[ORM\HasLifecycleCallbacks]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $totalAmount = null;

    #[ORM\Column(enumType: OrderStatusEnum::class)]
    private ?OrderStatusEnum $status = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

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
        $this->totalAmount = 0;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    /** @return ?int */
    public function getId(): ?int
    {
        return $this->id;
    }

    /** @return ?int Total order amount in the smallest currency unit */
    public function getTotalAmount(): ?int
    {
        return $this->totalAmount;
    }

    /** @param int $totalAmount Total amount in the smallest currency unit */
    public function setTotalAmount(int $totalAmount): static
    {
        $this->totalAmount = $totalAmount;

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
        return $this->createdAt;
    }

    /** @param \DateTimeImmutable $createdAt */
    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /** @return ?\DateTimeImmutable */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /** @param \DateTimeImmutable $updatedAt */
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /** @return ?\DateTimeImmutable Null if the order has not been soft-deleted */
    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    /** @param ?\DateTimeImmutable $deletedAt Pass null to restore a soft-deleted order */
    public function setDeletedAt(?\DateTimeImmutable $deletedAt): static
    {
        $this->deletedAt = $deletedAt;

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
            $this->totalAmount += $orderItem->getSubtotal();
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
        $this->updatedAt = new \DateTimeImmutable();
    }
}
