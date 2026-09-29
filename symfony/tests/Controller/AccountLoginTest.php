<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AccountLoginTest extends WebTestCase
{
    public function testLoginPageOffersOnlyEmailAndPassword(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('input[name="_username"]');
        self::assertSelectorExists('input[name="_password"]');
        self::assertSelectorNotExists('#card-login-button');
        self::assertStringNotContainsString('navigator.credentials', (string) $client->getResponse()->getContent());
    }

    public function testMobileCannotInitializeCardLoginWhenNfcIsDisabled(): void
    {
        $client = static::createClient();
        $client->request('POST', '/login/carte/session', server: [
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X)',
        ]);

        self::assertResponseStatusCodeSame(503);
        self::assertJsonStringEqualsJsonString(
            '{"success":false,"message":"La connexion NFC biométrique sur mobile est en cours de validation. Utilisez votre email et votre mot de passe."}',
            (string) $client->getResponse()->getContent(),
        );
    }
}
