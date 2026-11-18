<?php

namespace App\Security;

use App\Entity\User;
use App\Service\FeatureFlags;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class ActiveUserChecker implements UserCheckerInterface
{
    public function __construct(
        private readonly FeatureFlags $featureFlags,
    ) {
    }

    public function checkPreAuth(UserInterface $user): void
    {
        if ($user instanceof User && !$user->isActive()) {
            throw new CustomUserMessageAccountStatusException('Ce compte client n’est pas actif.');
        }

        if (!$this->featureFlags->isCustomerLoginEnabled() && !array_intersect(['ROLE_ADMIN', 'ROLE_OPERATOR'], $user->getRoles())) {
            throw new CustomUserMessageAccountStatusException('L’espace client sera bientôt disponible.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
