<?php

namespace App\Entity;

use App\Entity\Traits\Timestampable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'connected_cards')]
#[ORM\HasLifecycleCallbacks]
class ConnectedCard
{
    use Timestampable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120, unique: true)]
    private string $externalIdentifier;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Customer $customer = null;

    #[ORM\Column(length: 40)]
    private string $status = 'ordered';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $orderedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $activatedAt = null;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $provider = null;

    public function getId(): ?int { return $this->id; }
    public function getExternalIdentifier(): string { return $this->externalIdentifier; }
    public function setExternalIdentifier(string $externalIdentifier): self { $this->externalIdentifier = $externalIdentifier; return $this; }
    public function getCustomer(): ?Customer { return $this->customer; }
    public function setCustomer(?Customer $customer): self { $this->customer = $customer; return $this; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
    public function getOrderedAt(): ?\DateTimeImmutable { return $this->orderedAt; }
    public function setOrderedAt(?\DateTimeImmutable $orderedAt): self { $this->orderedAt = $orderedAt; return $this; }
    public function getProvider(): ?string { return $this->provider; }
    public function setProvider(?string $provider): self { $this->provider = $provider; return $this; }
}
