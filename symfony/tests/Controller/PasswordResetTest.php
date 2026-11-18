<?php

namespace App\Tests\Controller;

use App\Entity\EmailLog;
use App\Entity\PasswordResetToken;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class PasswordResetTest extends WebTestCase
{
    private string $databasePath;
    private array $previousEnv = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->databasePath = tempnam(sys_get_temp_dir(), 'password-reset-');
        $this->setEnv(['DATABASE_URL' => 'sqlite:///'.$this->databasePath, 'FEATURE_CUSTOMER_LOGIN_ENABLED' => '1', 'MAILER_DSN' => 'null://null']);
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

    private function setEnv(array $values): void
    {
        foreach ($values as $key => $value) {
            if (!array_key_exists($key, $this->previousEnv)) {
                $this->previousEnv[$key] = [$_ENV[$key] ?? null, $_SERVER[$key] ?? null];
            }
            $_ENV[$key] = $_SERVER[$key] = $value;
        }
    }

    private function prepareDatabase(): EntityManagerInterface
    {
        static::getContainer()->get('cache.password_reset_limiter')->clear();
        $em = static::getContainer()->get(EntityManagerInterface::class);
        (new SchemaTool($em))->createSchema($em->getMetadataFactory()->getAllMetadata());

        return $em;
    }

    private function createUser(EntityManagerInterface $em, string $email, array $roles = [], bool $active = true, ?string $password = 'ancien-mot-de-passe'): User
    {
        $user = (new User())->setEmail($email)->setRoles($roles)->setIsActive($active);
        if ($password !== null) {
            $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
            $user->setPassword($hasher->hashPassword($user, $password));
        }
        $em->persist($user);
        $em->flush();

        return $user;
    }

    private function requestReset(KernelBrowser $client, string $email): void
    {
        $crawler = $client->request('GET', '/mot-de-passe-oublie');
        $client->submit($crawler->selectButton('Envoyer le lien')->form(['email' => $email]));
    }

    private function extractResetPath(): string
    {
        $email = self::getMailerMessage();
        self::assertNotNull($email);
        self::assertSame(1, preg_match('#https?://[^/]+(/mot-de-passe-oublie/[a-f0-9]{64})#', (string) $email->getTextBody(), $matches));

        return $matches[1];
    }

    public function testLoginPageLinksToPasswordReset(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('a[href="/mot-de-passe-oublie"]');
    }

    public function testActiveUserCanResetPasswordOnceWithEmailedLink(): void
    {
        $client = static::createClient();
        $em = $this->prepareDatabase();
        $this->createUser($em, 'admin@example.test', ['ROLE_ADMIN']);

        $this->requestReset($client, ' Admin@Example.test ');
        self::assertResponseRedirects('/mot-de-passe-oublie/email-envoye');
        self::assertEmailCount(1);
        $resetPath = $this->extractResetPath();

        $client->followRedirect();
        self::assertResponseIsSuccessful();

        $crawler = $client->request('GET', $resetPath);
        self::assertResponseIsSuccessful();
        $client->submit($crawler->selectButton('Enregistrer')->form([
            'password' => 'nouveau-mot-de-passe',
            'passwordConfirmation' => 'nouveau-mot-de-passe',
        ]));
        self::assertResponseRedirects('/login');

        $client->followRedirect();
        self::assertSelectorTextContains('.account-success', 'Votre mot de passe a été modifié');

        $crawler = $client->request('GET', '/login');
        $client->submit($crawler->selectButton('Se connecter')->form([
            '_username' => 'admin@example.test',
            '_password' => 'nouveau-mot-de-passe',
        ]));
        self::assertResponseRedirects('/mon-compte');

        $client->request('GET', $resetPath);
        self::assertResponseStatusCodeSame(410);

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $log = $em->getRepository(EmailLog::class)->findOneBy(['type' => 'password_reset']);
        self::assertNotNull($log);
        self::assertSame('admin@example.test', $log->getRecipient());
    }

    public function testUnknownEmailGetsSameResponseWithoutEmail(): void
    {
        $client = static::createClient();
        $this->prepareDatabase();

        $this->requestReset($client, 'inconnue@example.test');

        self::assertResponseRedirects('/mot-de-passe-oublie/email-envoye');
        self::assertEmailCount(0);
    }

    public function testDeactivatedAccountIsNotReset(): void
    {
        $client = static::createClient();
        $em = $this->prepareDatabase();
        $this->createUser($em, 'desactivee@example.test', active: false);

        $this->requestReset($client, 'desactivee@example.test');

        self::assertResponseRedirects('/mot-de-passe-oublie/email-envoye');
        self::assertEmailCount(0);
    }

    public function testNeverActivatedAccountReceivesActivationEmail(): void
    {
        $client = static::createClient();
        $em = $this->prepareDatabase();
        $this->createUser($em, 'nouvelle@example.test', active: false, password: null);

        $this->requestReset($client, 'nouvelle@example.test');

        self::assertResponseRedirects('/mot-de-passe-oublie/email-envoye');
        self::assertEmailCount(1);
        self::assertStringContainsString('/account/activate/', (string) self::getMailerMessage()->getTextBody());
    }

    public function testCustomerIsNotResetWhileCustomerLoginIsDisabledButStaffIs(): void
    {
        $this->setEnv(['FEATURE_CUSTOMER_LOGIN_ENABLED' => '0']);
        $client = static::createClient();
        $em = $this->prepareDatabase();
        $this->createUser($em, 'cliente@example.test');
        $this->createUser($em, 'operatrice@example.test', ['ROLE_OPERATOR']);

        $this->requestReset($client, 'cliente@example.test');
        self::assertEmailCount(0);

        $this->requestReset($client, 'operatrice@example.test');
        self::assertEmailCount(1);
    }

    public function testExpiredTokenAndMismatchedPasswordsAreRejected(): void
    {
        $client = static::createClient();
        $em = $this->prepareDatabase();
        $user = $this->createUser($em, 'cliente@example.test');

        $em->persist((new PasswordResetToken())->setUser($user)->setTokenHash(hash('sha256', str_repeat('a', 64)))->setExpiresAt(new \DateTimeImmutable('-1 minute')));
        $em->flush();
        $client->request('GET', '/mot-de-passe-oublie/'.str_repeat('a', 64));
        self::assertResponseStatusCodeSame(410);

        $this->requestReset($client, 'cliente@example.test');
        $crawler = $client->request('GET', $this->extractResetPath());
        $client->submit($crawler->selectButton('Enregistrer')->form([
            'password' => 'nouveau-mot-de-passe',
            'passwordConfirmation' => 'autre-mot-de-passe',
        ]));
        self::assertResponseStatusCodeSame(422);
    }

    public function testRequestsAreRateLimitedByIp(): void
    {
        $client = static::createClient();
        $client->disableReboot();
        $this->prepareDatabase();

        for ($i = 0; $i < 10; ++$i) {
            $this->requestReset($client, sprintf('inconnue%d@example.test', $i));
            self::assertResponseRedirects('/mot-de-passe-oublie/email-envoye');
        }

        $this->requestReset($client, 'inconnue-de-trop@example.test');
        self::assertResponseStatusCodeSame(429);
    }
}
