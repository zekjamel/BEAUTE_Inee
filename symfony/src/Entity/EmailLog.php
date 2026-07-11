<?php

namespace App\Entity;

use App\Entity\Traits\Timestampable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'email_logs')]
#[ORM\HasLifecycleCallbacks]
class EmailLog
{
    use Timestampable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 80)]
    private string $type;

    #[ORM\Column(length: 180)]
    private string $recipient;

    #[ORM\Column(length: 180)]
    private string $sender;

    #[ORM\Column(length: 190)]
    private string $subject;

    #[ORM\Column(length: 30)]
    private string $status = 'pending';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $sentAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $errorMessage = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $context = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Customer $customer = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?CustomerOrder $customerOrder = null;

    public function getId(): ?int { return $this->id; }
    public function getType(): string { return $this->type; }
    public function setType(string $type): self { $this->type = $type; return $this; }
    public function getRecipient(): string { return $this->recipient; }
    public function setRecipient(string $recipient): self { $this->recipient = mb_strtolower(trim($recipient)); return $this; }
    public function getSender(): string { return $this->sender; }
    public function setSender(string $sender): self { $this->sender = $sender; return $this; }
    public function getSubject(): string { return $this->subject; }
    public function setSubject(string $subject): self { $this->subject = $subject; return $this; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
    public function getSentAt(): ?\DateTimeImmutable { return $this->sentAt; }
    public function markSent(): self { $this->status = 'sent'; $this->sentAt = new \DateTimeImmutable(); $this->errorMessage = null; return $this; }
    public function markFailed(string $errorMessage): self { $this->status = 'failed'; $this->errorMessage = $errorMessage; return $this; }
    public function getErrorMessage(): ?string { return $this->errorMessage; }
    public function getContext(): ?array { return $this->context; }
    public function setContext(?array $context): self { $this->context = $context; return $this; }
    public function getCustomer(): ?Customer { return $this->customer; }
    public function setCustomer(?Customer $customer): self { $this->customer = $customer; return $this; }
    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): self { $this->user = $user; return $this; }
    public function getCustomerOrder(): ?CustomerOrder { return $this->customerOrder; }
    public function setCustomerOrder(?CustomerOrder $customerOrder): self { $this->customerOrder = $customerOrder; return $this; }
}
