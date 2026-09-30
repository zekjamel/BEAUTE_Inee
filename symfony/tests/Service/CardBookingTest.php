<?php

namespace App\Tests\Service;

use App\Entity\ConnectedCard;
use App\Entity\Customer;
use App\Entity\CustomerOrder;
use App\Service\CardBooking;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;

final class CardBookingTest extends KernelTestCase
{
    private string $database;
    private array $previous;

    protected function setUp(): void
    {
        $this->database = tempnam(sys_get_temp_dir(), 'booking-test-');
        $this->previous = [$_ENV['DATABASE_URL'] ?? null, $_SERVER['DATABASE_URL'] ?? null];
        $_ENV['DATABASE_URL'] = $_SERVER['DATABASE_URL'] = 'sqlite:///'.$this->database;
        self::bootKernel();
        $em = self::getContainer()->get(EntityManagerInterface::class);
        (new SchemaTool($em))->createSchema($em->getMetadataFactory()->getAllMetadata());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        foreach (['env', 'server'] as $i => $key) {
            if ($key === 'env') {
                if ($this->previous[$i] === null) { unset($_ENV['DATABASE_URL']); } else { $_ENV['DATABASE_URL'] = $this->previous[$i]; }
            } else {
                if ($this->previous[$i] === null) { unset($_SERVER['DATABASE_URL']); } else { $_SERVER['DATABASE_URL'] = $this->previous[$i]; }
            }
        }
        unlink($this->database);
    }

    public function testBookingRenderingTracksPaymentAndCardState(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $twig = self::getContainer()->get(Environment::class);
        $booking = self::getContainer()->get(CardBooking::class);
        $customer = (new Customer())->setFirstName('Test')->setLastName('Cliente')->setEmail('test@example.test');
        $order = (new CustomerOrder())->setCustomer($customer)->setReference('BOOKING-TEST')->setTotalAmountCents(7700);
        $card = (new ConnectedCard())->setCustomer($customer)->setSourceOrder($order)->setExternalIdentifier('BOOKING-CARD')->setStatus('ordered');
        foreach ([$customer, $order, $card] as $entity) { $em->persist($entity); }
        $em->flush();
        self::assertFalse($booking->forCard($card));
        self::assertFalse($booking->forOrder(null));
        self::assertStringNotContainsString('Prendre rendez-vous pour activer ma carte', $twig->load('checkout/confirmation.html.twig')->renderBlock('body', ['order' => $order]));
        $order->markPaid();
        $em->flush();
        foreach (['ordered', 'configuration_in_progress', 'ready_for_collection'] as $status) {
            $card->setStatus($status);
            self::assertTrue($booking->forCard($card));
        }
        foreach (['checkout/confirmation.html.twig', 'emails/order_confirmation.html.twig', 'emails/order_confirmation.txt.twig'] as $template) {
            $body = $template === 'checkout/confirmation.html.twig'
                ? $twig->load($template)->renderBlock('body', ['order' => $order])
                : $twig->render($template, ['order' => $order, 'items' => []]);
            self::assertStringContainsString(CardBooking::URL, $body);
            self::assertStringContainsString('Prendre rendez-vous pour activer ma carte', $body);
            self::assertStringContainsString('présence', $body);
            self::assertStringNotContainsString(CardBooking::URL.'?', $body);
        }
        self::assertStringContainsString('Prendre rendez-vous pour activer ma carte', $twig->load('account/cards.html.twig')->renderBlock('account_content', ['cards' => [$card]]));
        foreach (['active', 'suspended', 'initialized', 'configured', 'cancelled'] as $status) {
            $card->setStatus($status);
            self::assertFalse($booking->forCard($card));
            self::assertStringNotContainsString('Prendre rendez-vous pour activer ma carte', $twig->load('account/cards.html.twig')->renderBlock('account_content', ['cards' => [$card]]));
            self::assertStringNotContainsString('Prendre rendez-vous pour activer ma carte', $twig->load('checkout/confirmation.html.twig')->renderBlock('body', ['order' => $order]));
            self::assertStringNotContainsString(CardBooking::URL, $twig->render('emails/order_confirmation.txt.twig', ['order' => $order, 'items' => []]));
        }
        $card->setStatus('ordered');
        $order->setStatus('refunded');
        self::assertFalse($booking->forCard($card));
        $order->setStatus('paid');
        $card->setCollectedAt(new \DateTimeImmutable());
        self::assertFalse($booking->forCard($card));
    }
}
