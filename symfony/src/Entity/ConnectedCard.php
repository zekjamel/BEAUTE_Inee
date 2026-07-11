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

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?CustomerOrder $sourceOrder = null;

    #[ORM\Column(length: 40)]
    private string $status = 'ordered';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $orderedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $activatedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $collectedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $initializedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $configuredAt = null;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $provider = null;

    #[ORM\Column(length: 120, nullable: true, unique: true)]
    private ?string $cardLabIdentifier = null;

    #[ORM\Column(length: 120, nullable: true, unique: true)]
    private ?string $quardlockTokenSerialNumber = null;

    #[ORM\Column(length: 40)]
    private string $quardlockEnrollmentStatus = 'not_started';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $quardlockEnrollmentStartedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $quardlockEnrolledAt = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $quardlockEnrollmentNonceHash = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $quardlockEnrollmentExpiresAt = null;

    public function getId(): ?int { return $this->id; }
    public function getExternalIdentifier(): string { return $this->externalIdentifier; }
    public function setExternalIdentifier(string $externalIdentifier): self { $this->externalIdentifier = $externalIdentifier; return $this; }
    public function getCustomer(): ?Customer { return $this->customer; }
    public function setCustomer(?Customer $customer): self { $this->customer = $customer; return $this; }
    public function getSourceOrder(): ?CustomerOrder { return $this->sourceOrder; }
    public function setSourceOrder(?CustomerOrder $sourceOrder): self { $this->sourceOrder = $sourceOrder; return $this; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
    public function getOrderedAt(): ?\DateTimeImmutable { return $this->orderedAt; }
    public function setOrderedAt(?\DateTimeImmutable $orderedAt): self { $this->orderedAt = $orderedAt; return $this; }
    public function getActivatedAt(): ?\DateTimeImmutable { return $this->activatedAt; }
    public function setActivatedAt(?\DateTimeImmutable $activatedAt): self { $this->activatedAt = $activatedAt; return $this; }
    public function getCollectedAt(): ?\DateTimeImmutable { return $this->collectedAt; }
    public function setCollectedAt(?\DateTimeImmutable $collectedAt): self { $this->collectedAt = $collectedAt; return $this; }
    public function getInitializedAt(): ?\DateTimeImmutable { return $this->initializedAt; }
    public function setInitializedAt(?\DateTimeImmutable $initializedAt): self { $this->initializedAt = $initializedAt; return $this; }
    public function getConfiguredAt(): ?\DateTimeImmutable { return $this->configuredAt; }
    public function setConfiguredAt(?\DateTimeImmutable $configuredAt): self { $this->configuredAt = $configuredAt; return $this; }
    public function getProvider(): ?string { return $this->provider; }
    public function setProvider(?string $provider): self { $this->provider = $provider; return $this; }
    public function getCardLabIdentifier(): ?string { return $this->cardLabIdentifier; }
    public function setCardLabIdentifier(?string $cardLabIdentifier): self { $this->cardLabIdentifier = $cardLabIdentifier === null ? null : trim($cardLabIdentifier); return $this; }
    public function getQuardlockTokenSerialNumber(): ?string { return $this->quardlockTokenSerialNumber; }
    public function setQuardlockTokenSerialNumber(?string $quardlockTokenSerialNumber): self { $this->quardlockTokenSerialNumber = $quardlockTokenSerialNumber === null ? null : trim($quardlockTokenSerialNumber); return $this; }
    public function getQuardlockEnrollmentStatus(): string { return $this->quardlockEnrollmentStatus; }
    public function setQuardlockEnrollmentStatus(string $quardlockEnrollmentStatus): self { $this->quardlockEnrollmentStatus = $quardlockEnrollmentStatus; return $this; }
    public function getQuardlockEnrollmentStartedAt(): ?\DateTimeImmutable { return $this->quardlockEnrollmentStartedAt; }
    public function setQuardlockEnrollmentStartedAt(?\DateTimeImmutable $quardlockEnrollmentStartedAt): self { $this->quardlockEnrollmentStartedAt = $quardlockEnrollmentStartedAt; return $this; }
    public function getQuardlockEnrolledAt(): ?\DateTimeImmutable { return $this->quardlockEnrolledAt; }
    public function setQuardlockEnrolledAt(?\DateTimeImmutable $quardlockEnrolledAt): self { $this->quardlockEnrolledAt = $quardlockEnrolledAt; return $this; }
    public function getQuardlockEnrollmentNonceHash(): ?string { return $this->quardlockEnrollmentNonceHash; }
    public function setQuardlockEnrollmentNonceHash(?string $quardlockEnrollmentNonceHash): self { $this->quardlockEnrollmentNonceHash = $quardlockEnrollmentNonceHash; return $this; }
    public function getQuardlockEnrollmentExpiresAt(): ?\DateTimeImmutable { return $this->quardlockEnrollmentExpiresAt; }
    public function setQuardlockEnrollmentExpiresAt(?\DateTimeImmutable $quardlockEnrollmentExpiresAt): self { $this->quardlockEnrollmentExpiresAt = $quardlockEnrollmentExpiresAt; return $this; }
}
