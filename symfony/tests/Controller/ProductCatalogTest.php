<?php

namespace App\Tests\Controller;

use App\Entity\ConnectedCard;
use App\Entity\CustomerOrder;
use App\Entity\Product;
use App\Entity\User;
use App\Service\CardOrderService;
use App\Service\ProductCatalog;
use App\Service\StripeGateway;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ProductCatalogTest extends WebTestCase
{
    private string $databasePath;
    private array $previousEnv = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->databasePath = tempnam(sys_get_temp_dir(), 'catalog-');
        foreach (['DATABASE_URL' => 'sqlite:///'.$this->databasePath, 'FEATURE_CARD_SALES_ENABLED' => '1',
            'MAILER_DSN' => 'null://null', 'MAILER_FROM' => 'no-reply@example.test', 'STRIPE_MODE' => 'test',
            'STRIPE_SECRET_KEY' => 'sk_test_local_unit_only', 'STRIPE_WEBHOOK_SECRET' => 'whsec_local_unit_only',
            'STRIPE_RETURN_BASE_URL' => 'https://example.test'] as $key => $value) {
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

    private function database(): EntityManagerInterface
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);
        (new SchemaTool($em))->createSchema($em->getMetadataFactory()->getAllMetadata());
        return $em;
    }

    private function data(): array
    {
        return ['sku' => 'PACK', 'name' => 'Carte + 2 diagnostics', 'type' => 'card_diagnostic', 'price' => '120,50',
            'description' => 'Carte et rendez-vous en boutique.', 'diagnosticCount' => '2', 'isActive' => '1',
            'shippingEnabled' => '1', 'shippingPrice' => '8,50', 'shippingCountries' => 'FR, BE', 'shippingTerms' => 'Sous 5 jours ouvrés.',
            'pickupEnabled' => '1', 'pickupPrice' => '0', 'pickupTerms' => 'Au centre de Paris, sur rendez-vous.'];
    }

    private function product(): Product
    {
        $product = new Product();
        static::getContainer()->get(ProductCatalog::class)->save($product, $this->data());
        return $product;
    }

    private function payload(Product $product, string $mode): array
    {
        $catalog = static::getContainer()->get(ProductCatalog::class);
        return ['productId' => (string) $product->getId(), 'deliveryMode' => $mode,
            'quote' => $catalog->fingerprint($catalog->quote($product, $mode)),
            'firstName' => 'Test', 'lastName' => 'Cliente', 'email' => 'test@example.test',
            'unitAmount' => '1', 'total' => '1'];
    }

    private function pay(CustomerOrder $order): void
    {
        $orders = static::getContainer()->get(CardOrderService::class);
        $orders->recordSession($order, 'cs_test_catalog');
        self::assertTrue($orders->handleEvent(['livemode' => false, 'type' => 'checkout.session.completed', 'data' => ['object' => [
            'id' => 'cs_test_catalog', 'livemode' => false, 'mode' => 'payment', 'payment_status' => 'paid',
            'client_reference_id' => $order->getReference(), 'amount_total' => $order->getTotalAmountCents(), 'currency' => 'eur',
            'metadata' => ['application' => 'beaute_inee_card', 'order_reference' => $order->getReference()],
        ]]], false));
    }

    public function testAdminCanCreateAndEditOffersWhileOperatorCannot(): void
    {
        $client = static::createClient();
        $em = $this->database();
        $admin = (new User())->setEmail('admin@example.test')->setRoles(['ROLE_ADMIN'])->setIsActive(true);
        $operator = (new User())->setEmail('operator@example.test')->setRoles(['ROLE_OPERATOR'])->setIsActive(true);
        $em->persist($admin); $em->persist($operator); $em->flush();
        $client->loginUser($operator);
        foreach (['GET', 'POST'] as $method) {
            $client->request($method, '/admin/produits/nouveau');
            self::assertResponseStatusCodeSame(403);
        }
        $client->loginUser($admin);
        $client->request('POST', '/admin/produits/nouveau', $this->data());
        self::assertResponseStatusCodeSame(403);
        $client->request('GET', '/admin/produits/nouveau');
        self::assertResponseIsSuccessful();
        $client->submitForm('Enregistrer l’offre', $this->data());
        self::assertResponseRedirects('/admin/produits');
        $client->followRedirect();
        self::assertSelectorTextContains('.admin-table', '120,50');
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $product = $em->getRepository(Product::class)->findOneBy(['sku' => 'PACK']);
        self::assertSame(2, $product->getDiagnosticCount());
        $client->request('GET', '/admin/produits/'.$product->getId().'/modifier');
        self::assertResponseIsSuccessful();
        $client->submitForm('Enregistrer l’offre', ['price' => '-1']);
        self::assertResponseStatusCodeSame(422);
        $client->submitForm('Enregistrer l’offre', ['price' => '135.90', 'isActive' => false, 'pickupEnabled' => false]);
        self::assertResponseRedirects('/admin/produits');
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $product = $em->getRepository(Product::class)->findOneBy(['sku' => 'PACK']);
        self::assertSame(13590, $product->getUnitAmountCents());
        self::assertFalse($product->isActive());
        self::assertArrayNotHasKey('pickup', $product->getDeliveryOptions());
        $client->request('GET', '/admin/produits/99999/modifier');
        self::assertResponseStatusCodeSame(404);
    }

    public function testPickupCheckoutUsesSnapshotAndCreatesExactlyOneCard(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $em = $this->database();
        $product = $this->product();
        $gateway = $this->createMock(StripeGateway::class);
        $gateway->method('isConfigured')->willReturn(true);
        $gateway->expects(self::once())->method('createSession')->willReturnCallback(function (CustomerOrder $order): array {
            self::assertSame(12050, $order->getTotalAmountCents());
            self::assertNull($order->getShippingAddress());
            self::assertSame(2, $order->getFulfillment()['diagnosticCount']);
            return ['id' => 'cs_test_catalog', 'url' => 'https://checkout.stripe.com/test'];
        });
        static::getContainer()->set(StripeGateway::class, $gateway);
        $client->request('GET', '/commande/carte', ['productId' => $product->getId(), 'deliveryMode' => 'pickup']);
        self::assertResponseIsSuccessful();
        self::assertSelectorNotExists('input[name="addressLine1"]');
        self::assertSelectorTextContains('.workflow-muted', '120,50');
        $client->submitForm('Payer 120,50 € avec Stripe', ['firstName' => 'Test', 'lastName' => 'Cliente', 'email' => 'test@example.test']);
        self::assertResponseRedirects('https://checkout.stripe.com/test');
        $order = $em->getRepository(CustomerOrder::class)->findOneBy([]);
        $product->setName('Changement')->setUnitAmountCents(99900)->setType('diagnostic')->setDiagnosticCount(9)->setDeliveryOptions([])->setIsActive(false);
        $em->flush();
        $this->pay($order);
        $this->pay($order);
        self::assertSame(1, $em->getRepository(ConnectedCard::class)->count([]));
        self::assertSame(12050, $order->getTotalAmountCents());
        self::assertSame('Carte + 2 diagnostics', $order->getItems()->first()->getLabel());
        self::assertSame('Au centre de Paris, sur rendez-vous.', $order->getFulfillment()['delivery']['terms']);
        self::assertEmailTextBodyContains(self::getMailerMessage(), '2 diagnostic(s) inclus');
        self::assertEmailHtmlBodyContains(self::getMailerMessage(), 'Retrait en boutique');
        $client->request('GET', '/commande/carte/confirmation');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('main', '2 diagnostic(s) inclus');
    }

    public function testShippingPriceAndCountryAreValidatedOnServer(): void
    {
        static::createClient(); $em = $this->database(); $product = $this->product();
        $orders = static::getContainer()->get(CardOrderService::class);
        $payload = $this->payload($product, 'shipping') + ['addressLine1' => '1 rue de Test', 'postalCode' => '75001', 'city' => 'Paris', 'country' => 'CH'];
        try { $orders->create($payload); self::fail('Country must be rejected.'); } catch (\InvalidArgumentException) {}
        self::assertSame(0, $em->getRepository(CustomerOrder::class)->count([]));
        $payload['country'] = 'BE';
        $order = $orders->create($payload);
        self::assertSame(12900, $order->getTotalAmountCents());
        self::assertSame('BE', $order->getShippingAddress()['country']);
    }

    public function testStaleOfferDisabledOfferAndDisabledDeliveryAreRejected(): void
    {
        static::createClient(); $em = $this->database(); $product = $this->product();
        $orders = static::getContainer()->get(CardOrderService::class);
        $payload = $this->payload($product, 'pickup');
        $product->setUnitAmountCents(13000); $em->flush();
        try { $orders->create($payload); self::fail('Stale price must be rejected.'); } catch (\InvalidArgumentException $e) {
            self::assertStringContainsString('L’offre a changé', $e->getMessage());
        }
        $payload = $this->payload($product, 'pickup');
        $product->setIsActive(false); $em->flush();
        try { $orders->create($payload); self::fail('Inactive offer must be rejected.'); } catch (\InvalidArgumentException) {}
        $product->setIsActive(true)->setDeliveryOptions([]); $em->flush();
        try { $orders->create($payload); self::fail('Disabled delivery must be rejected.'); } catch (\InvalidArgumentException) {}
        self::assertSame(0, $em->getRepository(CustomerOrder::class)->count([]));
    }

    public function testDiagnosticOnlyDoesNotCreateCardEvenIfProductChangesLater(): void
    {
        static::createClient(); $em = $this->database(); $product = $this->product();
        $product->setType('diagnostic'); $em->flush();
        $order = static::getContainer()->get(CardOrderService::class)->create($this->payload($product, 'pickup'));
        $product->setType('card_diagnostic'); $em->flush();
        $this->pay($order);
        self::assertSame(0, $em->getRepository(ConnectedCard::class)->count([]));
        self::assertNotNull($order->getPaidAt());
    }

    public function testInvalidCatalogConfigurationIsNotPersisted(): void
    {
        static::createClient(); $em = $this->database();
        $catalog = static::getContainer()->get(ProductCatalog::class);
        foreach ([['price' => '1.234'], ['price' => '0'], ['diagnosticCount' => '0'], ['shippingCountries' => 'XX'],
            ['pickupTerms' => ''], ['shippingEnabled' => '', 'pickupEnabled' => '']] as $invalid) {
            try { $catalog->save(new Product(), array_replace($this->data(), $invalid)); self::fail('Invalid offer accepted.'); } catch (\InvalidArgumentException) {}
        }
        self::assertSame(0, $em->getRepository(Product::class)->count([]));
        $this->product();
        try { $catalog->save(new Product(), $this->data()); self::fail('Duplicate SKU accepted.'); } catch (\InvalidArgumentException) {}
        self::assertSame(1, $em->getRepository(Product::class)->count([]));
    }
}
