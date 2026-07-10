<?php

namespace App\Entity;

use App\Entity\Traits\Timestampable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'customers')]
#[ORM\HasLifecycleCallbacks]
class Customer
{
    use Timestampable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private string $firstName;

    #[ORM\Column(length: 120)]
    private string $lastName;

    #[ORM\Column(length: 180, nullable: true, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 32, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(nullable: true)]
    private ?int $birthDay = null;

    #[ORM\Column(nullable: true)]
    private ?int $birthMonth = null;

    #[ORM\Column(nullable: true)]
    private ?int $birthYear = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $ageRange = null;

    #[ORM\Column(length: 5)]
    private string $preferredLocale = 'fr';

    #[ORM\Column(length: 40)]
    private string $status = 'prospect';

    #[ORM\Column(length: 60, nullable: true)]
    private ?string $acquisitionChannel = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Center $acquisitionCenter = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Center $referenceCenter = null;

    #[ORM\OneToOne(mappedBy: 'customer')]
    private ?User $user = null;

    public function getId(): ?int { return $this->id; }
    public function getFirstName(): string { return $this->firstName; }
    public function setFirstName(string $firstName): self { $this->firstName = $firstName; return $this; }
    public function getLastName(): string { return $this->lastName; }
    public function setLastName(string $lastName): self { $this->lastName = $lastName; return $this; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): self { $this->email = $email === null ? null : mb_strtolower(trim($email)); return $this; }
    public function getPhone(): ?string { return $this->phone; }
    public function setPhone(?string $phone): self { $this->phone = $phone; return $this; }
    public function getPreferredLocale(): string { return $this->preferredLocale; }
    public function setPreferredLocale(string $preferredLocale): self { $this->preferredLocale = $preferredLocale; return $this; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
    public function getUser(): ?User { return $this->user; }
}
