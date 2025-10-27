<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    public const STATUS_ACTIVE    = 'active';
    public const STATUS_COMPLETED = 'completed';

    // Not mapped to the database (runtime-only)
    private int $priority = 0; // 1 = VIP, 0 = Normal

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'json')]
    #[Assert\NotBlank]
    private array $items = [];

    #[ORM\Column(type: 'datetime_immutable')]
    #[Assert\NotNull]
    private \DateTimeImmutable $pickupTime;

    #[ORM\Column(type: 'boolean')]
    private bool $vip = false;

    #[ORM\Column(type: 'string', length: 20)]
    private string $status = self::STATUS_ACTIVE;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(array $items, \DateTimeImmutable $pickupTime, bool $vip = false)
    {
        $this->items      = $items;
        $this->pickupTime = $pickupTime;
        $this->vip        = $vip;
        $this->priority   = $vip ? 1 : 0;
        $this->createdAt  = new \DateTimeImmutable();
        $this->status     = self::STATUS_ACTIVE;
    }

    // ─── Getters & Setters ──────────────────────────────────────────────

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function setItems(array $items): self
    {
        $this->items = $items;
        return $this;
    }

    public function getPickupTime(): \DateTimeImmutable
    {
        return $this->pickupTime;
    }

    public function setPickupTime(\DateTimeImmutable $pickupTime): self
    {
        $this->pickupTime = $pickupTime;
        return $this;
    }

    public function isVip(): bool
    {
        return $this->vip;
    }

    public function setVip(bool $vip): self
    {
        $this->vip = $vip;
        $this->priority = $vip ? 1 : 0;
        return $this;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
