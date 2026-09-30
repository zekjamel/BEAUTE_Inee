<?php

namespace App\Tests\Service;

use App\Entity\Customer;
use App\Entity\CustomerOrder;
use App\Service\CardReference;
use App\Service\StripeGateway;
use PHPUnit\Framework\TestCase;
use Stripe\ApiRequestor;
use Stripe\HttpClient\ClientInterface;
use Stripe\HttpClient\CurlClient;

final class StripeGatewayTest extends TestCase
{
    public function testCheckoutSendsTrustedPricesAndIdempotencyKeyToStripe(): void
    {
        $http = $this->createMock(ClientInterface::class);
        $http->expects(self::once())->method('request')->willReturnCallback(function ($method, $url, $headers, $params) {
            self::assertSame('post', $method);
            self::assertSame('https://api.stripe.com/v1/checkout/sessions', $url);
            self::assertSame(7000, $params['line_items'][0]['price_data']['unit_amount']);
            self::assertSame(700, $params['line_items'][1]['price_data']['unit_amount']);
            self::assertSame(CardReference::PRODUCT_NAME, $params['line_items'][0]['price_data']['product_data']['name']);
            self::assertContains('Idempotency-Key: card-checkout-BI-1234', $headers);
            self::assertSame('https://example.test/commande/carte/confirmation', $params['success_url']);
            return [json_encode(['id' => 'cs_test_local', 'object' => 'checkout.session', 'url' => 'https://checkout.stripe.com/test']), 200, []];
        });
        ApiRequestor::setHttpClient($http);
        try {
            $gateway = new StripeGateway('sk_test_unit_only', 'whsec_unit_only', 'https://example.test', 'test');
            $order = (new CustomerOrder())->setReference('BI-1234')->setCustomer((new Customer())->setEmail('test@example.test'));
            self::assertSame('cs_test_local', $gateway->createSession($order)['id']);
        } finally {
            ApiRequestor::setHttpClient(new CurlClient());
        }
    }

    public function testEnvironmentRejectsKeysFromTheOtherMode(): void
    {
        self::assertFalse((new StripeGateway('sk_live_unit', 'whsec_unit', 'https://dev.beauteinee.fr', 'test'))->isConfigured());
        self::assertFalse((new StripeGateway('sk_test_unit', 'whsec_unit', 'https://beauteinee.fr', 'live'))->isConfigured());
        self::assertTrue((new StripeGateway('sk_test_unit', 'whsec_unit', 'https://dev.beauteinee.fr', 'test'))->isConfigured());
        self::assertTrue((new StripeGateway('sk_live_unit', 'whsec_unit', 'https://beauteinee.fr', 'live'))->isConfigured());
        self::assertFalse((new StripeGateway('sk_live_unit', 'whsec_unit', 'https://dev.beauteinee.fr', 'live'))->isConfigured());
        self::assertFalse((new StripeGateway('sk_test_unit', 'whsec_unit', 'https://beauteinee.fr', 'test'))->isConfigured());
        self::assertFalse((new StripeGateway('sk_test_unit', 'whsec_unit', 'https://example.test', 'invalid'))->isConfigured());
    }

    public function testReferencesAndMissingConfiguration(): void
    {
        self::assertSame('BI-CARTE-1234', CardReference::forOrder('BI-1234'));
        self::assertSame('TEST-BI-CARTE-1234', CardReference::forOrder('BI-1234', true));
        self::assertFalse((new StripeGateway('', '', '', 'test'))->isConfigured());
        self::assertFalse((new StripeGateway('sk_test_unit', 'whsec_unit', 'http://example.test', 'test'))->isConfigured());
        self::assertTrue((new StripeGateway('sk_live_unit', 'whsec_unit', 'https://example.test', 'live'))->isLive());
    }
}
