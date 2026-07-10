<?php

namespace App\Entity;

use App\Entity\Traits\Timestampable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'diagnostics')]
#[ORM\HasLifecycleCallbacks]
class Diagnostic
{
    use Timestampable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Customer $customer = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Center $center = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $performedAt;

    #[ORM\Column(length: 50)]
    private string $status = 'completed';

    #[ORM\Column(length: 100, nullable: true, unique: true)]
    private ?string $externalReference = null;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $skinType = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $skinConditions = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $priorityObjectives = null;

    #[ORM\Column(nullable: true)]
    private ?int $globalScore = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $recommendations = null;
}
