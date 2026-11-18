<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\ConnectedCard;
use App\Service\AccountActivationService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class TeamAccessTest extends WebTestCase
{
    private string $databasePath;
    private array $previousEnv = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->databasePath = tempnam(sys_get_temp_dir(), 'team-access-');
        foreach (['DATABASE_URL' => 'sqlite:///'.$this->databasePath, 'FEATURE_CUSTOMER_LOGIN_ENABLED' => '0', 'MAILER_DSN' => 'null://null'] as $key => $value) {
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

    private function prepareDatabase(): EntityManagerInterface
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);
        (new SchemaTool($em))->createSchema($em->getMetadataFactory()->getAllMetadata());

        return $em;
    }

    public function testOperatorCanUseOrdersAndCardsButCannotAccessAdministration(): void
    {
        $client = static::createClient();
        $em = $this->prepareDatabase();
        $operator = (new User())->setEmail('operator@example.test')->setRoles(['ROLE_OPERATOR'])->setIsActive(true);
        $em->persist($operator);
        $card = (new ConnectedCard())->setExternalIdentifier('TEST-CARD');
        $em->persist($card);
        $em->flush();
        $cardId = $card->getId();
        $client->loginUser($operator);
        $client->request('GET', '/mon-compte');
        self::assertResponseRedirects('/admin/commandes');
        foreach (['/admin/commandes', '/admin/cartes'] as $path) {
            $client->request('GET', $path);
            self::assertResponseIsSuccessful();
            self::assertSelectorNotExists('a[href="/admin/equipe"]');
        }
        $client->request('GET', '/admin/cartes/'.$cardId);
        self::assertResponseIsSuccessful();
        $client->submitForm('Démarrer la configuration technique');
        self::assertResponseRedirects('/admin/cartes/'.$cardId);
        $em = static::getContainer()->get(EntityManagerInterface::class);
        self::assertSame('configuration_in_progress', $em->find(ConnectedCard::class, $cardId)->getStatus());
        $client->request('GET', '/admin/cartes/'.$cardId.'/quardlock/enrolement');
        self::assertResponseIsSuccessful();
        foreach (['/admin/equipe', '/admin/clients', '/admin/quardlock', '/admin/diagnostics', '/admin/emails'] as $path) {
            $client->request('GET', $path);
            self::assertResponseStatusCodeSame(403);
        }
        $client->request('POST', '/admin/equipe/inviter', ['email' => 'intruder@example.test', 'role' => 'ROLE_ADMIN']);
        self::assertResponseStatusCodeSame(403);
    }

    public function testAdministratorCanInviteAndActivateOperatorWithCustomerLoginDisabled(): void
    {
        $client = static::createClient();
        $em = $this->prepareDatabase();
        $admin = (new User())->setEmail('admin@example.test')->setRoles(['ROLE_ADMIN'])->setIsActive(true);
        $em->persist($admin);
        $em->flush();
        $client->loginUser($admin);
        $client->request('GET', '/admin/equipe');
        self::assertResponseIsSuccessful();
        $client->submitForm('Envoyer l’invitation', ['email' => 'new@example.test', 'role' => 'ROLE_OPERATOR']);
        self::assertResponseRedirects('/admin/equipe');
        $client->request('GET', '/admin/equipe');
        $client->submitForm('Envoyer l’invitation', ['email' => 'second-admin@example.test', 'role' => 'ROLE_ADMIN']);
        self::assertResponseRedirects('/admin/equipe');
        $client->followRedirect();
        self::assertSelectorTextContains('.team-table', 'second-admin@example.test');
        $client->submitForm('Envoyer l’invitation', ['email' => 'new@example.test', 'role' => 'ROLE_ADMIN']);
        $client->followRedirect();
        self::assertSelectorTextContains('.admin-flash--error', 'Cette adresse possède déjà un compte');
        $em = static::getContainer()->get(EntityManagerInterface::class);
        self::assertContains('ROLE_ADMIN', $em->getRepository(User::class)->findOneBy(['email' => 'second-admin@example.test'])->getRoles());
        $operator = $em->getRepository(User::class)->findOneBy(['email' => 'new@example.test']);
        self::assertInstanceOf(User::class, $operator);
        self::assertFalse($operator->isActive());
        self::assertContains('ROLE_OPERATOR', $operator->getRoles());
        self::assertNotContains('ROLE_ADMIN', $operator->getRoles());
        $token = static::getContainer()->get(AccountActivationService::class)->issueToken($operator);
        $em->flush();
        $client->request('GET', '/logout');
        $client->request('GET', '/account/activate/'.$token);
        self::assertResponseIsSuccessful();
        $client->submitForm('Activer mon compte', ['password' => 'Test-password-123', 'passwordConfirmation' => 'Test-password-123']);
        self::assertResponseRedirects('/login');
        $client->followRedirect();
        $client->submitForm('Se connecter', ['_username' => 'new@example.test', '_password' => 'Test-password-123']);
        self::assertResponseRedirects('/mon-compte');
        $client->followRedirect();
        self::assertResponseRedirects('/admin/commandes');
        $client->request('GET', '/account/activate/'.$token);
        self::assertSelectorNotExists('input[name="password"]');
    }

    public function testCustomerCannotAccessTeamAndInviteRequiresCsrf(): void
    {
        $client = static::createClient();
        $em = $this->prepareDatabase();
        $customer = (new User())->setEmail('customer@example.test')->setIsActive(true);
        $admin = (new User())->setEmail('admin@example.test')->setRoles(['ROLE_ADMIN'])->setIsActive(true);
        $em->persist($customer);
        $em->persist($admin);
        $em->flush();
        $client->loginUser($customer);
        $client->request('GET', '/admin/equipe');
        self::assertResponseStatusCodeSame(403);
        $client->loginUser($admin);
        $client->request('POST', '/admin/equipe/inviter', ['email' => 'new@example.test', 'role' => 'ROLE_ADMIN']);
        self::assertResponseStatusCodeSame(403);
    }
}
