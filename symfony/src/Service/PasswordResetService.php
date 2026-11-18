<?php

namespace App\Service;

use App\Entity\EmailLog;
use App\Entity\PasswordResetToken;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class PasswordResetService
{
    public const TOKEN_LIFETIME = '+1 hour';
    public const MIN_PASSWORD_LENGTH = 10;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerInterface $mailer,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly Environment $twig,
        private readonly LoggerInterface $logger,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly AccountActivationService $accountActivationService,
        private readonly FeatureFlags $featureFlags,
        #[Autowire(service: 'limiter.password_reset_ip')]
        private readonly RateLimiterFactoryInterface $ipLimiter,
        #[Autowire(service: 'limiter.password_reset_email')]
        private readonly RateLimiterFactoryInterface $emailLimiter,
        #[Autowire('%env(MAILER_FROM)%')]
        private readonly string $fromAddress,
    ) {
    }

    /**
     * Traite une demande de réinitialisation.
     *
     * Retourne false uniquement si l'adresse IP a dépassé sa limite. Dans tous
     * les autres cas, la réponse affichée doit rester identique, que le compte
     * existe ou non, pour ne pas révéler les adresses enregistrées.
     */
    public function handleRequest(string $email, ?string $clientIp): bool
    {
        if (!$this->ipLimiter->create($clientIp ?? 'unknown')->consume()->isAccepted()) {
            return false;
        }

        $email = mb_strtolower(trim($email));
        if ($email === '' || !$this->emailLimiter->create($email)->consume()->isAccepted()) {
            return true;
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if (!$user instanceof User) {
            return true;
        }

        if (!$user->isStaff() && !$this->featureFlags->isCustomerLoginEnabled()) {
            return true;
        }

        if (!$user->isActive()) {
            // Compte jamais activé : on renvoie le lien d'activation habituel.
            // Un compte désactivé (mot de passe déjà défini) reste bloqué.
            if ($user->getPassword() === null) {
                $this->accountActivationService->issueAndSend($user);
            }

            return true;
        }

        $plainToken = $this->issueToken($user);
        $this->entityManager->flush();
        $this->sendResetEmail($user, $plainToken);

        return true;
    }

    public function findUsableToken(string $plainToken): ?PasswordResetToken
    {
        $token = $this->entityManager->getRepository(PasswordResetToken::class)->findOneBy([
            'tokenHash' => hash('sha256', $plainToken),
        ]);

        if (!$token instanceof PasswordResetToken || !$token->isUsable() || !$token->getUser()->isActive()) {
            return null;
        }

        if (!$token->getUser()->isStaff() && !$this->featureFlags->isCustomerLoginEnabled()) {
            return null;
        }

        return $token;
    }

    public function resetPassword(PasswordResetToken $token, string $plainPassword): void
    {
        $user = $token->getUser();
        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        $token->markUsed();
        $this->invalidateExistingTokens($user);

        $this->entityManager->flush();
    }

    private function issueToken(User $user): string
    {
        $this->invalidateExistingTokens($user);

        $plainToken = bin2hex(random_bytes(32));
        $token = (new PasswordResetToken())
            ->setUser($user)
            ->setTokenHash(hash('sha256', $plainToken))
            ->setExpiresAt(new \DateTimeImmutable(self::TOKEN_LIFETIME));

        $this->entityManager->persist($token);

        return $plainToken;
    }

    private function sendResetEmail(User $user, string $plainToken): EmailLog
    {
        $email = $user->getEmail();
        $locale = $this->resolveLocale($user);
        $subject = $locale === 'en' ? 'Reset your Beaute INEE password' : 'Réinitialisez votre mot de passe Beaute INEE';
        $resetUrl = $this->urlGenerator->generate(
            'password_reset_reset',
            ['token' => $plainToken],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
        $emailLog = (new EmailLog())
            ->setType('password_reset')
            ->setRecipient($email)
            ->setSender($this->fromAddress)
            ->setSubject($subject)
            ->setStatus('pending')
            ->setCustomer($user->getCustomer())
            ->setUser($user)
            ->setContext([
                'locale' => $locale,
                'transport' => 'smtp',
            ]);

        $this->entityManager->persist($emailLog);
        $this->entityManager->flush();

        $message = (new Email())
            ->from($this->fromAddress)
            ->to($email)
            ->subject($subject)
            ->html($this->twig->render('emails/password_reset.html.twig', [
                'resetUrl' => $resetUrl,
                'customer' => $user->getCustomer(),
                'locale' => $locale,
            ]))
            ->text($this->buildTextBody($resetUrl, $locale));

        try {
            $this->mailer->send($message);
            $emailLog->markSent();
            $this->entityManager->flush();
        } catch (TransportExceptionInterface $exception) {
            $emailLog->markFailed($exception->getMessage());
            $this->entityManager->flush();

            $this->logger->error('Password reset email could not be sent.', [
                'email' => $email,
                'exception' => $exception,
            ]);
        }

        return $emailLog;
    }

    private function invalidateExistingTokens(User $user): void
    {
        $tokens = $this->entityManager
            ->getRepository(PasswordResetToken::class)
            ->findBy(['user' => $user]);

        foreach ($tokens as $token) {
            if ($token instanceof PasswordResetToken && $token->isUsable()) {
                $token->markUsed();
            }
        }
    }

    private function resolveLocale(User $user): string
    {
        $locale = $user->getPreferredLocale() ?? $user->getCustomer()?->getPreferredLocale() ?? 'fr';

        return $locale === 'en' ? 'en' : 'fr';
    }

    private function buildTextBody(string $resetUrl, string $locale): string
    {
        if ($locale === 'en') {
            return "A password reset was requested for your Beaute INEE account.\n\nChoose a new password here (valid for 1 hour):\n" . $resetUrl . "\n\nIf you did not request it, you can ignore this email.";
        }

        return "Une réinitialisation du mot de passe de votre compte Beaute INEE a été demandée.\n\nChoisissez un nouveau mot de passe ici (lien valable 1 heure) :\n" . $resetUrl . "\n\nSi vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet e-mail.";
    }
}
