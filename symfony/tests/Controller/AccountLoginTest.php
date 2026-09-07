<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AccountLoginTest extends WebTestCase
{
    public function testLoginPageExplainsDesktopCardFlowAndKeepsMobileNfcDisabled(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            '#card-login-intro',
            'Après trois empreintes non reconnues, votre PIN personnel pourra vous être demandé.',
        );
        self::assertSelectorExists('#card-login-button[data-mobile-nfc-enabled="0"]');
        self::assertSelectorTextContains(
            '#card-login-mobile-notice',
            'La connexion NFC biométrique sur mobile est en cours de validation.',
        );
        self::assertSelectorTextContains(
            '.card-login-alternative',
            'Connectez-vous avec votre email et votre mot de passe.',
        );

        $html = (string) $client->getResponse()->getContent();
        self::assertStringContainsString("userVerification: 'required'", $html);
        self::assertStringNotContainsString('webauthn.io', $html);
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
