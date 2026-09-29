<?php

namespace App\Tests\Controller;

use App\Entity\ConnectedCard;
use App\Entity\Customer;
use App\Entity\CustomerOrder;
use App\Entity\Diagnostic;
use App\Entity\OrderItem;
use App\Entity\User;
use App\Integration\Quardlock\OfficialQuardlockServerApi;
use App\Service\CardVisitService;
use App\Service\QuardlockClientApiRelay;
use App\Service\QuardlockDiagnosticLogger;
use App\Service\QuardlockServerApiClient;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Psr\Log\NullLogger;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class CardVisitTest extends WebTestCase
{
    private string $databasePath;
    private array $previousEnv = [];
    private array $calls = [];
    private bool $accepted = true;
    private string $csrf;
    private int $cardId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->databasePath = tempnam(sys_get_temp_dir(), 'card-visit-');
        foreach (['DATABASE_URL' => 'sqlite:///'.$this->databasePath, 'FEATURE_CUSTOMER_LOGIN_ENABLED' => '0', 'CARD_LOGIN_MOBILE_NFC_ENABLED' => '0'] as $key => $value) {
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

    private function browser(?string $role = 'ROLE_OPERATOR'): KernelBrowser
    {
        $client = static::createClient();
        $client->disableReboot();
        $em = static::getContainer()->get(EntityManagerInterface::class);
        (new SchemaTool($em))->createSchema($em->getMetadataFactory()->getAllMetadata());
        $customer = (new Customer())->setFirstName('Cliente')->setLastName('Vérifiée')->setEmail('client@example.test');
        $other = (new Customer())->setFirstName('Autre')->setLastName('Confidentielle')->setEmail('other@example.test');
        $card = (new ConnectedCard())->setExternalIdentifier('TEST-VISIT')->setCustomer($customer)->setStatus('active')
            ->setCollectedAt(new \DateTimeImmutable())->setQuardlockEnrollmentStatus('enrolled')->setQuardlockTokenSerialNumber('test-serial');
        foreach ([$customer, $other, $card] as $entity) { $em->persist($entity); }
        foreach ([[$customer, 'VISIT-ORDER'], [$other, 'HIDDEN-ORDER']] as [$owner, $reference]) {
            $order = (new CustomerOrder())->setCustomer($owner)->setReference($reference)->setStatus('paid')->setTotalAmountCents(2000)->setCurrency('EUR');
            $em->persist($order);
            $em->persist((new OrderItem())->setCustomerOrder($order)->setLabel($reference.' article')->setUnitAmountCents(2000));
            $diagnostic = new Diagnostic();
            foreach (['customer' => $owner, 'performedAt' => new \DateTimeImmutable(), 'skinType' => $reference.' peau'] as $field => $value) {
                (new \ReflectionProperty(Diagnostic::class, $field))->setValue($diagnostic, $value);
            }
            $em->persist($diagnostic);
        }
        if ($role !== null) {
            $operator = (new User())->setEmail('operator@example.test')->setRoles([$role])->setIsActive(true);
            $em->persist($operator);
        }
        $em->flush();
        $this->cardId = $card->getId();
        if ($role !== null) { $client->loginUser($operator); }
        $http = new MockHttpClient(function (string $method, string $url): MockResponse {
            $endpoint = basename(parse_url($url, PHP_URL_PATH));
            $this->calls[] = $endpoint;
            return match ($endpoint) {
                'InitializeApiClientSession' => new MockResponse('{"result":"private-server-token"}'),
                'GetChallenge' => new MockResponse('test-challenge', ['response_headers' => ['WebAuthnSessionId: server-challenge-session']]),
                'AuthenticateWebAuthnToken' => new MockResponse(json_encode(['result' => $this->accepted])),
                'RevokeApiClientSessionToken' => new MockResponse('{"result":true}'),
                default => throw new \LogicException('Unexpected endpoint '.$endpoint),
            };
        });
        $api = new QuardlockServerApiClient(new OfficialQuardlockServerApi($http), 'https://example.test/client', 'test-key', $http, new NullLogger());
        $relay = new QuardlockClientApiRelay($http, 'https://example.test/client', new QuardlockDiagnosticLogger(static::getContainer()->getParameter('kernel.logs_dir')));
        static::getContainer()->set(CardVisitService::class, new CardVisitService($api, $relay, $em));
        return $client;
    }

    private function start(KernelBrowser $client): string
    {
        $crawler = $client->request('GET', '/admin/visite');
        self::assertResponseIsSuccessful();
        $this->csrf = $crawler->filter('input[name="_token"]')->attr('value');
        $client->request('POST', '/admin/visite/session', server: ['HTTP_X_CSRF_TOKEN' => $this->csrf]);
        self::assertResponseIsSuccessful();
        self::assertStringNotContainsString('private-server-token', $client->getResponse()->getContent());
        $handle = json_decode($client->getResponse()->getContent(), true)['handle'];
        $client->request('GET', '/admin/visite/defi', server: ['HTTP_X_CSRF_TOKEN' => $this->csrf, 'HTTP_CLIENTAPITOKEN' => $handle]);
        self::assertResponseIsSuccessful();
        self::assertSame('test-challenge', $client->getResponse()->getContent());
        return $handle;
    }

    private function verify(KernelBrowser $client, string $handle, array $replace = []): void
    {
        $client->request('POST', '/admin/visite/verification', server: ['HTTP_X_CSRF_TOKEN' => $this->csrf, 'HTTP_CLIENTAPITOKEN' => $handle, 'CONTENT_TYPE' => 'application/json'], content: json_encode(array_replace([
            'Id' => 'credential', 'Type' => 'public-key', 'UserHandle' => base64_encode('test-serial'),
            'ClientDataBase64Encoded' => base64_encode(json_encode(['type' => 'webauthn.get', 'origin' => 'http://localhost', 'challenge' => rtrim(strtr(base64_encode('test-challenge'), '+/', '-_'), '=')])),
            'AuthenticatorData64Encoded' => base64_encode(str_repeat("\0", 32).chr(5).str_repeat("\0", 4)),
            'Signature' => base64_encode('signature'),
        ], $replace)));
    }

    public function testVerifiedVisitIsScopedAndFinishingPreservesOperator(): void
    {
        $client = $this->browser();
        $handle = $this->start($client);
        $this->verify($client, $handle);
        self::assertResponseIsSuccessful();
        self::assertContains('AuthenticateWebAuthnToken', $this->calls);
        self::assertContains('RevokeApiClientSessionToken', $this->calls);
        $client->request('GET', '/admin/visite/dossier?customerId=999');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Cliente Vérifiée');
        self::assertSelectorTextContains('#visit-content', 'VISIT-ORDER article');
        self::assertSelectorTextContains('#visit-content', 'VISIT-ORDER peau');
        self::assertStringNotContainsString('HIDDEN-ORDER', $client->getResponse()->getContent());
        self::assertStringContainsString('no-store', $client->getResponse()->headers->get('Cache-Control'));
        self::assertSame('operator@example.test', static::getContainer()->get('security.token_storage')->getToken()->getUserIdentifier());
        $client->submitForm('Terminer la visite');
        self::assertResponseRedirects('/admin/visite', 303);
        $client->request('GET', '/admin/visite/dossier');
        self::assertResponseRedirects('/admin/visite');
        $this->verify($client, $handle);
        self::assertResponseStatusCodeSame(403);
        $client->request('GET', '/mon-compte');
        self::assertResponseRedirects('/admin/visite');
        $client->request('GET', '/admin/clients');
        self::assertResponseStatusCodeSame(403);
    }

    public function testRejectedSignatureNeverOpensVisitAndIsConsumed(): void
    {
        $client = $this->browser();
        $handle = $this->start($client);
        $this->accepted = false;
        $this->verify($client, $handle);
        self::assertResponseStatusCodeSame(403);
        self::assertContains('RevokeApiClientSessionToken', $this->calls);
        $this->accepted = true;
        $this->verify($client, $handle);
        self::assertResponseStatusCodeSame(403);
        self::assertSame(1, count(array_filter($this->calls, fn ($call) => $call === 'AuthenticateWebAuthnToken')));
        $client->request('GET', '/admin/visite/dossier');
        self::assertResponseRedirects('/admin/visite');
    }

    public function testProofWithoutUserVerificationIsRejectedBeforeProvider(): void
    {
        $client = $this->browser();
        $this->verify($client, $this->start($client), ['AuthenticatorData64Encoded' => base64_encode(str_repeat("\0", 32).chr(1).str_repeat("\0", 4))]);
        self::assertResponseStatusCodeSame(400);
        self::assertNotContains('AuthenticateWebAuthnToken', $this->calls);
        self::assertContains('RevokeApiClientSessionToken', $this->calls);
    }

    public function testSuspendedCardCannotOpenVisit(): void
    {
        $client = $this->browser();
        $handle = $this->start($client);
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->find(ConnectedCard::class, $this->cardId)->setStatus('suspended');
        $em->flush();
        $this->verify($client, $handle);
        self::assertResponseStatusCodeSame(403);
        self::assertNotContains('AuthenticateWebAuthnToken', $this->calls);
    }

    #[DataProvider('invalidClientData')]
    public function testMismatchedOriginOrChallengeIsRejected(array $clientData): void
    {
        $client = $this->browser();
        $this->verify($client, $this->start($client), ['ClientDataBase64Encoded' => base64_encode(json_encode($clientData))]);
        self::assertResponseStatusCodeSame(400);
        self::assertNotContains('AuthenticateWebAuthnToken', $this->calls);
    }

    public static function invalidClientData(): iterable
    {
        $valid = ['type' => 'webauthn.get', 'origin' => 'http://localhost', 'challenge' => rtrim(strtr(base64_encode('test-challenge'), '+/', '-_'), '=')];
        yield 'wrong origin' => [array_replace($valid, ['origin' => 'https://other.test'])];
        yield 'wrong challenge' => [array_replace($valid, ['challenge' => 'unrelated'])];
        yield 'embedded cross origin' => [array_replace($valid, ['crossOrigin' => true])];
    }

    public function testWrongHandleCannotUsePendingVerification(): void
    {
        $client = $this->browser();
        $handle = $this->start($client);
        $this->verify($client, 'wrong-handle');
        self::assertResponseStatusCodeSame(403);
        self::assertNotContains('AuthenticateWebAuthnToken', $this->calls);
        $this->verify($client, $handle);
        self::assertResponseIsSuccessful();
    }

    #[DataProvider('invalidContexts')]
    public function testExpiredOrOtherOperatorContextCannotBeUsed(string $field, mixed $value): void
    {
        $client = $this->browser();
        $handle = $this->start($client);
        $session = $client->getRequest()->getSession();
        $pending = $session->get('quardlock_card_visit_pending');
        $pending[$field] = $value;
        $session->set('quardlock_card_visit_pending', $pending);
        $session->save();
        $this->verify($client, $handle);
        self::assertResponseStatusCodeSame(403);
        self::assertContains('RevokeApiClientSessionToken', $this->calls);
        self::assertNotContains('AuthenticateWebAuthnToken', $this->calls);

        $this->verify($client, $this->start($client));
        self::assertResponseIsSuccessful();
        $session = $client->getRequest()->getSession();
        $visit = $session->get('quardlock_card_visit');
        $visit[$field] = $value;
        $session->set('quardlock_card_visit', $visit);
        $session->save();
        $client->request('GET', '/admin/visite/dossier');
        self::assertResponseRedirects('/admin/visite');
    }

    public static function invalidContexts(): iterable
    {
        yield 'expired' => ['expiresAt', 1];
        yield 'different operator' => ['operator', 'other@example.test'];
    }

    public function testNewAttemptClearsPreviousCustomerEvenIfItFails(): void
    {
        $client = $this->browser();
        $this->verify($client, $this->start($client));
        self::assertResponseIsSuccessful();
        $handle = $this->start($client);
        $this->accepted = false;
        $this->verify($client, $handle);
        self::assertResponseStatusCodeSame(403);
        $client->request('GET', '/admin/visite/dossier');
        self::assertResponseRedirects('/admin/visite');
    }

    public function testCardRevokedDuringVisitClosesDossier(): void
    {
        $client = $this->browser();
        $this->verify($client, $this->start($client));
        self::assertResponseIsSuccessful();
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->find(ConnectedCard::class, $this->cardId)->setStatus('suspended');
        $em->flush();
        $client->request('GET', '/admin/visite/dossier');
        self::assertResponseRedirects('/admin/visite');
    }

    public function testMissingCsrfAndMobileAreRejected(): void
    {
        $client = $this->browser();
        $client->request('POST', '/admin/visite/session');
        self::assertResponseStatusCodeSame(403);
        $crawler = $client->request('GET', '/admin/visite');
        $client->request('POST', '/admin/visite/session', server: ['HTTP_X_CSRF_TOKEN' => $crawler->filter('input[name="_token"]')->attr('value'), 'HTTP_SEC_CH_UA_MOBILE' => '?1']);
        self::assertResponseStatusCodeSame(503);
        self::assertSame([], $this->calls);
    }

    public function testCustomerCannotUseAnyVisitEndpoint(): void
    {
        $client = $this->browser('ROLE_USER');
        foreach (['/admin/visite', '/admin/visite/dossier', '/admin/visite/defi'] as $url) {
            $client->request('GET', $url);
            self::assertResponseStatusCodeSame(403);
        }
        foreach (['/admin/visite/session', '/admin/visite/verification', '/admin/visite/terminer'] as $url) {
            $client->request('POST', $url);
            self::assertResponseStatusCodeSame(403);
        }
    }

    public function testAnonymousIsRedirectedToLogin(): void
    {
        $client = $this->browser(null);
        $client->request('GET', '/admin/visite');
        self::assertResponseRedirects('http://localhost/login');
    }

    public function testAdminCanIdentifyCustomer(): void
    {
        $client = $this->browser('ROLE_ADMIN');
        $this->verify($client, $this->start($client));
        self::assertResponseIsSuccessful();
        $client->request('GET', '/admin/visite/dossier');
        self::assertResponseIsSuccessful();
    }
}
