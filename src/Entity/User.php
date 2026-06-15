<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\HasLifecycleCallbacks]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $locale = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $password = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $metadata = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $isActive = true;
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, Order>
     */
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'customer')]
    private Collection $orders;

    /**
     * Initializes the orders collection and sets timestamps to the current time.
     */
    public function __construct()
    {
        $this->orders = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    /** @return ?int */
    public function getId(): ?int
    {
        return $this->id;
    }

    /** @return ?string */
    public function getName(): ?string
    {
        return $this->name;
    }

    /** @param string $name */
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /** @return ?string Unique email address used to identify the user */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /** @param string $email Must be unique across all users */
    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /** @return ?\DateTimeImmutable */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /** @param \DateTimeImmutable $created_at */
    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->createdAt = $created_at;

        return $this;
    }

    /** @return ?\DateTimeImmutable */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /** @param \DateTimeImmutable $updated_at */
    public function setUpdatedAt(\DateTimeImmutable $updated_at): static
    {
        $this->updatedAt = $updated_at;

        return $this;
    }

    /** @return ?string Hashed password; null if password auth is not configured */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /** @param ?string $password Pass null to remove password auth */
    public function setPassword(?string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /** @return ?string */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /** @param ?string $phone */
    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    /** @return ?string BCP 47 locale tag (e.g. "en", "fr-FR") */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /** @param ?string $locale BCP 47 locale tag */
    public function setLocale(?string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    /** @return ?array Arbitrary JSON metadata stored against the user */
    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    /** @param ?array $metadata */
    public function setMetadata(?array $metadata): static
    {
        $this->metadata = $metadata;

        return $this;
    }

    /** @return ?bool False means the account is disabled */
    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    /** @param bool $isActive Set to false to disable the account */
    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    /**
     * Associates an order with this user and sets the owning side of the relation.
     * Has no effect if the order is already linked to this user.
     *
     * @param Order $order
     */
    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setCustomer($this);
        }

        return $this;
    }

    /**
     * Removes an order from this user and nullifies the customer reference on the order.
     *
     * @param Order $order
     */
    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            if ($order->getCustomer() === $this) {
                $order->setCustomer(null);
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
