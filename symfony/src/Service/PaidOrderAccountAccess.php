<?php

namespace App\Service;

use App\Entity\CustomerOrder;
use App\Entity\User;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PaidOrderAccountAccess
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AccountActivationService $activation,
        private readonly UrlGeneratorInterface $urls,
        private readonly FeatureFlags $flags,
    ) {
    }

    /** @return array{url: string, create: bool}|null */
    public function prepare(CustomerOrder $order): ?array
    {
        if (!$this->flags->isCustomerLoginEnabled() || $order->getPaidAt() === null
            || in_array($order->getStatus(), ['refunded', 'cancelled', 'canceled', 'failed'], true)) {
            return null;
        }

        return $this->entityManager->wrapInTransaction(function () use ($order): ?array {
            $customer = $order->getCustomer();
            // Different paid orders from the same customer must share one account.
            $this->entityManager->refresh($customer, LockMode::PESSIMISTIC_WRITE);
            $email = $customer->getEmail();
            if (!is_string($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return null;
            }
            if ($customer->getUser() !== null && $customer->getUser()->getEmail() !== $email) {
                return null;
            }
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($user instanceof User) {
                // A purchase must never reactivate staff, disabled accounts or reassign an identity.
                if ($user->isStaff() || ($user->getCustomer() !== null && $user->getCustomer()->getId() !== $customer->getId())) {
                    return null;
                }
                if ($user->isActive()) {
                    return ['url' => $this->urls->generate('app_login', [], UrlGeneratorInterface::ABSOLUTE_URL), 'create' => false];
                }
                if ($user->getPassword() !== null) {
                    return null;
                }
            } else {
                $user = (new User())->setEmail($email)->setRoles(['ROLE_CUSTOMER'])->setIsActive(false)
                    ->setPreferredLocale($customer->getPreferredLocale());
                $this->entityManager->persist($user);
            }
            $user->setCustomer($customer);
            $plainToken = $this->activation->issueToken($user);

            return [
                'url' => $this->urls->generate('account_activate', ['token' => $plainToken, 'lang' => $customer->getPreferredLocale()], UrlGeneratorInterface::ABSOLUTE_URL),
                'create' => true,
            ];
        });
    }
}
