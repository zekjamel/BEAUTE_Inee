<?php

namespace App\Entity;

use App\Entity\Traits\Timestampable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'payments')]
#[ORM\HasLifecycleCallbacks]
class Payment
{
    use Timestampable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private CustomerOrder $customerOrder;

    #[ORM\Column(length: 30)]
    private string $provider = 'stripe';

    #[ORM\Column(length: 255, unique: true)]
    private string $providerSessionId;

    #[ORM\Column(length: 40)]
    private string $status = 'pending';

    #[ORM\Column]
    private int $amountCents;

    #[ORM\Column(length: 3)]
    private string $currency = 'EUR';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $confirmedAt = null;

    public function getId(): ?int { return $this->id; }
    public function getCustomerOrder(): CustomerOrder { return $this->customerOrder; }
    public function setCustomerOrder(CustomerOrder $customerOrder): self { $this->customerOrder = $customerOrder; return $this; }
    public function getProvider(): string { return $this->provider; }
    public function setProvider(string $provider): self { $this->provider = $provider; return $this; }
    public function getProviderSessionId(): string { return $this->providerSessionId; }
    public function setProviderSessionId(string $providerSessionId): self { $this->providerSessionId = $providerSessionId; return $this; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
    public function getAmountCents(): int { return $this->amountCents; }
    public function setAmountCents(int $amountCents): self { $this->amountCents = $amountCents; return $this; }
    public function getCurrency(): string { return $this->currency; }
    public function setCurrency(string $currency): self { $this->currency = strtoupper($currency); return $this; }
    public function getConfirmedAt(): ?\DateTimeImmutable { return $this->confirmedAt; }
    public function markSucceeded(): self
    {
        $this->status = 'succeeded';
        $this->confirmedAt = new \DateTimeImmutable();

        return $this;
    }
}
