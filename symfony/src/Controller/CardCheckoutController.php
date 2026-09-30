<?php

namespace App\Controller;

use App\Entity\CustomerOrder;
use App\Service\CardOrderService;
use App\Service\FeatureFlags;
use App\Service\StripeGateway;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Exception\SignatureVerificationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CardCheckoutController extends AbstractController
{
    #[Route('/commande/carte', name: 'card_checkout', methods: ['GET', 'POST'])]
    public function checkout(Request $request, FeatureFlags $flags, StripeGateway $stripe, CardOrderService $orders): Response
    {
        if (!$flags->isCardSalesEnabled()) {
            throw $this->createNotFoundException();
        }
        $error = null;
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('card_checkout', (string) $request->request->get('_token'))) {
                return new Response('Jeton de formulaire invalide.', Response::HTTP_BAD_REQUEST);
            }
            if (!$stripe->isConfigured()) {
                $error = 'Le paiement sera bientôt disponible.';
            } else {
                try {
                    $order = $orders->create($request->request->all());
                    $session = $stripe->createSession($order);
                    $orders->recordSession($order, $session['id']);
                    $request->getSession()->set('card_checkout_reference', $order->getReference());

                    return $this->redirect($session['url'], Response::HTTP_SEE_OTHER);
                } catch (\InvalidArgumentException $exception) {
                    $error = $exception->getMessage();
                } catch (\Stripe\Exception\ApiErrorException $exception) {
                    $error = 'Le paiement est temporairement indisponible. Veuillez réessayer plus tard.';
                }
            }
        }

        return $this->render('checkout/card.html.twig', [
            'available' => $stripe->isConfigured(), 'error' => $error,
            'values' => array_map(static fn ($value): string => is_string($value) ? $value : '', $request->request->all()),
            'cardAmount' => CardOrderService::CARD_AMOUNT, 'shippingAmount' => CardOrderService::SHIPPING_AMOUNT,
        ]);
    }

    #[Route('/commande/carte/confirmation', name: 'card_checkout_confirmation', methods: ['GET'])]
    public function confirmation(Request $request, EntityManagerInterface $em): Response
    {
        $reference = $request->getSession()->get('card_checkout_reference');
        $order = is_string($reference) ? $em->getRepository(CustomerOrder::class)->findOneBy(['reference' => $reference]) : null;

        return $this->render('checkout/confirmation.html.twig', ['order' => $order]);
    }

    #[Route('/stripe/webhook', name: 'stripe_webhook', methods: ['POST'])]
    public function webhook(Request $request, StripeGateway $stripe, CardOrderService $orders): Response
    {
        if (!$stripe->isConfigured()) {
            return new Response('Paiement non configuré.', Response::HTTP_SERVICE_UNAVAILABLE);
        }
        try {
            $event = $stripe->verifyEvent($request->getContent(), (string) $request->headers->get('Stripe-Signature'));
        } catch (SignatureVerificationException|\UnexpectedValueException $exception) {
            return new Response('Signature ou contenu invalide.', Response::HTTP_BAD_REQUEST);
        }
        try {
            if (!$orders->handleEvent($event, $stripe->isLive())) {
                return new Response('Confirmation email temporairement indisponible.', Response::HTTP_SERVICE_UNAVAILABLE);
            }
        } catch (\UnexpectedValueException $exception) {
            return new Response('Paiement non reconnu.', Response::HTTP_BAD_REQUEST);
        }

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
