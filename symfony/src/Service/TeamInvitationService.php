<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final class TeamInvitationService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AccountActivationService $activation,
    ) {
    }

    public function invite(string $email, string $role): bool
    {
        $email = mb_strtolower(trim($email));
        if (mb_strlen($email) > 180 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Saisissez une adresse e-mail valide.');
        }
        if (!in_array($role, ['ROLE_ADMIN', 'ROLE_OPERATOR'], true)) {
            throw new \InvalidArgumentException('Choisissez un rôle valide.');
        }
        if ($this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]) !== null) {
            throw new \InvalidArgumentException('Cette adresse possède déjà un compte. Aucun accès n’a été modifié.');
        }

        $user = (new User())->setEmail($email)->setRoles([$role])->setPreferredLocale('fr');
        $this->entityManager->persist($user);

        return $this->resend($user);
    }

    public function resend(User $user): bool
    {
        if (!$user->isStaff() || $user->isActive()) {
            throw new \InvalidArgumentException('Seuls les membres de l’équipe en attente d’activation peuvent être invités.');
        }

        return $this->activation->issueAndSend($user)['emailLog']->getStatus() === 'sent';
    }
}
