<?php

namespace App\Entity;

use App\Entity\Traits\Timestampable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'products')]
#[ORM\HasLifecycleCallbacks]
class Product
{
    use Timestampable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    private string $sku;

    #[ORM\Column(length: 180)]
    private string $name;

    #[ORM\Column(length: 40)]
    private string $type = 'product';

    #[ORM\Column]
    private int $unitAmountCents;

    #[ORM\Column(length: 3)]
    private string $currency = 'EUR';

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $diagnosticCount = 0;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $deliveryOptions = null;

    public function getId(): ?int { return $this->id; }
    public function getSku(): string { return $this->sku; }
    public function setSku(string $sku): self { $this->sku = $sku; return $this; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function getType(): string { return $this->type; }
    public function setType(string $type): self { $this->type = $type; return $this; }
    public function includesCard(): bool { return in_array($this->type, ['card', 'card_diagnostic'], true); }
    public function getUnitAmountCents(): int { return $this->unitAmountCents; }
    public function setUnitAmountCents(int $amount): self { $this->unitAmountCents = $amount; return $this; }
    public function getCurrency(): string { return $this->currency; }
    public function setCurrency(string $currency): self { $this->currency = $currency; return $this; }
    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $active): self { $this->isActive = $active; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }
    public function getDiagnosticCount(): int { return $this->diagnosticCount; }
    public function setDiagnosticCount(int $count): self { $this->diagnosticCount = $count; return $this; }
    public function getDeliveryOptions(): array { return $this->deliveryOptions ?? []; }
    public function setDeliveryOptions(array $options): self { $this->deliveryOptions = $options; return $this; }
}
