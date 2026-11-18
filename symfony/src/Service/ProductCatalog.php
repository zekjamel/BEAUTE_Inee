<?php

namespace App\Service;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;

final class ProductCatalog
{
    public const TYPES = ['card' => 'Carte', 'card_diagnostic' => 'Carte + diagnostic(s)', 'diagnostic' => 'Diagnostic(s)', 'product' => 'Autre produit'];
    public const DELIVERY_LABELS = ['shipping' => 'Livraison à domicile', 'pickup' => 'Retrait en boutique'];

    public function __construct(private readonly EntityManagerInterface $entityManager) {}

    public function activeProducts(): array
    {
        return array_values(array_filter(
            $this->entityManager->getRepository(Product::class)->findBy(['isActive' => true], ['id' => 'ASC']),
            static fn (Product $product): bool => $product->getCurrency() === 'EUR' && $product->getDeliveryOptions() !== [],
        ));
    }

    public function save(Product $product, array $data): void
    {
        $sku = $this->string($data, 'sku', 100, true);
        if (!preg_match('/^[A-Za-z0-9_-]+$/D', $sku)) {
            throw new \InvalidArgumentException('La référence accepte uniquement lettres, chiffres, tirets et underscores.');
        }
        $existing = $this->entityManager->getRepository(Product::class)->findOneBy(['sku' => $sku]);
        if ($existing !== null && $existing !== $product) {
            throw new \InvalidArgumentException('Cette référence est déjà utilisée.');
        }
        $name = $this->string($data, 'name', 180, true);
        $description = $this->string($data, 'description', 4000);
        $type = $this->string($data, 'type', 40, true);
        if (!isset(self::TYPES[$type])) {
            throw new \InvalidArgumentException('Choisissez un type de produit valide.');
        }
        $price = $this->amount($data['price'] ?? null, false);
        $count = $data['diagnosticCount'] ?? '0';
        if (!is_string($count) || !preg_match('/^\d{1,3}$/D', $count) || (int) $count > 100) {
            throw new \InvalidArgumentException('Le nombre de diagnostics doit être compris entre 0 et 100.');
        }
        $count = (int) $count;
        if (in_array($type, ['card_diagnostic', 'diagnostic'], true) !== ($count > 0)) {
            throw new \InvalidArgumentException('Renseignez les diagnostics inclus pour une offre de diagnostics ou un pack ; utilisez 0 pour les autres produits.');
        }
        $delivery = [];
        foreach (self::DELIVERY_LABELS as $mode => $label) {
            if (($data[$mode . 'Enabled'] ?? '') !== '1') { continue; }
            $amount = $this->amount($data[$mode . 'Price'] ?? null, true);
            $terms = $this->string($data, $mode . 'Terms', 2000, true);
            $option = ['label' => $label, 'amount' => $amount, 'terms' => $terms];
            if ($mode === 'shipping') {
                $countries = array_values(array_unique(array_filter(preg_split('/[\s,;]+/', strtoupper($this->string($data, 'shippingCountries', 1000, true))))));
                foreach ($countries as $country) {
                    if (!preg_match('/^[A-Z]{2}$/D', $country) || !\Symfony\Component\Intl\Countries::exists($country)) {
                        throw new \InvalidArgumentException('Pays de livraison invalide : utilisez les codes ISO à deux lettres, par exemple FR, BE, CH.');
                    }
                }
                if ($countries === []) { throw new \InvalidArgumentException('Renseignez au moins un pays de livraison.'); }
                $option['countries'] = $countries;
            }
            $delivery[$mode] = $option;
        }
        $active = ($data['isActive'] ?? '') === '1';
        if ($active && $delivery === []) {
            throw new \InvalidArgumentException('Activez au moins un mode de remise pour publier cette offre.');
        }
        $product->setSku($sku)->setName($name)->setDescription($description ?: null)->setType($type)
            ->setUnitAmountCents($price)->setCurrency('EUR')->setDiagnosticCount($count)
            ->setDeliveryOptions($delivery)->setIsActive($active);
        $this->entityManager->persist($product);
        $this->entityManager->flush();
    }

    public function quote(Product $product, string $mode): array
    {
        $delivery = $product->getDeliveryOptions()[$mode] ?? null;
        if (!$product->isActive() || $product->getCurrency() !== 'EUR' || $delivery === null) {
            throw new \InvalidArgumentException('Cette offre ou ce mode de remise n’est plus disponible.');
        }
        return [
            'productId' => $product->getId(), 'sku' => $product->getSku(), 'name' => $product->getName(),
            'description' => $product->getDescription(), 'includesCard' => $product->includesCard(),
            'diagnosticCount' => $product->getDiagnosticCount(), 'unitAmount' => $product->getUnitAmountCents(),
            'deliveryMode' => $mode, 'delivery' => $delivery,
            'total' => $product->getUnitAmountCents() + $delivery['amount'],
        ];
    }

    public function fingerprint(array $quote): string
    {
        return hash('sha256', json_encode($quote, JSON_THROW_ON_ERROR));
    }

    private function string(array $data, string $key, int $limit, bool $required = false): string
    {
        $value = $data[$key] ?? '';
        if (!is_string($value) || mb_strlen($value) > $limit || ($required && trim($value) === '')) {
            throw new \InvalidArgumentException('Vérifiez le champ « ' . $key . ' » (maximum ' . $limit . ' caractères).');
        }
        return trim($value);
    }

    private function amount(mixed $value, bool $allowZero): int
    {
        if (!is_string($value) || !preg_match('/^\d{1,5}(?:[.,]\d{1,2})?$/D', trim($value))) {
            throw new \InvalidArgumentException('Saisissez un prix en euros avec au maximum deux décimales.');
        }
        $parts = explode('.', str_replace(',', '.', trim($value)));
        $amount = (int) $parts[0] * 100 + (int) str_pad($parts[1] ?? '', 2, '0');
        if (!$allowZero && $amount < 50) {
            throw new \InvalidArgumentException('Le prix du produit doit être d’au moins 0,50 € pour le paiement en ligne.');
        }
        return $amount;
    }
}
