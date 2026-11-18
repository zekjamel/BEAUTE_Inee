<?php

namespace App\Controller;

use App\Service\PasswordResetService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PasswordResetController extends AbstractController
{
    public function __construct(
        private readonly PasswordResetService $passwordResetService,
    ) {
    }

    #[Route('/mot-de-passe-oublie', name: 'password_reset_request', methods: ['GET', 'POST'])]
    public function request(Request $request): Response
    {
        if (!$request->isMethod('POST')) {
            return $this->render('password_reset/request.html.twig', ['error' => null]);
        }

        if (!$this->isCsrfTokenValid('password_reset_request', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton de formulaire invalide.');
        }

        $accepted = $this->passwordResetService->handleRequest(
            (string) $request->request->get('email'),
            $request->getClientIp(),
        );

        if (!$accepted) {
            return $this->render('password_reset/request.html.twig', [
                'error' => 'Trop de demandes ont été envoyées. Merci de réessayer dans quelques minutes.',
            ], new Response(status: Response::HTTP_TOO_MANY_REQUESTS));
        }

        return $this->redirectToRoute('password_reset_check_email');
    }

    #[Route('/mot-de-passe-oublie/email-envoye', name: 'password_reset_check_email', methods: ['GET'])]
    public function checkEmail(): Response
    {
        return $this->render('password_reset/check_email.html.twig');
    }

    #[Route('/mot-de-passe-oublie/{token}', name: 'password_reset_reset', requirements: ['token' => '[a-f0-9]{64}'], methods: ['GET', 'POST'])]
    public function reset(string $token, Request $request): Response
    {
        $resetToken = $this->passwordResetService->findUsableToken($token);
        if ($resetToken === null) {
            return $this->render('password_reset/invalid.html.twig', [], new Response(status: Response::HTTP_GONE));
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('password_reset_' . $token, (string) $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Jeton de formulaire invalide.');
            }

            $password = (string) $request->request->get('password');
            $passwordConfirmation = (string) $request->request->get('passwordConfirmation');

            if (mb_strlen($password) < PasswordResetService::MIN_PASSWORD_LENGTH || $password !== $passwordConfirmation) {
                return $this->render('password_reset/reset.html.twig', [
                    'token' => $token,
                    'error' => 'Le mot de passe doit contenir au moins 10 caractères et les deux champs doivent être identiques.',
                ], new Response(status: Response::HTTP_UNPROCESSABLE_ENTITY));
            }

            $this->passwordResetService->resetPassword($resetToken, $password);
            $this->addFlash('success', 'Votre mot de passe a été modifié. Vous pouvez vous connecter.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('password_reset/reset.html.twig', [
            'token' => $token,
            'error' => null,
        ]);
    }
}
