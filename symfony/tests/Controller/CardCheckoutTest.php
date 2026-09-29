<?php

namespace App\Tests\Controller;

use App\Entity\ConnectedCard;
use App\Entity\CustomerOrder;
use App\Entity\Payment;
use App\Service\CardOrderService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CardCheckoutTest extends WebTestCase
{
    private string $databasePath;
    private array $previousEnv = [];
    private const WEBHOOK_SECRET = 'whsec_local_unit_test_only';

    protected function setUp(): void
    {
        parent::setUp();
        $this->databasePath = tempnam(sys_get_temp_dir(), 'card-checkout-');
        foreach ([
            'DATABASE_URL' => 'sqlite:///'.$this->databasePath,
            'FEATURE_CARD_SALES_ENABLED' => '1',
            'STRIPE_SECRET_KEY' => 'sk_test_local_unit_test_only',
            'STRIPE_WEBHOOK_SECRET' => self::WEBHOOK_SECRET,
            'STRIPE_RETURN_BASE_URL' => 'https://example.test',
        ] as $key => $value) {
            $this->previousEnv[$key] = [$_ENV[$key] ?? null, $_SERVER[$key] ?? null];
            $_ENV[$key] = $_SERVER[$key] = $value;
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        foreach ($this->previousEnv as $key => [$env, $server]) {
            if ($env === null) { unset($_ENV[$key]); } else { $_ENV[$key] = $env; }
            if ($server === null) { unset($_SERVER[$key]); } else { $_SERVER[$key] = $server; }
        }
        @unlink($this->databasePath);
    }

    private function prepareOrder(): CustomerOrder
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);
        (new SchemaTool($em))->createSchema($em->getMetadataFactory()->getAllMetadata());
        $orders = static::getContainer()->get(CardOrderService::class);
        $order = $orders->create([
            'firstName' => 'Test', 'lastName' => 'Cliente', 'email' => 'client@example.test',
            'phone' => '', 'addressLine1' => '1 rue de Test', 'postalCode' => '75001', 'city' => 'Paris', 'country' => 'FR',
            'amount' => 1,
        ]);
        $orders->recordSession($order, 'cs_test_local');
        return $order;
    }

    private function event(CustomerOrder $order): array
    {
        return ['id' => 'evt_local', 'object' => 'event', 'livemode' => false, 'type' => 'checkout.session.completed', 'data' => ['object' => [
            'id' => 'cs_test_local', 'object' => 'checkout.session', 'livemode' => false, 'mode' => 'payment',
            'client_reference_id' => $order->getReference(), 'amount_total' => 7700, 'currency' => 'eur', 'payment_status' => 'paid',
            'metadata' => ['application' => 'beaute_inee_card', 'order_reference' => $order->getReference()],
        ]]];
    }

    private function sendEvent($client, array $event, ?int $timestamp = null): void
    {
        $body = json_encode($event, JSON_THROW_ON_ERROR);
        $timestamp ??= time();
        $signature = hash_hmac('sha256', $timestamp.'.'.$body, self::WEBHOOK_SECRET);
        $client->request('POST', '/stripe/webhook', server: ['HTTP_STRIPE_SIGNATURE' => 't='.$timestamp.',v1='.$signature, 'CONTENT_TYPE' => 'application/json'], content: $body);
    }

    public function testSignedPaymentCreatesOneCardAndReplayDoesNotResetConfiguration(): void
    {
        $client = static::createClient();
        $order = $this->prepareOrder();
        self::assertSame(7700, $order->getTotalAmountCents());
        $event = $this->event($order);
        $this->sendEvent($client, $event);
        self::assertResponseStatusCodeSame(204);
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $card = $em->getRepository(ConnectedCard::class)->findOneBy([]);
        self::assertInstanceOf(ConnectedCard::class, $card);
        self::assertStringStartsWith('TEST-BI-CARTE-', $card->getExternalIdentifier());
        self::assertSame('CardLab', $card->getProvider());
        self::assertSame('succeeded', $em->getRepository(Payment::class)->findOneBy([])->getStatus());
        $card->setStatus('configuration_in_progress');
        $card->getSourceOrder()->setStatus('configuration_in_progress');
        $em->flush();
        $this->sendEvent($client, $event);
        self::assertResponseStatusCodeSame(204);
        $em = static::getContainer()->get(EntityManagerInterface::class);
        self::assertSame(1, $em->getRepository(ConnectedCard::class)->count([]));
        self::assertSame('configuration_in_progress', $em->getRepository(CustomerOrder::class)->findOneBy([])->getStatus());
    }

    public function testUnpaidEventDoesNotCreateCardAndDelayedPaymentDoes(): void
    {
        $client = static::createClient();
        $event = $this->event($this->prepareOrder());
        $event['data']['object']['payment_status'] = 'unpaid';
        $this->sendEvent($client, $event);
        self::assertResponseStatusCodeSame(204);
        self::assertSame(0, static::getContainer()->get(EntityManagerInterface::class)->getRepository(ConnectedCard::class)->count([]));
        $event['type'] = 'checkout.session.async_payment_succeeded';
        $event['data']['object']['payment_status'] = 'paid';
        $this->sendEvent($client, $event);
        self::assertResponseStatusCodeSame(204);
        self::assertSame(1, static::getContainer()->get(EntityManagerInterface::class)->getRepository(ConnectedCard::class)->count([]));
    }

    public function testBadSignaturesAndWrongAmountsAreRejected(): void
    {
        $client = static::createClient();
        $event = $this->event($this->prepareOrder());
        $client->request('POST', '/stripe/webhook', content: json_encode($event));
        self::assertResponseStatusCodeSame(400);
        $this->sendEvent($client, $event, time() - 600);
        self::assertResponseStatusCodeSame(400);
        $event['data']['object']['amount_total'] = 1;
        $this->sendEvent($client, $event);
        self::assertResponseStatusCodeSame(400);
        $client->request('GET', '/commande/carte');
        self::assertResponseIsSuccessful();
        self::assertSame(0, static::getContainer()->get(EntityManagerInterface::class)->getRepository(ConnectedCard::class)->count([]));
    }

    public function testCheckoutUsesProductionLabelsAndSimulationIsNotPublic(): void
    {
        $client = static::createClient();
        $client->request('GET', '/commande/carte');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Commander ma carte');
        self::assertSelectorTextContains('.workflow-muted', '77,00');
        self::assertSelectorExists('input[name="email"][value=""]');
        $client->request('POST', '/commande/carte');
        self::assertResponseStatusCodeSame(400);
        $client->request('GET', '/dev/card-checkout');
        self::assertResponseRedirects('http://localhost/login');
    }
}
