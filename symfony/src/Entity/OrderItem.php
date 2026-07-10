<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'order_items')]
class OrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private CustomerOrder $customerOrder;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Product $product = null;

    #[ORM\Column(length: 180)]
    private string $label;

    #[ORM\Column]
    private int $quantity = 1;

    #[ORM\Column]
    private int $unitAmountCents;

    public function getId(): ?int { return $this->id; }
    public function getCustomerOrder(): CustomerOrder { return $this->customerOrder; }
    public function setCustomerOrder(CustomerOrder $customerOrder): self { $this->customerOrder = $customerOrder; return $this; }
    public function getProduct(): ?Product { return $this->product; }
    public function setProduct(?Product $product): self { $this->product = $product; return $this; }
    public function getLabel(): string { return $this->label; }
    public function setLabel(string $label): self { $this->label = $label; return $this; }
    public function getQuantity(): int { return $this->quantity; }
    public function setQuantity(int $quantity): self { $this->quantity = $quantity; return $this; }
    public function getUnitAmountCents(): int { return $this->unitAmountCents; }
    public function setUnitAmountCents(int $unitAmountCents): self { $this->unitAmountCents = $unitAmountCents; return $this; }
}
