<?php

namespace App\Entity;

use App\Entity\Traits\Timestampable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'customer_orders')]
#[ORM\HasLifecycleCallbacks]
class CustomerOrder
{
    use Timestampable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40, unique: true)]
    private string $reference;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private Customer $customer;

    #[ORM\Column(length: 40)]
    private string $status = 'pending';

    #[ORM\Column]
    private int $totalAmountCents;

    #[ORM\Column(length: 3)]
    private string $currency = 'EUR';

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $shippingAddress = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $paidAt = null;

    public function getId(): ?int { return $this->id; }
    public function getReference(): string { return $this->reference; }
    public function setReference(string $reference): self { $this->reference = $reference; return $this; }
    public function getCustomer(): Customer { return $this->customer; }
    public function setCustomer(Customer $customer): self { $this->customer = $customer; return $this; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
    public function getTotalAmountCents(): int { return $this->totalAmountCents; }
    public function setTotalAmountCents(int $totalAmountCents): self { $this->totalAmountCents = $totalAmountCents; return $this; }
    public function getCurrency(): string { return $this->currency; }
    public function setCurrency(string $currency): self { $this->currency = strtoupper($currency); return $this; }
    public function getShippingAddress(): ?array { return $this->shippingAddress; }
    public function setShippingAddress(?array $shippingAddress): self { $this->shippingAddress = $shippingAddress; return $this; }
    public function getPaidAt(): ?\DateTimeImmutable { return $this->paidAt; }
    public function markPaid(): self
    {
        $this->status = 'paid';
        $this->paidAt = new \DateTimeImmutable();

        return $this;
    }
}
