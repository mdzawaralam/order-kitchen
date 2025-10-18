<?php
// src/Entity/Order.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\OrderRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
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
        $this->items = $items;
        $this->pickupTime = $pickupTime;
        $this->vip = $vip;
        $this->priority = $vip ? 1 : 0;
        $this->createdAt = new \DateTimeImmutable();
        $this->status = self::STATUS_ACTIVE;
    }

    // getters & setters...

    public function getId(): ?int { return $this->id; }
    public function getItems(): array { return $this->items; }
    public function setItems(array $items): self { $this->items = $items; return $this; }

    public function getPickupTime(): \DateTimeImmutable { return $this->pickupTime; }
    public function setPickupTime(\DateTimeImmutable $t): self { $this->pickupTime = $t; return $this; }

    public function isVip(): bool { return $this->vip; }
    public function setVip(bool $vip): self { $this->vip = $vip; return $this; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
