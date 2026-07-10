<?php

namespace App\Service;

use App\Entity\AccountActivationToken;
use App\Entity\ConnectedCard;
use App\Entity\Customer;
use App\Entity\CustomerOrder;
use App\Entity\OrderItem;
use App\Entity\Payment;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class CheckoutSimulationService
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function createCardOrder(array $payload): CustomerOrder
    {
        $email = mb_strtolower(trim((string) ($payload['email'] ?? '')));
        $customer = $this->findOrCreateCustomer($email);

        $customer
            ->setFirstName(trim((string) ($payload['firstName'] ?? '')))
            ->setLastName(trim((string) ($payload['lastName'] ?? '')))
            ->setPhone(trim((string) ($payload['phone'] ?? '')) ?: null)
            ->setPreferredLocale((string) ($payload['locale'] ?? 'fr'))
            ->setStatus('checkout_started');

        $reference = 'BI-' . strtoupper(bin2hex(random_bytes(4)));

        $order = (new CustomerOrder())
            ->setReference($reference)
            ->setCustomer($customer)
            ->setStatus('pending_payment')
            ->setTotalAmountCents(7700)
            ->setCurrency('EUR')
            ->setShippingAddress([
                'fullName' => trim(($payload['firstName'] ?? '') . ' ' . ($payload['lastName'] ?? '')),
                'line1' => trim((string) ($payload['addressLine1'] ?? '')),
                'postalCode' => trim((string) ($payload['postalCode'] ?? '')),
                'city' => trim((string) ($payload['city'] ?? '')),
                'country' => trim((string) ($payload['country'] ?? 'FR')),
            ]);

        $item = (new OrderItem())
            ->setCustomerOrder($order)
            ->setLabel('Carte connectee Beaute INEE')
            ->setQuantity(1)
            ->setUnitAmountCents(7000);

        $payment = (new Payment())
            ->setCustomerOrder($order)
            ->setProvider('fake')
            ->setProviderSessionId('fake_checkout_' . strtolower($reference))
            ->setStatus('pending')
            ->setAmountCents(7700)
            ->setCurrency('EUR');

        $this->entityManager->persist($customer);
        $this->entityManager->persist($order);
        $this->entityManager->persist($item);
        $this->entityManager->persist($payment);
        $this->entityManager->flush();

        return $order;
    }

    public function markOrderPaid(CustomerOrder $order): string
    {
        $payment = $this->entityManager
            ->getRepository(Payment::class)
            ->findOneBy(['customerOrder' => $order, 'provider' => 'fake']);

        if ($payment instanceof Payment && $payment->getStatus() !== 'succeeded') {
            $payment->markSucceeded();
        }

        if ($order->getStatus() !== 'paid') {
            $order->markPaid();
        }

        $customer = $order->getCustomer();
        $customer->setStatus('pending_activation');

        $user = $this->findOrCreateUser($customer);
        $card = $this->findOrCreateConnectedCard($customer, $order);

        $activationToken = $this->issueActivationToken($user);

        $this->entityManager->persist($payment);
        $this->entityManager->persist($order);
        $this->entityManager->persist($customer);
        $this->entityManager->persist($user);
        $this->entityManager->persist($card);
        $this->entityManager->persist($activationToken['entity']);
        $this->entityManager->flush();

        return $activationToken['plain'];
    }

    private function findOrCreateCustomer(string $email): Customer
    {
        $customer = $this->entityManager->getRepository(Customer::class)->findOneBy(['email' => $email]);

        if ($customer instanceof Customer) {
            return $customer;
        }

        return (new Customer())->setEmail($email);
    }

    private function findOrCreateUser(Customer $customer): User
    {
        $user = $customer->getUser();

        if ($user instanceof User) {
            return $user;
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $customer->getEmail()]);

        if ($user instanceof User) {
            return $user->setCustomer($customer);
        }

        return (new User())
            ->setEmail((string) $customer->getEmail())
            ->setRoles(['ROLE_CUSTOMER'])
            ->setIsActive(false)
            ->setCustomer($customer);
    }

    private function findOrCreateConnectedCard(Customer $customer, CustomerOrder $order): ConnectedCard
    {
        $externalIdentifier = 'DEV-CARD-' . $order->getReference();
        $card = $this->entityManager->getRepository(ConnectedCard::class)->findOneBy([
            'externalIdentifier' => $externalIdentifier,
        ]);

        if ($card instanceof ConnectedCard) {
            return $card;
        }

        return (new ConnectedCard())
            ->setExternalIdentifier($externalIdentifier)
            ->setProvider('fake-cardlab')
            ->setCustomer($customer)
            ->setStatus('ordered')
            ->setOrderedAt(new \DateTimeImmutable());
    }

    /**
     * @return array{plain: string, entity: AccountActivationToken}
     */
    private function issueActivationToken(User $user): array
    {
        $plainToken = bin2hex(random_bytes(32));
        $token = (new AccountActivationToken())
            ->setUser($user)
            ->setTokenHash(hash('sha256', $plainToken))
            ->setExpiresAt(new \DateTimeImmutable('+7 days'));

        return ['plain' => $plainToken, 'entity' => $token];
    }
}
