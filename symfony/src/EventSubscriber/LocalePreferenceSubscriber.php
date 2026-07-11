<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class LocalePreferenceSubscriber implements EventSubscriberInterface
{
    private const SUPPORTED_LOCALES = ['fr', 'en'];

    public function __construct(
        private readonly Security $security,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['resolveLocale', 0],
            KernelEvents::RESPONSE => ['rememberLocale', 0],
        ];
    }

    public function resolveLocale(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $requestedLocale = strtolower((string) $request->query->get('lang', ''));
        $user = $this->security->getUser();

        if (in_array($requestedLocale, self::SUPPORTED_LOCALES, true)) {
            $locale = $requestedLocale;

            if ($user instanceof User && $user->getPreferredLocale() !== $locale) {
                $user->setPreferredLocale($locale);
                $user->getCustomer()?->setPreferredLocale($locale);
                $this->entityManager->flush();
            }
        } elseif ($user instanceof User && in_array($user->getPreferredLocale(), self::SUPPORTED_LOCALES, true)) {
            $locale = $user->getPreferredLocale();
        } else {
            $cookieLocale = strtolower((string) $request->cookies->get('language', ''));
            $locale = in_array($cookieLocale, self::SUPPORTED_LOCALES, true)
                ? $cookieLocale
                : ($request->getPreferredLanguage(self::SUPPORTED_LOCALES) ?: 'fr');
        }

        $request->setLocale($locale);
        $request->attributes->set('_preferred_language', $locale);
    }

    public function rememberLocale(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $locale = $request->attributes->get('_preferred_language');

        if (!in_array($locale, self::SUPPORTED_LOCALES, true)) {
            return;
        }

        if ($request->cookies->get('language') !== $locale) {
            $event->getResponse()->headers->setCookie(new Cookie('language', $locale, new \DateTimeImmutable('+1 year'), '/'));
        }
    }
}
