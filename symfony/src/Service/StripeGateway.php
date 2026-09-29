<?php

namespace App\Service;

use App\Entity\CustomerOrder;
use Stripe\StripeClient;
use Stripe\Webhook;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class StripeGateway
{
    public function __construct(
        #[Autowire('%env(STRIPE_SECRET_KEY)%')]
        private readonly string $secretKey,
        #[Autowire('%env(STRIPE_WEBHOOK_SECRET)%')]
        private readonly string $webhookSecret,
        #[Autowire('%env(STRIPE_RETURN_BASE_URL)%')]
        private readonly string $returnBaseUrl,
    ) {
    }

    public function isConfigured(): bool
    {
        return preg_match('/^(sk|rk)_(test|live)_/', $this->secretKey) === 1
            && str_starts_with($this->webhookSecret, 'whsec_')
            && filter_var($this->returnBaseUrl, FILTER_VALIDATE_URL)
            && parse_url($this->returnBaseUrl, PHP_URL_SCHEME) === 'https';
    }

    public function isLive(): bool
    {
        return preg_match('/^(sk|rk)_live_/', $this->secretKey) === 1;
    }

    /** @return array{id: string, url: string} */
    public function createSession(CustomerOrder $order): array
    {
        if (!$this->isConfigured()) {
            throw new \RuntimeException('Le paiement sera bientôt disponible.');
        }
        $baseUrl = rtrim($this->returnBaseUrl, '/');
        $session = (new StripeClient($this->secretKey))->checkout->sessions->create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'customer_email' => $order->getCustomer()->getEmail(),
            'client_reference_id' => $order->getReference(),
            'metadata' => ['application' => 'beaute_inee_card', 'order_reference' => $order->getReference()],
            'line_items' => [
                ['price_data' => ['currency' => 'eur', 'unit_amount' => CardOrderService::CARD_AMOUNT, 'product_data' => ['name' => CardReference::PRODUCT_NAME]], 'quantity' => 1],
                ['price_data' => ['currency' => 'eur', 'unit_amount' => CardOrderService::SHIPPING_AMOUNT, 'product_data' => ['name' => 'Livraison']], 'quantity' => 1],
            ],
            'success_url' => $baseUrl . '/commande/carte/confirmation',
            'cancel_url' => $baseUrl . '/commande/carte?annule=1',
            'locale' => $order->getCustomer()->getPreferredLocale() === 'en' ? 'en' : 'fr',
        ], ['idempotency_key' => 'card-checkout-' . $order->getReference()]);

        return ['id' => $session->id, 'url' => $session->url];
    }

    public function verifyEvent(string $body, string $signature): array
    {
        if ($this->webhookSecret === '') {
            throw new \RuntimeException('Stripe non configuré.');
        }

        return Webhook::constructEvent($body, $signature, $this->webhookSecret)->toArray();
    }
}
