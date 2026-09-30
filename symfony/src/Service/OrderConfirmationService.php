<?php

namespace App\Service;

use App\Entity\CustomerOrder;
use App\Entity\EmailLog;
use App\Entity\OrderItem;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

final class OrderConfirmationService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerInterface $mailer,
        private readonly Environment $twig,
        #[Autowire('%env(MAILER_FROM)%')]
        private readonly string $fromAddress,
    ) {
    }

    // Called while the payment transaction holds the order lock.
    public function queue(CustomerOrder $order): void
    {
        if ($this->findLog($order) !== null) {
            return;
        }
        $log = (new EmailLog())->setType('order_confirmation')->setCustomerOrder($order)
            ->setCustomer($order->getCustomer())->setRecipient((string) $order->getCustomer()->getEmail())
            ->setSender($this->fromAddress)->setSubject('Confirmation de votre commande ' . $order->getReference())
            ->setContext(['locale' => 'fr']);
        $this->entityManager->persist($log);
    }

    public function deliver(CustomerOrder $order): bool
    {
        // Commit a claim before SMTP: concurrent webhook deliveries cannot send twice.
        $log = $this->entityManager->wrapInTransaction(function () use ($order): ?EmailLog {
            $this->entityManager->refresh($order, LockMode::PESSIMISTIC_WRITE);
            $log = $this->findLog($order);
            if ($log !== null) {
                $this->entityManager->refresh($log);
            }
            if ($log === null || $order->getPaidAt() === null || !in_array($log->getStatus(), ['pending', 'failed'], true)) {
                return null;
            }
            $log->setStatus('sending');
            return $log;
        });
        if ($log === null) {
            return true;
        }
        try {
            $context = [
                'order' => $order,
                'items' => $this->entityManager->getRepository(OrderItem::class)->findBy(['customerOrder' => $order], ['id' => 'ASC']),
            ];
            $message = (new Email())->from($log->getSender())->to($log->getRecipient())->subject($log->getSubject())
                ->html($this->twig->render('emails/order_confirmation.html.twig', $context))
                ->text($this->twig->render('emails/order_confirmation.txt.twig', $context));
            $this->mailer->send($message);
        } catch (TransportExceptionInterface $exception) {
            // Do not store SMTP diagnostics that could contain credentials or personal data.
            $log->markFailed('Échec du transport email. Une nouvelle notification Stripe permettra de réessayer.');
            $this->entityManager->flush();
            return false;
        } catch (\Throwable $exception) {
            $log->markFailed('Impossible de préparer la confirmation de commande.');
            $this->entityManager->flush();
            throw $exception;
        }
        $log->markSent();
        $this->entityManager->flush();
        return true;
    }

    private function findLog(CustomerOrder $order): ?EmailLog
    {
        return $this->entityManager->getRepository(EmailLog::class)->findOneBy([
            'customerOrder' => $order, 'type' => 'order_confirmation',
        ]);
    }
}
