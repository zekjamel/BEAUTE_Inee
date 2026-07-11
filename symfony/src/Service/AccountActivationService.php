<?php

namespace App\Service;

use App\Entity\AccountActivationToken;
use App\Entity\CustomerOrder;
use App\Entity\EmailLog;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class AccountActivationService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerInterface $mailer,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly Environment $twig,
        private readonly LoggerInterface $logger,
        #[Autowire('%env(MAILER_FROM)%')]
        private readonly string $fromAddress,
    ) {
    }

    /**
     * @return array{plainToken: string, emailLog: EmailLog}
     */
    public function issueAndSend(User $user, ?CustomerOrder $customerOrder = null): array
    {
        $plainToken = $this->issueToken($user);
        $this->entityManager->flush();
        $emailLog = $this->sendActivationEmail($user, $plainToken, $customerOrder);

        return ['plainToken' => $plainToken, 'emailLog' => $emailLog];
    }

    public function issueToken(User $user): string
    {
        $this->invalidateExistingTokens($user);

        $plainToken = bin2hex(random_bytes(32));
        $token = (new AccountActivationToken())
            ->setUser($user)
            ->setTokenHash(hash('sha256', $plainToken))
            ->setExpiresAt(new \DateTimeImmutable('+7 days'));

        $this->entityManager->persist($token);

        return $plainToken;
    }

    public function sendActivationEmail(User $user, string $plainToken, ?CustomerOrder $customerOrder = null): EmailLog
    {
        $email = $user->getEmail();
        $locale = $this->resolveLocale($user);
        $subject = $locale === 'en' ? 'Activate your Beaute INEE account' : 'Activez votre compte Beaute INEE';
        $activationUrl = $this->urlGenerator->generate(
            'account_activate',
            ['token' => $plainToken, 'lang' => $locale],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
        $emailLog = (new EmailLog())
            ->setType('account_activation')
            ->setRecipient($email)
            ->setSender($this->fromAddress)
            ->setSubject($subject)
            ->setStatus('pending')
            ->setCustomer($user->getCustomer())
            ->setUser($user)
            ->setCustomerOrder($customerOrder)
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
            ->html($this->twig->render('emails/account_activation.html.twig', [
                'activationUrl' => $activationUrl,
                'customer' => $user->getCustomer(),
                'locale' => $locale,
            ]))
            ->text($this->buildTextBody($activationUrl, $locale));

        try {
            $this->mailer->send($message);
            $emailLog->markSent();
            $this->entityManager->flush();
        } catch (TransportExceptionInterface $exception) {
            $emailLog->markFailed($exception->getMessage());
            $this->entityManager->flush();

            $this->logger->error('Activation email could not be sent.', [
                'email' => $email,
                'exception' => $exception,
            ]);
        }

        return $emailLog;
    }

    private function invalidateExistingTokens(User $user): void
    {
        $tokens = $this->entityManager
            ->getRepository(AccountActivationToken::class)
            ->findBy(['user' => $user]);

        foreach ($tokens as $token) {
            if ($token instanceof AccountActivationToken && $token->isUsable()) {
                $token->markUsed();
            }
        }
    }

    private function resolveLocale(User $user): string
    {
        $locale = $user->getPreferredLocale() ?? $user->getCustomer()?->getPreferredLocale() ?? 'fr';

        return $locale === 'en' ? 'en' : 'fr';
    }

    private function buildTextBody(string $activationUrl, string $locale): string
    {
        if ($locale === 'en') {
            return "Welcome to Beaute INEE.\n\nActivate your account and create your password here:\n" . $activationUrl;
        }

        return "Bienvenue chez Beaute INEE.\n\nActivez votre compte et créez votre mot de passe ici :\n" . $activationUrl;
    }
}
