<?php

namespace App\Controller;

use App\Entity\AccountActivationToken;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class AccountController extends AbstractController
{
    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('account/login.html.twig', [
            'lastUsername' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): never
    {
        throw new \LogicException('Logout is handled by Symfony security.');
    }

    #[Route('/account/activate/{token}', name: 'account_activate', methods: ['GET', 'POST'])]
    public function activate(
        string $token,
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        $activationToken = $entityManager->getRepository(AccountActivationToken::class)->findOneBy([
            'tokenHash' => hash('sha256', $token),
        ]);

        if (!$activationToken instanceof AccountActivationToken || !$activationToken->isUsable()) {
            return $this->render('account/activation_expired.html.twig');
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('account_activate_' . $token, (string) $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Jeton de formulaire invalide.');
            }

            $password = (string) $request->request->get('password');
            $passwordConfirmation = (string) $request->request->get('passwordConfirmation');

            if (mb_strlen($password) < 10 || $password !== $passwordConfirmation) {
                return $this->render('account/activate.html.twig', [
                    'token' => $token,
                    'error' => 'Le mot de passe doit contenir au moins 10 caracteres et les deux champs doivent etre identiques.',
                ]);
            }

            $user = $activationToken->getUser();
            $user->setPassword($passwordHasher->hashPassword($user, $password));
            $user->setIsActive(true);
            $user->getCustomer()?->setStatus('active');
            $activationToken->markUsed();

            $entityManager->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('account/activate.html.twig', [
            'token' => $token,
            'error' => null,
        ]);
    }

    #[Route('/mon-compte', name: 'account_dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        return $this->render('account/dashboard.html.twig');
    }
}
