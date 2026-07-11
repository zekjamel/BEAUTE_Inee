<?php

namespace App\Entity;

use App\Entity\Traits\Timestampable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'quardlock_audit_logs')]
#[ORM\Index(columns: ['status'], name: 'IDX_QUARDLOCK_AUDIT_STATUS')]
#[ORM\Index(columns: ['created_at'], name: 'IDX_QUARDLOCK_AUDIT_CREATED')]
#[ORM\HasLifecycleCallbacks]
class QuardlockAuditLog
{
    use Timestampable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 80)]
    private string $action;

    #[ORM\Column(length: 30)]
    private string $status;

    #[ORM\Column(length: 160, nullable: true)]
    private ?string $endpoint = null;

    #[ORM\Column(nullable: true)]
    private ?int $httpStatus = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $message = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?User $initiatedBy = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?ConnectedCard $connectedCard = null;

    public function getId(): ?int { return $this->id; }
    public function getAction(): string { return $this->action; }
    public function setAction(string $action): self { $this->action = $action; return $this; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
    public function getEndpoint(): ?string { return $this->endpoint; }
    public function setEndpoint(?string $endpoint): self { $this->endpoint = $endpoint; return $this; }
    public function getHttpStatus(): ?int { return $this->httpStatus; }
    public function setHttpStatus(?int $httpStatus): self { $this->httpStatus = $httpStatus; return $this; }
    public function getMessage(): ?string { return $this->message; }
    public function setMessage(?string $message): self { $this->message = $message; return $this; }
    public function getInitiatedBy(): ?User { return $this->initiatedBy; }
    public function setInitiatedBy(?User $initiatedBy): self { $this->initiatedBy = $initiatedBy; return $this; }
    public function getConnectedCard(): ?ConnectedCard { return $this->connectedCard; }
    public function setConnectedCard(?ConnectedCard $connectedCard): self { $this->connectedCard = $connectedCard; return $this; }
}
