<?php

namespace App\Service;

use App\Entity\ConnectedCard;
use App\Entity\Customer;
use App\Entity\CustomerOrder;
use App\Entity\OrderItem;
use App\Entity\Payment;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;

final class CardOrderService
{
    public const CARD_AMOUNT = 7000;
    public const SHIPPING_AMOUNT = 700;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly OrderConfirmationService $confirmation,
    )
    {
    }

    public function create(array $payload): CustomerOrder
    {
        foreach (['firstName' => 100, 'lastName' => 100, 'email' => 180, 'addressLine1' => 255, 'postalCode' => 20, 'city' => 100] as $field => $limit) {
            if (!is_string($payload[$field] ?? null) || trim($payload[$field]) === '' || mb_strlen($payload[$field]) > $limit) {
                throw new \InvalidArgumentException('Complétez vos coordonnées et votre adresse de livraison.');
            }
        }
        $email = mb_strtolower(trim($payload['email']));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !in_array($payload['country'] ?? null, ['FR', 'BE', 'CH'], true)) {
            throw new \InvalidArgumentException('Vérifiez votre adresse e-mail et votre pays de livraison.');
        }
        $phone = $payload['phone'] ?? '';
        if (!is_string($phone) || mb_strlen($phone) > 32) {
            throw new \InvalidArgumentException('Vérifiez votre numéro de téléphone.');
        }
        $customer = $this->entityManager->getRepository(Customer::class)->findOneBy(['email' => $email]);
        // An unauthenticated checkout must not overwrite an existing customer's profile.
        if (!$customer instanceof Customer) {
            $customer = (new Customer())->setEmail($email)
                ->setFirstName(trim($payload['firstName']))->setLastName(trim($payload['lastName']))
                ->setPhone(trim($phone) ?: null)->setPreferredLocale('fr')->setStatus('checkout_started');
            $this->entityManager->persist($customer);
        }
        $order = (new CustomerOrder())->setReference('BI-' . strtoupper(bin2hex(random_bytes(8))))
            ->setCustomer($customer)->setStatus('pending_payment')
            ->setTotalAmountCents(self::CARD_AMOUNT + self::SHIPPING_AMOUNT)->setCurrency('EUR')
            ->setShippingAddress([
                'fullName' => trim($payload['firstName']) . ' ' . trim($payload['lastName']),
                'line1' => trim($payload['addressLine1']), 'postalCode' => trim($payload['postalCode']),
                'city' => trim($payload['city']), 'country' => $payload['country'],
            ]);
        $this->entityManager->persist($order);
        foreach ([CardReference::PRODUCT_NAME => self::CARD_AMOUNT, 'Livraison' => self::SHIPPING_AMOUNT] as $label => $amount) {
            $this->entityManager->persist((new OrderItem())->setCustomerOrder($order)->setLabel($label)->setQuantity(1)->setUnitAmountCents($amount));
        }
        $this->entityManager->persist((new Payment())->setCustomerOrder($order)->setProvider('stripe')
            ->setProviderSessionId('pending_' . $order->getReference())->setStatus('pending')
            ->setAmountCents($order->getTotalAmountCents())->setCurrency('EUR'));
        $this->entityManager->flush();

        return $order;
    }

    public function recordSession(CustomerOrder $order, string $sessionId): void
    {
        $payment = $this->entityManager->getRepository(Payment::class)->findOneBy(['customerOrder' => $order, 'provider' => 'stripe']);
        $payment->setProviderSessionId($sessionId);
        $this->entityManager->flush();
    }

    public function handleEvent(array $event, bool $live): bool
    {
        $type = $event['type'] ?? '';
        if (!in_array($type, ['checkout.session.completed', 'checkout.session.async_payment_succeeded', 'checkout.session.async_payment_failed', 'checkout.session.expired'], true)) {
            return true;
        }
        $session = $event['data']['object'] ?? [];
        if (($session['metadata']['application'] ?? '') !== 'beaute_inee_card') {
            return true;
        }
        if (($event['livemode'] ?? null) !== $live || ($session['livemode'] ?? null) !== $live) {
            throw new \UnexpectedValueException('Environnement Stripe incorrect.');
        }
        $confirmedOrder = $this->entityManager->wrapInTransaction(function () use ($session, $type, $live): ?CustomerOrder {
            $order = $this->entityManager->getRepository(CustomerOrder::class)->findOneBy(['reference' => $session['metadata']['order_reference'] ?? '']);
            if (!$order instanceof CustomerOrder) {
                throw new \UnexpectedValueException('Commande Stripe inconnue.');
            }
            // Serialize duplicate/concurrent webhooks before checking payment and card state.
            $this->entityManager->refresh($order, LockMode::PESSIMISTIC_WRITE);
            $payment = $this->entityManager->getRepository(Payment::class)->findOneBy(['customerOrder' => $order, 'provider' => 'stripe']);
            if (!$payment instanceof Payment
                || !is_string($session['id'] ?? null) || !str_starts_with($session['id'], 'cs_')
                || !in_array($payment->getProviderSessionId(), [$session['id'], 'pending_' . $order->getReference()], true)
                || ($session['client_reference_id'] ?? null) !== $order->getReference()
                || ($session['mode'] ?? null) !== 'payment'
                || ($session['amount_total'] ?? null) !== $payment->getAmountCents()
                || $payment->getAmountCents() !== $order->getTotalAmountCents()
                || strtoupper($session['currency'] ?? '') !== $payment->getCurrency()) {
                throw new \UnexpectedValueException('Le paiement ne correspond pas à la commande.');
            }
            if ($payment->getStatus() === 'succeeded') {
                if (($session['payment_status'] ?? null) !== 'paid' || !in_array($type, ['checkout.session.completed', 'checkout.session.async_payment_succeeded'], true)) {
                    return null;
                }
                $this->confirmation->queue($order);
                return $order;
            }
            $payment->setProviderSessionId($session['id']);
            if (in_array($type, ['checkout.session.expired', 'checkout.session.async_payment_failed'], true)) {
                $payment->setStatus($type === 'checkout.session.expired' ? 'expired' : 'failed');
                return null;
            }
            if (($session['payment_status'] ?? null) !== 'paid') {
                return null;
            }
            $payment->markSucceeded();
            $order->markPaid();
            if ($this->entityManager->getRepository(ConnectedCard::class)->findOneBy(['sourceOrder' => $order]) === null) {
                $card = (new ConnectedCard())->setExternalIdentifier(CardReference::forOrder($order->getReference(), !$live))
                    ->setProvider(CardReference::PROVIDER)->setCustomer($order->getCustomer())->setSourceOrder($order)
                    ->setStatus('ordered')->setOrderedAt(new \DateTimeImmutable());
                $this->entityManager->persist($card);
            }
            $this->confirmation->queue($order);
            return $order;
        });

        return $confirmedOrder === null || $this->confirmation->deliver($confirmedOrder);
    }
}
