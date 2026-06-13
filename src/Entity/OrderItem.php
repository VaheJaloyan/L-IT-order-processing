<?php

namespace App\Entity;

use App\Repository\OrderItemsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderItemsRepository::class)]
#[ORM\Table(name: 'order_items')]
class OrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $product_code = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column]
    private ?int $unit_price = null;

    #[ORM\Column]
    private ?int $subtotal = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column (nullable: true)]
    private ?\DateTimeImmutable $deleted_at = null;

    #[ORM\ManyToOne(inversedBy: 'orderItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Order $order = null;

    /**
     * Initializes timestamps to the current time.
     */
    public function __construct()
    {
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

    /** @return ?string SKU or product identifier (max 100 characters) */
    public function getProductCode(): ?string
    {
        return $this->product_code;
    }

    /** @param string $product_code SKU or product identifier */
    public function setProductCode(string $product_code): static
    {
        $this->product_code = $product_code;

        return $this;
    }

    /** @return ?int */
    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    /** @param int $quantity */
    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    /** @return ?int Unit price in the smallest currency unit */
    public function getUnitPrice(): ?int
    {
        return $this->unit_price;
    }

    /** @param int $unit_price Unit price in the smallest currency unit */
    public function setUnitPrice(int $unit_price): static
    {
        $this->unit_price = $unit_price;

        return $this;
    }

    /** @return ?int Precomputed line total (unit_price × quantity) in the smallest currency unit */
    public function getSubtotal(): ?int
    {
        return $this->subtotal;
    }

    /** @param int $subtotal Precomputed line total in the smallest currency unit */
    public function setSubtotal(int $subtotal): static
    {
        $this->subtotal = $subtotal;

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

    /** @return ?\DateTimeImmutable Null if the item has not been soft-deleted */
    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deleted_at;
    }

    /** @param \DateTimeImmutable $deleted_at */
    public function setDeletedAt(\DateTimeImmutable $deleted_at): static
    {
        $this->deleted_at = $deleted_at;

        return $this;
    }

    /** @return ?Order The parent order this item belongs to */
    public function getOrder(): ?Order
    {
        return $this->order;
    }

    /** @param ?Order $order Pass null to detach the item from its order */
    public function setOrder(?Order $order): static
    {
        $this->order = $order;

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
