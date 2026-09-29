<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\TeamInvitationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/equipe')]
#[IsGranted('ROLE_ADMIN')]
final class TeamController extends AbstractController
{
    #[Route('', name: 'admin_team_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        return $this->render('admin/team/index.html.twig', [
            'members' => array_filter(
                $entityManager->getRepository(User::class)->findBy([], ['email' => 'ASC']),
                static fn (User $user): bool => $user->isStaff(),
            ),
        ]);
    }

    #[Route('/inviter', name: 'admin_team_invite', methods: ['POST'])]
    public function invite(Request $request, TeamInvitationService $invitations): Response
    {
        if (!$this->isCsrfTokenValid('team_invite', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton de formulaire invalide.');
        }
        try {
            $this->reportDelivery($invitations->invite(
                (string) $request->request->get('email'),
                (string) $request->request->get('role'),
            ));
        } catch (\InvalidArgumentException $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        return $this->redirectToRoute('admin_team_index');
    }

    #[Route('/{id}/renvoyer', name: 'admin_team_resend', methods: ['POST'])]
    public function resend(User $user, Request $request, TeamInvitationService $invitations): Response
    {
        if (!$this->isCsrfTokenValid('team_resend_' . $user->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton de formulaire invalide.');
        }
        try {
            $this->reportDelivery($invitations->resend($user));
        } catch (\InvalidArgumentException $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        return $this->redirectToRoute('admin_team_index');
    }

    private function reportDelivery(bool $sent): void
    {
        $this->addFlash($sent ? 'success' : 'error', $sent
            ? 'Invitation envoyée. La personne dispose de 7 jours pour choisir son mot de passe.'
            : 'Le compte est en attente d’activation, mais l’e-mail n’a pas pu être envoyé. Vérifiez la configuration SMTP puis renvoyez l’invitation.');
    }
}
